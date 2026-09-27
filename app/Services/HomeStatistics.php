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

    /** Panjang grafik panen bulanan di beranda. */
    public const MONTHS = 30;

    /**
     * @return array{
     *     groups: int, active_groups: int, villages: int, districts: int,
     *     harvest_kg_this_year: float, beneficiaries_this_year: int, year: int,
     *     harvest_kg_total: float, beneficiaries_total: int, since_year: ?int, last_harvest_date: ?string,
     *     monthly_harvest: list<array{month: string, total: float}>,
     *     map_points: list<array{lat: float, lng: float, groups: int}>,
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

        $monthlyHarvest = $this->monthlyHarvest($harvests, $allTime, $period?->first_date, $period?->last_date);
        $mapPoints = $this->mapPoints();

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
            'monthly_harvest' => $monthlyHarvest,
            'map_points' => $mapPoints,
            'sectors' => $sectors,
        ];
    }

    /**
     * Hasil panen (kg) per bulan, paling banyak MONTHS bulan terakhir sampai bulan
     * panen terakhir. Bulan tanpa panen bernilai 0.
     *
     * @return list<array{month: string, total: float}>
     */
    private function monthlyHarvest(\Closure $harvests, array $allTime, ?string $firstDate, ?string $lastDate): array
    {
        if (! $firstDate || ! $lastDate) {
            return [];
        }

        $last = Carbon::parse(substr($lastDate, 0, 10))->startOfMonth();
        $first = Carbon::parse(substr($firstDate, 0, 10))->startOfMonth()->max($last->copy()->subMonths(self::MONTHS - 1));

        // substr() pada tanggal berlaku sama di MariaDB dan SQLite.
        $totals = $harvests()->where('s.harvest_unit', 'kg')
            ->whereBetween('p.harvest_date', [$first->toDateString(), $allTime[1]])
            ->selectRaw('substr(p.harvest_date, 1, 7) AS month, SUM(p.harvest_quantity) AS total')
            ->groupBy('month')
            ->pluck('total', 'month');

        $series = [];
        for ($month = $first->copy(); $month->lte($last); $month->addMonth()) {
            $key = $month->format('Y-m');
            $series[] = ['month' => $key, 'total' => round((float) ($totals[$key] ?? 0), 1)];
        }

        return $series;
    }

    /**
     * Titik kelurahan yang punya kelompok (untuk peta mini beranda), dalam batas
     * wilayah yang sama dengan /api/locations.
     *
     * @return list<array{lat: float, lng: float, groups: int}>
     */
    private function mapPoints(): array
    {
        $bounds = config('buruansae.map.locations_bounds');

        return DB::table('villages as v')
            ->join('farmer_groups as g', 'g.village_id', '=', 'v.id')
            ->whereNull('g.deleted_at')
            ->whereBetween('v.latitude', [$bounds['south'], $bounds['north']])
            ->whereBetween('v.longitude', [$bounds['west'], $bounds['east']])
            ->groupBy('v.id', 'v.latitude', 'v.longitude')
            ->select('v.latitude', 'v.longitude')
            ->selectRaw('COUNT(*) AS group_count')
            ->get()
            ->map(fn ($row) => ['lat' => (float) $row->latitude, 'lng' => (float) $row->longitude, 'groups' => (int) $row->group_count])
            ->all();
    }
}
