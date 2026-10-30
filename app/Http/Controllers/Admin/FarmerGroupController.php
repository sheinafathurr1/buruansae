<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FarmerGroupRequest;
use App\Models\District;
use App\Models\FarmerGroup;
use App\Models\User;
use App\Support\ImageStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Admin: semua kelompok. Staf: hanya kelompok binaannya, tanpa tambah/hapus. */
class FarmerGroupController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $search = trim((string) $request->query('q', ''));
        $districtId = $request->integer('district') ?: null;
        $status = $request->query('status');
        // Filter petugas (admin): "none" = belum ada petugas, angka = id staf.
        $staffFilter = $user->isAdmin() ? (string) $request->query('petugas', '') : '';

        $groups = FarmerGroup::query()
            ->managedBy($user)
            ->with(['village:id,name,district_id', 'village.district:id,name', 'staff:id,name'])
            ->withCount('productions')
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('leader_name', 'like', "%{$search}%")
                ->orWhereHas('staff', fn ($q) => $q->where('name', 'like', "%{$search}%"))))
            ->when($districtId, fn ($q, $id) => $q->whereHas('village', fn ($q) => $q->where('district_id', $id)))
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($status === 'unknown', fn ($q) => $q->whereNull('is_active'))
            ->when($staffFilter === 'none', fn ($q) => $q->doesntHave('staff'))
            ->when(ctype_digit($staffFilter), fn ($q) => $q->whereHas('staff', fn ($q) => $q->whereKey((int) $staffFilter)))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.groups.index', [
            'groups' => $groups,
            'districts' => District::query()->withGroupsManagedBy($user)->orderBy('name')->get(['id', 'name']),
            'staffUsers' => $user->isAdmin() ? $this->staffUsers() : collect(),
            'search' => $search,
            'districtId' => $districtId,
            'status' => $status,
            'staffFilter' => $staffFilter,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', FarmerGroup::class);

        return $this->form(new FarmerGroup(['is_active' => true]));
    }

    public function store(FarmerGroupRequest $request): RedirectResponse
    {
        $group = new FarmerGroup($this->attributes($request));
        $this->storePhotos($request, $group);
        $group->save();
        $group->staff()->sync($request->validated('staff_ids', []));

        return redirect()->route('admin.kelompok.index')->with('success', "Kelompok {$group->name} berhasil ditambahkan.");
    }

    public function edit(FarmerGroup $farmerGroup): View
    {
        Gate::authorize('update', $farmerGroup);

        return $this->form($farmerGroup->load(['village.district', 'staff:id,name']));
    }

    public function update(FarmerGroupRequest $request, FarmerGroup $farmerGroup): RedirectResponse
    {
        $old = ['land_photo' => $farmerGroup->land_photo, 'leader_photo' => $farmerGroup->leader_photo];

        $farmerGroup->fill($this->attributes($request));
        $this->storePhotos($request, $farmerGroup);
        $farmerGroup->save();

        if ($request->user()->isAdmin()) {
            $farmerGroup->staff()->sync($request->validated('staff_ids', []));
        }

        foreach ($old as $column => $file) {
            if ($file && $farmerGroup->{$column} !== $file) {
                ImageStore::delete($file, FarmerGroup::PHOTO_DIRECTORY);
            }
        }

        return redirect()->route('admin.kelompok.index')->with('success', "Kelompok {$farmerGroup->name} berhasil diperbarui.");
    }

    /**
     * Soft delete: data produksi kelompok tetap tersimpan (tidak lagi dihitung
     * di portal publik). Aplikasi lama menghapus permanen seluruh datanya.
     */
    public function destroy(FarmerGroup $farmerGroup): RedirectResponse
    {
        Gate::authorize('delete', $farmerGroup);

        $farmerGroup->delete();

        return redirect()->route('admin.kelompok.index')->with('success', "Kelompok {$farmerGroup->name} berhasil dihapus.");
    }

    private function form(FarmerGroup $group): View
    {
        $isAdmin = request()->user()->isAdmin();

        return view('admin.groups.form', [
            'group' => $group,
            'staffUsers' => $isAdmin ? $this->staffUsers() : collect(),
            'districts' => District::query()
                ->with(['villages' => fn ($q) => $q->orderBy('name')->select('id', 'name', 'district_id')])
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    /** Semua staf, termasuk yang nonaktif, agar penugasannya tidak hilang saat form disimpan. */
    private function staffUsers()
    {
        return User::query()
            ->where('role', UserRole::Staff)
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'is_active']);
    }

    private function attributes(FarmerGroupRequest $request): array
    {
        return $request->safe()->except(['land_photo', 'leader_photo', 'remove_land_photo', 'remove_leader_photo', 'staff_ids']);
    }

    private function storePhotos(FarmerGroupRequest $request, FarmerGroup $group): void
    {
        foreach (['land_photo', 'leader_photo'] as $column) {
            if ($request->hasFile($column)) {
                $group->{$column} = ImageStore::store($request->file($column), FarmerGroup::PHOTO_DIRECTORY);
            } elseif ($request->boolean('remove_'.$column)) {
                $group->{$column} = null;
            }
        }
    }
}
