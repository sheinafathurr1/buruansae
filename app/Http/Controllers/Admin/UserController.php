<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\FarmerGroup;
use App\Models\Production;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Menu Pengguna (khusus admin): akun admin & staf serta kelompok binaan staf. */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $role = UserRole::tryFrom((string) $request->query('peran'));

        $users = User::query()
            ->withCount('farmerGroups')
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when($role, fn ($q, $role) => $q->where('role', $role))
            ->orderBy('role')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'groupsWithoutStaff' => FarmerGroup::query()->doesntHave('staff')->count(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new User(['role' => UserRole::Staff, 'is_active' => true]));
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                ...Arr::only($data, ['name', 'username', 'email', 'role', 'is_active', 'password']),
                'must_change_password' => true,
            ]);
            $user->farmerGroups()->sync($this->groupIds($user, $data));

            return $user;
        });

        return redirect()->route('admin.pengguna.index')->with('success',
            "Akun {$user->username} berhasil dibuat. Berikan kata sandi sementaranya; pengguna wajib menggantinya saat pertama masuk.");
    }

    public function edit(User $user): View
    {
        return $this->form($user->load('farmerGroups:id'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $attributes = Arr::only($data, ['name', 'username', 'email', 'role', 'is_active']);

        if (filled($data['password'] ?? null)) {
            $attributes['password'] = $data['password'];
            $attributes['must_change_password'] = true;
        }

        DB::transaction(function () use ($user, $attributes, $data) {
            $user->update($attributes);
            $user->farmerGroups()->sync($this->groupIds($user, $data));
        });

        $message = "Akun {$user->username} berhasil diperbarui.";
        if (isset($attributes['password'])) {
            $message .= ' Kata sandi sementara baru wajib diganti saat pengguna masuk.';
        }

        return redirect()->route('admin.pengguna.index')->with('success', $message);
    }

    /** Akun yang sudah pernah mencatat data tidak dihapus agar riwayatnya tetap jelas; nonaktifkan saja. */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        $hasRecords = Production::query()
            ->where('created_by', $user->id)
            ->orWhere('updated_by', $user->id)
            ->exists();

        if ($hasRecords) {
            return back()->with('error',
                "Akun {$user->username} sudah pernah mencatat data, jadi tidak dihapus agar riwayatnya tetap tercatat. Nonaktifkan saja akunnya.");
        }

        $user->delete();

        return redirect()->route('admin.pengguna.index')->with('success', "Akun {$user->username} berhasil dihapus.");
    }

    private function form(User $user): View
    {
        // Diurutkan per kecamatan → kelurahan agar mudah dipilih per wilayah.
        $groups = FarmerGroup::query()
            ->join('villages', 'villages.id', '=', 'farmer_groups.village_id')
            ->join('districts', 'districts.id', '=', 'villages.district_id')
            ->with('staff:id,name')
            ->orderBy('districts.name')
            ->orderBy('villages.name')
            ->orderBy('farmer_groups.name')
            ->get([
                'farmer_groups.id', 'farmer_groups.name', 'farmer_groups.rw', 'farmer_groups.extension_officer',
                'farmer_groups.facilitator', 'villages.name as village_name', 'districts.id as district_id',
                'districts.name as district_name',
            ]);

        return view('admin.users.form', [
            'user' => $user,
            'isSelf' => $user->exists && $user->is(request()->user()),
            'groupsByDistrict' => $groups->groupBy('district_id'),
            'selectedGroups' => array_map('intval', old('group_ids', $user->exists ? $user->farmerGroups->pluck('id')->all() : [])),
        ]);
    }

    /** Admin mengelola semua kelompok, jadi penugasan hanya disimpan untuk staf. */
    private function groupIds(User $user, array $data): array
    {
        return $user->isAdmin() ? [] : ($data['group_ids'] ?? []);
    }
}
