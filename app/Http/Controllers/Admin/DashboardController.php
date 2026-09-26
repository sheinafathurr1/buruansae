<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SectorType;
use App\Http\Controllers\Controller;
use App\Models\FarmerGroup;
use App\Models\Production;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth()->toDateString();

        // Hitungan per sektor dalam satu query.
        $perSector = DB::table('productions as p')
            ->join('commodities as c', 'c.id', '=', 'p.commodity_id')
            ->join('sectors as s', 's.id', '=', 'c.sector_id')
            ->join('farmer_groups as g', 'g.id', '=', 'p.farmer_group_id')
            ->whereNull('g.deleted_at')
            ->groupBy('s.code')
            ->select('s.code')
            ->selectRaw('SUM(CASE WHEN p.harvest_date IS NULL THEN 1 ELSE 0 END) AS running')
            ->selectRaw('SUM(CASE WHEN p.harvest_date IS NULL AND p.estimated_harvest_date < ? THEN 1 ELSE 0 END) AS late', [$today->toDateString()])
            ->selectRaw('SUM(CASE WHEN p.harvest_date >= ? THEN 1 ELSE 0 END) AS harvested_this_month', [$monthStart])
            ->selectRaw('COALESCE(SUM(CASE WHEN p.harvest_date >= ? AND s.harvest_unit = ? THEN p.harvest_quantity ELSE 0 END), 0) AS kg_this_month', [$monthStart, 'kg'])
            ->get()
            ->keyBy('code');

        $sectors = collect(SectorType::cases())->map(fn (SectorType $type) => (object) [
            'type' => $type,
            'running' => (int) ($perSector[$type->code()]->running ?? 0),
            'late' => (int) ($perSector[$type->code()]->late ?? 0),
            'harvested_this_month' => (int) ($perSector[$type->code()]->harvested_this_month ?? 0),
        ]);

        $lateProductions = Production::query()
            ->notHarvested()
            ->where('estimated_harvest_date', '<', $today->toDateString())
            ->whereHas('farmerGroup')
            ->with(['commodity:id,name,sector_id', 'commodity.sector:id,code', 'farmerGroup:id,name,village_id', 'farmerGroup.village:id,name'])
            ->orderBy('estimated_harvest_date')
            ->limit(8)
            ->get();

        return view('admin.dashboard', [
            'stats' => [
                'active_groups' => FarmerGroup::query()->where('is_active', true)->count(),
                'groups' => FarmerGroup::query()->count(),
                'running' => $sectors->sum('running'),
                'late' => $sectors->sum('late'),
                'kg_this_month' => (float) $perSector->sum('kg_this_month'),
            ],
            'sectors' => $sectors,
            'lateProductions' => $lateProductions,
            'today' => $today,
        ]);
    }
}
