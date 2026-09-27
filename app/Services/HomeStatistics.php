<?php

namespace App\Services;

use App\Enums\SectorType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/** Angka ringkas untuk beranda, disimpan di cache beberapa menit. */
class HomeStatistics
{
    public const CACHE_KEY = 'home.statistics';

    public const CACHE_TTL_SECONDS = 600;

    /**
     * @return array{
     *     groups: int, active_groups: int, villages: int, districts: int,
     *     harvest_kg_this_year: float, beneficiaries_this_year: int, year: int,
     *     sectors: array<string, array{groups: int, commodities: int}>,
     * }
     */
    public function get(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, fn () => $this->compute());
    }

    private function compute(): array
    {
        $year = Carbon::today()->year;
        $yearRange = ["{$year}-01-01", "{$year}-12-31"];

        $groups = DB::table('farmer_groups as g')
            ->join('villages as v', 'v.id', '=', 'g.village_id')
            ->whereNull('g.deleted_at')
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(CASE WHEN g.is_active = 1 THEN 1 ELSE 0 END) AS active')
            ->selectRaw('COUNT(DISTINCT g.village_id) AS villages')
            ->selectRaw('COUNT(DISTINCT v.district_id) AS districts')
            ->first();

        $harvestKg = DB::table('productions as p')
            ->join('commodities as c', 'c.id', '=', 'p.commodity_id')
            ->join('sectors as s', 's.id', '=', 'c.sector_id')
            ->join('farmer_groups as g', 'g.id', '=', 'p.farmer_group_id')
            ->whereNull('g.deleted_at')
            ->where('s.harvest_unit', 'kg')
            ->whereBetween('p.harvest_date', $yearRange)
            ->sum('p.harvest_quantity');

        $beneficiaries = DB::table('distributions as dist')
            ->join('recipient_categories as rc', 'rc.id', '=', 'dist.recipient_category_id')
            ->join('productions as p', 'p.id', '=', 'dist.production_id')
            ->join('farmer_groups as g', 'g.id', '=', 'p.farmer_group_id')
            ->whereNull('g.deleted_at')
            ->whereNotIn('rc.code', ['KP', 'DIJUAL'])
            ->whereBetween('p.harvest_date', $yearRange)
            ->sum('dist.person_count');

        $perSector = DB::table('productions as p')
            ->join('commodities as c', 'c.id', '=', 'p.commodity_id')
            ->join('sectors as s', 's.id', '=', 'c.sector_id')
            ->join('farmer_groups as g', 'g.id', '=', 'p.farmer_group_id')
            ->whereNull('g.deleted_at')
            ->groupBy('s.code')
            ->select('s.code')
            ->selectRaw('COUNT(DISTINCT p.farmer_group_id) AS group_count')
            ->selectRaw('COUNT(DISTINCT p.commodity_id) AS commodity_count')
            ->get()
            ->keyBy('code');

        $sectors = [];
        foreach (SectorType::cases() as $type) {
            $row = $perSector->get($type->code());
            $sectors[$type->value] = [
                'groups' => (int) ($row->group_count ?? 0),
                'commodities' => (int) ($row->commodity_count ?? 0),
            ];
        }

        return [
            'groups' => (int) $groups->total,
            'active_groups' => (int) $groups->active,
            'villages' => (int) $groups->villages,
            'districts' => (int) $groups->districts,
            'harvest_kg_this_year' => (float) $harvestKg,
            'beneficiaries_this_year' => (int) $beneficiaries,
            'year' => $year,
            'sectors' => $sectors,
        ];
    }
}
