<?php

namespace App\Http\Controllers;

use App\Enums\SectorType;
use App\Http\Requests\SectorDashboardRequest;
use App\Models\Commodity;
use App\Models\District;
use App\Services\SectorDashboard;
use Illuminate\View\View;

class SectorController extends Controller
{
    public function show(SectorDashboardRequest $request, SectorType $sector): View
    {
        $sectorModel = $request->sector();
        $dashboard = new SectorDashboard($sector, $sectorModel, $request->filters());
        $filters = $dashboard->filters;

        // Hanya komoditas yang punya data produksi (sama dengan aplikasi lama).
        $commodities = Commodity::query()
            ->where('sector_id', $sectorModel->id)
            ->whereHas('productions')
            ->orderBy('name')
            ->get(['id', 'name', 'growing_days', 'image']);

        $districts = District::query()->orderBy('name')->get(['id', 'name']);

        return view('sectors.show', [
            'sector' => $sector,
            'sectorModel' => $sectorModel,
            'unit' => $sectorModel->harvest_unit,
            'filters' => $filters,
            'filterErrors' => $request->filterErrors(),
            'commodities' => $commodities,
            'districts' => $districts,
            'selectedCommodity' => $filters->commodityId
                ? ($commodities->firstWhere('id', $filters->commodityId) ?? Commodity::find($filters->commodityId))
                : null,
            'selectedDistrict' => $filters->districtId ? $districts->firstWhere('id', $filters->districtId) : null,
            'areaLevel' => $dashboard->areaLevel(),
            'summary' => $dashboard->summary(),
            'harvestByArea' => $dashboard->harvestByArea(),
            'pendingByArea' => $sector->tracksHarvestEstimate() ? $dashboard->pendingByArea() : collect(),
            'lateByArea' => $sector->tracksHarvestEstimate() ? $dashboard->lateByArea() : collect(),
            'upcomingByArea' => $sector->tracksHarvestEstimate() ? $dashboard->upcomingByArea() : collect(),
            'distribution' => $dashboard->distributionSummary(),
        ]);
    }
}
