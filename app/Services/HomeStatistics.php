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
     *     harvest_kg_total: float, beneficiaries_total: int, since_year: ?int, last_harvest_date: ?string,
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

        // Sejak awal program. Batas bawah & "hari ini" menyaring tanggal salah ketik
        // di data lama (mis. tahun 0026 atau 2925).
        $allTime = ['2015-01-01', Carbon::today()->toDateString()];

        $harvests = fn () => DB::table('productions as p')
            ->join('commodities as c', 'c.id', '=', 'p.commodity_id')
            ->join('sectors as s', 's.id', '=', 'c.sector_id')
            ->join('farmer_groups as g', 'g.id', '=', 'p.farmer_group_id')
            ->whereNull('g.deleted_at');

        $beneficiaries = fn (array $range) => (int) DB::table('distributions as dist')
            ->join('recipient_categories as rc', 'rc.id', '=', 'dist.recipient_category_id')
            ->join('productions as p', 'p.id', '=', 'dist.production_id')
            ->join('farmer_groups as g', 'g.id', '=', 'p.farmer_group_id')
            ->whereNull('g.deleted_at')
            ->whereNotIn('rc.code', ['KP', 'DIJUAL'])
            ->whereBetween('p.harvest_date', $range)
            ->sum('dist.person_count');

        $harvestKg = $harvests()->where('s.harvest_unit', 'kg')->whereBetween('p.harvest_date', $yearRange)->sum('p.harvest_quantity');
        $harvestKgTotal = $harvests()->where('s.harvest_unit', 'kg')->whereBetween('p.harvest_date', $allTime)->sum('p.harvest_quantity');
        // Rentang data: abaikan panen yang tercatat sebelum tanggal tanamnya (salah ketik tahun).
        $period = $harvests()->whereBetween('p.harvest_date', $allTime)
            ->where(fn ($q) => $q->whereNull('p.start_date')->orWhereColumn('p.harvest_date', '>=', 'p.start_date'))
            ->selectRaw('MIN(p.harvest_date) AS first_date, MAX(p.harvest_date) AS last_date')
            ->first();

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
            'beneficiaries_this_year' => $beneficiaries($yearRange),
            'year' => $year,
            'harvest_kg_total' => (float) $harvestKgTotal,
            'beneficiaries_total' => $beneficiaries($allTime),
            'since_year' => $period?->first_date ? (int) substr($period->first_date, 0, 4) : null,
            'last_harvest_date' => $period?->last_date ? substr($period->last_date, 0, 10) : null,
            'sectors' => $sectors,
        ];
    }
}
