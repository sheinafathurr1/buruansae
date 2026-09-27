<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SectorType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductionRequest;
use App\Models\Commodity;
use App\Models\District;
use App\Models\FarmerGroup;
use App\Models\Production;
use App\Models\Sector;
use App\Services\ProductionRecorder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/** Data produksi per sektor (pengganti DataSayur, DataIkan, ... di aplikasi lama). */
class ProductionController extends Controller
{
    public const STATUSES = ['all', 'pending', 'late', 'harvested'];

    public function __construct(private readonly ProductionRecorder $recorder) {}

    public function index(Request $request, SectorType $sector): View
    {
        $sectorModel = $this->sectorModel($sector);
        $today = Carbon::today()->toDateString();
        $status = in_array($request->query('status'), self::STATUSES, true) ? $request->query('status') : 'all';
        $search = trim((string) $request->query('q', ''));
        $commodityId = $request->integer('commodity') ?: null;
        $districtId = $request->integer('district') ?: null;

        $base = fn () => Production::query()
            ->whereHas('commodity', fn (Builder $q) => $q->where('sector_id', $sectorModel->id))
            ->whereHas('farmerGroup', fn (Builder $q) => $q
                ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->when($districtId, fn ($q, $id) => $q->whereHas('village', fn ($q) => $q->where('district_id', $id))))
            ->when($commodityId, fn ($q, $id) => $q->where('commodity_id', $id));

        $counts = $base()
            ->toBase()
            ->selectRaw('COUNT(*) AS total_count')
            ->selectRaw('SUM(CASE WHEN harvest_date IS NULL THEN 1 ELSE 0 END) AS pending_count')
            ->selectRaw('SUM(CASE WHEN harvest_date IS NULL AND estimated_harvest_date < ? THEN 1 ELSE 0 END) AS late_count', [$today])
            ->selectRaw('SUM(CASE WHEN harvest_date IS NOT NULL THEN 1 ELSE 0 END) AS harvested_count')
            ->first();

        $productions = $base()
            ->when($status === 'pending', fn ($q) => $q->whereNull('harvest_date'))
            ->when($status === 'late', fn ($q) => $q->whereNull('harvest_date')->where('estimated_harvest_date', '<', $today))
            ->when($status === 'harvested', fn ($q) => $q->whereNotNull('harvest_date'))
            ->with([
                'commodity:id,name',
                'farmerGroup:id,name,rw,village_id,extension_officer,facilitator',
                'farmerGroup.village:id,name,district_id',
                'farmerGroup.village.district:id,name',
            ])
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.productions.index', [
            'sector' => $sector,
            'unit' => $sectorModel->harvest_unit,
            'productions' => $productions,
            'counts' => [
                'all' => (int) $counts->total_count,
                'pending' => (int) $counts->pending_count,
                'late' => (int) $counts->late_count,
                'harvested' => (int) $counts->harvested_count,
            ],
            'status' => $status,
            'search' => $search,
            'commodityId' => $commodityId,
            'districtId' => $districtId,
            'commodities' => Commodity::query()->where('sector_id', $sectorModel->id)->orderBy('name')->get(['id', 'name']),
            'districts' => District::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(SectorType $sector): View
    {
        return $this->form($sector, new Production);
    }

    public function store(ProductionRequest $request, SectorType $sector): RedirectResponse
    {
        $production = $this->recorder->create($sector, $request->validated());

        return redirect()
            ->route('admin.productions.index', $sector)
            ->with('success', 'Data '.strtolower($sector->label()).' berhasil ditambahkan.')
            ->with('highlight', $production->id);
    }

    public function edit(SectorType $sector, Production $production): View
    {
        $this->ensureBelongsToSector($sector, $production);

        return $this->form($sector, $production->load(['processedProductDetail', 'seedlingDetail', 'inputs']));
    }

    public function update(ProductionRequest $request, SectorType $sector, Production $production): RedirectResponse
    {
        $this->ensureBelongsToSector($sector, $production);
        $this->recorder->update($sector, $production, $request->validated());

        return redirect()
            ->route('admin.productions.index', $sector)
            ->with('success', 'Data '.strtolower($sector->label()).' berhasil diperbarui.');
    }

    public function destroy(SectorType $sector, Production $production): RedirectResponse
    {
        $this->ensureBelongsToSector($sector, $production);
        $this->recorder->delete($production);

        return back()->with('success', 'Data '.strtolower($sector->label()).' berhasil dihapus.');
    }

    private function form(SectorType $sector, Production $production): View
    {
        $sectorModel = $this->sectorModel($sector);

        return view('admin.productions.form', [
            'sector' => $sector,
            'unit' => $sectorModel->harvest_unit,
            'production' => $production,
            'commodities' => Commodity::query()->where('sector_id', $sectorModel->id)->orderBy('name')->get(['id', 'name', 'growing_days']),
            'groups' => FarmerGroup::query()
                ->with(['village:id,name,district_id', 'village.district:id,name'])
                ->orderBy('name')
                ->get(['id', 'name', 'rw', 'village_id', 'extension_officer', 'facilitator']),
        ]);
    }

    private function sectorModel(SectorType $sector): Sector
    {
        return Sector::query()->where('code', $sector->code())->firstOrFail();
    }

    public static function ensureBelongsToSector(SectorType $sector, Production $production): void
    {
        $code = Commodity::query()
            ->join('sectors', 'sectors.id', '=', 'commodities.sector_id')
            ->where('commodities.id', $production->commodity_id)
            ->value('sectors.code');

        abort_unless($code === $sector->code(), 404);
    }
}
