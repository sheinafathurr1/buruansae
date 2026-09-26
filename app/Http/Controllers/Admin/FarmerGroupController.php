<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FarmerGroupRequest;
use App\Models\District;
use App\Models\FarmerGroup;
use App\Support\ImageStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FarmerGroupController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $districtId = $request->integer('district') ?: null;
        $status = $request->query('status');

        $groups = FarmerGroup::query()
            ->with(['village:id,name,district_id', 'village.district:id,name'])
            ->withCount('productions')
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('leader_name', 'like', "%{$search}%")
                ->orWhere('extension_officer', 'like', "%{$search}%")))
            ->when($districtId, fn ($q, $id) => $q->whereHas('village', fn ($q) => $q->where('district_id', $id)))
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($status === 'unknown', fn ($q) => $q->whereNull('is_active'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.groups.index', [
            'groups' => $groups,
            'districts' => District::query()->orderBy('name')->get(['id', 'name']),
            'search' => $search,
            'districtId' => $districtId,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return $this->form(new FarmerGroup(['is_active' => true]));
    }

    public function store(FarmerGroupRequest $request): RedirectResponse
    {
        $group = new FarmerGroup($this->attributes($request));
        $this->storePhotos($request, $group);
        $group->save();

        return redirect()->route('admin.kelompok.index')->with('success', "Kelompok {$group->name} berhasil ditambahkan.");
    }

    public function edit(FarmerGroup $farmerGroup): View
    {
        return $this->form($farmerGroup->load('village'));
    }

    public function update(FarmerGroupRequest $request, FarmerGroup $farmerGroup): RedirectResponse
    {
        $old = ['land_photo' => $farmerGroup->land_photo, 'leader_photo' => $farmerGroup->leader_photo];

        $farmerGroup->fill($this->attributes($request));
        $this->storePhotos($request, $farmerGroup);
        $farmerGroup->save();

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
        $farmerGroup->delete();

        return redirect()->route('admin.kelompok.index')->with('success', "Kelompok {$farmerGroup->name} berhasil dihapus.");
    }

    private function form(FarmerGroup $group): View
    {
        return view('admin.groups.form', [
            'group' => $group,
            'districts' => District::query()
                ->with(['villages' => fn ($q) => $q->orderBy('name')->select('id', 'name', 'district_id')])
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    private function attributes(FarmerGroupRequest $request): array
    {
        return $request->safe()->except(['land_photo', 'leader_photo', 'remove_land_photo', 'remove_leader_photo']);
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
