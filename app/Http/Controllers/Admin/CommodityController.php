<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CommodityRequest;
use App\Models\Commodity;
use App\Models\Sector;
use App\Support\ImageStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommodityController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $sectorId = $request->integer('sector') ?: null;

        $commodities = Commodity::query()
            ->with('sector:id,name,code')
            ->withCount('productions')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%'.mb_strtoupper($search).'%'))
            ->when($sectorId, fn ($q, $id) => $q->where('sector_id', $id))
            ->orderBy('sector_id')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.commodities.index', [
            'commodities' => $commodities,
            'sectors' => $this->sectors(),
            'search' => $search,
            'sectorId' => $sectorId,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.commodities.form', [
            'commodity' => new Commodity(['sector_id' => $request->integer('sector') ?: null]),
            'sectors' => $this->sectors(),
        ]);
    }

    public function store(CommodityRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(['sector_id', 'name', 'growing_days']);

        if ($request->hasFile('image')) {
            $data['image'] = ImageStore::store($request->file('image'), Commodity::IMAGE_DIRECTORY);
        }

        Commodity::create($data);

        return redirect()->route('admin.komoditas.index')->with('success', "Komoditas {$data['name']} berhasil ditambahkan.");
    }

    public function edit(Commodity $commodity): View
    {
        return view('admin.commodities.form', [
            'commodity' => $commodity,
            'sectors' => $this->sectors(),
        ]);
    }

    public function update(CommodityRequest $request, Commodity $commodity): RedirectResponse
    {
        $data = $request->safe()->only(['sector_id', 'name', 'growing_days']);

        if ((int) $commodity->sector_id !== (int) $data['sector_id'] && $commodity->productions()->exists()) {
            return back()->withInput()->withErrors(['sector_id' => 'Sektor tidak bisa diubah karena komoditas ini sudah dipakai data produksi.']);
        }

        $oldImage = $commodity->image;
        if ($request->hasFile('image')) {
            $data['image'] = ImageStore::store($request->file('image'), Commodity::IMAGE_DIRECTORY);
        } elseif ($request->boolean('remove_image')) {
            $data['image'] = null;
        }

        $commodity->update($data);

        if (array_key_exists('image', $data) && $oldImage) {
            ImageStore::delete($oldImage, Commodity::IMAGE_DIRECTORY);
        }

        return redirect()->route('admin.komoditas.index')->with('success', "Komoditas {$commodity->name} berhasil diperbarui.");
    }

    public function destroy(Commodity $commodity): RedirectResponse
    {
        $used = $commodity->productions()->count();
        if ($used > 0) {
            return back()->with('error', "Komoditas {$commodity->name} tidak bisa dihapus karena dipakai {$used} data produksi.");
        }

        $commodity->delete();
        ImageStore::delete($commodity->image, Commodity::IMAGE_DIRECTORY);

        return redirect()->route('admin.komoditas.index')->with('success', "Komoditas {$commodity->name} berhasil dihapus.");
    }

    private function sectors()
    {
        return Sector::query()->orderBy('id')->get(['id', 'code', 'name']);
    }
}
