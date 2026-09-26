<?php

namespace App\Services;

use App\Enums\DistributionGroup;
use App\Enums\SectorType;
use App\Models\Sector;
use App\Support\DashboardFilters;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Angka-angka dashboard satu sektor, dihitung langsung di database (SUM/GROUP BY).
 *
 * Definisi (sama dengan aplikasi lama):
 * - Sudah panen   : harvest_quantity terisi; rentang tanggal memakai harvest_date.
 * - Belum panen   : harvest_date kosong; rentang tanggal memakai estimated_harvest_date.
 * - Terlambat     : belum panen dan estimated_harvest_date sudah lewat (tanpa filter tanggal).
 * - 7 hari ke depan: belum panen dan estimated_harvest_date antara hari ini s.d. +7 hari.
 *
 * Rincian per wilayah dikelompokkan per kecamatan, atau per kelurahan bila
 * filter kecamatan dipilih. Kelompok yang di-soft delete tidak dihitung.
 */
class SectorDashboard
{
    public const UPCOMING_DAYS = 7;

    private readonly Carbon $today;

    public function __construct(
        public readonly SectorType $type,
        public readonly Sector $sector,
        public readonly DashboardFilters $filters,
        ?Carbon $today = null,
    ) {
        $this->today = ($today ?? Carbon::today())->copy()->startOfDay();
    }

    /** 'district' (per kecamatan) atau 'village' (per kelurahan). */
    public function areaLevel(): string
    {
        return $this->filters->districtId ? 'village' : 'district';
    }

    /**
     * @return array{
     *     harvest: array{quantity: float, heads: float, cycles: int, groups: int},
     *     pending: array{initial: float, estimate: float, cycles: int},
     *     late: array{estimate: float, cycles: int},
     *     upcoming: array{estimate: float, cycles: int},
     * }
     */
    public function summary(): array
    {
        $harvest = $this->harvested($this->base())
            ->selectRaw('COALESCE(SUM(p.harvest_quantity), 0) AS quantity')
            ->selectRaw('COALESCE(SUM(p.harvest_head_count), 0) AS heads')
            ->selectRaw('COUNT(*) AS cycles')
            ->selectRaw('COUNT(DISTINCT p.farmer_group_id) AS group_count')
            ->first();

        $pending = $this->pending($this->base())
            ->selectRaw('COALESCE(SUM(p.initial_quantity), 0) AS initial')
            ->selectRaw('COALESCE(SUM(p.estimated_harvest_quantity), 0) AS estimate')
            ->selectRaw('COUNT(*) AS cycles')
            ->first();

        $late = $this->late($this->base())
            ->selectRaw('COALESCE(SUM(p.estimated_harvest_quantity), 0) AS estimate')
            ->selectRaw('COUNT(*) AS cycles')
            ->first();

        $upcoming = $this->upcoming($this->base())
            ->selectRaw('COALESCE(SUM(p.estimated_harvest_quantity), 0) AS estimate')
            ->selectRaw('COUNT(*) AS cycles')
            ->first();

        return [
            'harvest' => [
                'quantity' => (float) $harvest->quantity,
                'heads' => (float) $harvest->heads,
                'cycles' => (int) $harvest->cycles,
                'groups' => (int) $harvest->group_count,
            ],
            'pending' => [
                'initial' => (float) $pending->initial,
                'estimate' => (float) $pending->estimate,
                'cycles' => (int) $pending->cycles,
            ],
            'late' => ['estimate' => (float) $late->estimate, 'cycles' => (int) $late->cycles],
            'upcoming' => ['estimate' => (float) $upcoming->estimate, 'cycles' => (int) $upcoming->cycles],
        ];
    }

    /** @return Collection<int, object{id: int, name: string, total: float, cycles: int}> */
    public function harvestByArea(): Collection
    {
        return $this->groupByArea($this->harvested($this->base()), 'p.harvest_quantity')
            ->get()
            ->map(fn ($row) => $this->castAreaRow($row))
            ->filter(fn ($row) => $row->total > 0)
            ->values();
    }

    /** @return Collection<int, object{id: int, name: string, total: float, cycles: int}> */
    public function pendingByArea(): Collection
    {
        return $this->groupByArea($this->pending($this->base()), 'p.estimated_harvest_quantity')
            ->get()
            ->map(fn ($row) => $this->castAreaRow($row))
            ->filter(fn ($row) => $row->total > 0)
            ->values();
    }

    /** @return Collection<int, object{id: int, name: string, total: float, cycles: int, date: ?string}> */
    public function lateByArea(): Collection
    {
        return $this->groupByArea($this->late($this->base()), 'p.estimated_harvest_quantity')
            ->selectRaw('MIN(p.estimated_harvest_date) AS date')
            ->get()
            ->map(fn ($row) => $this->castAreaRow($row));
    }

    /** @return Collection<int, object{id: int, name: string, total: float, cycles: int, date: ?string}> */
    public function upcomingByArea(): Collection
    {
        return $this->groupByArea($this->upcoming($this->base()), 'p.estimated_harvest_quantity')
            ->selectRaw('MIN(p.estimated_harvest_date) AS date')
            ->reorder()
            ->orderBy('date')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => $this->castAreaRow($row));
    }

    /**
     * Ke mana hasil panen disalurkan, per kategori penerima.
     *
     * @return Collection<int, object{code: string, name: string, group: DistributionGroup, quantity: float, households: int, persons: int}>
     */
    public function distributionSummary(): Collection
    {
        return $this->harvested($this->base())
            ->join('distributions as dist', 'dist.production_id', '=', 'p.id')
            ->join('recipient_categories as rc', 'rc.id', '=', 'dist.recipient_category_id')
            ->groupBy('rc.id', 'rc.code', 'rc.name', 'rc.sort_order')
            ->orderBy('rc.sort_order')
            ->select('rc.code', 'rc.name')
            ->selectRaw('COALESCE(SUM(dist.quantity), 0) AS quantity')
            ->selectRaw('COALESCE(SUM(dist.household_count), 0) AS households')
            ->selectRaw('COALESCE(SUM(dist.person_count), 0) AS persons')
            ->get()
            ->map(fn ($row) => (object) [
                'code' => $row->code,
                'name' => $row->name,
                'group' => DistributionGroup::fromCategoryCode($row->code),
                'quantity' => (float) $row->quantity,
                'households' => (int) $row->households,
                'persons' => (int) $row->persons,
            ])
            ->filter(fn ($row) => $row->quantity > 0 || $row->households > 0 || $row->persons > 0)
            ->values();
    }

    public function base(): Builder
    {
        return DB::table('productions as p')
            ->join('commodities as c', 'c.id', '=', 'p.commodity_id')
            ->join('farmer_groups as g', 'g.id', '=', 'p.farmer_group_id')
            ->join('villages as v', 'v.id', '=', 'g.village_id')
            ->join('districts as d', 'd.id', '=', 'v.district_id')
            ->where('c.sector_id', $this->sector->id)
            ->whereNull('g.deleted_at')
            ->when($this->filters->commodityId, fn (Builder $q, int $id) => $q->where('p.commodity_id', $id))
            ->when($this->filters->districtId, fn (Builder $q, int $id) => $q->where('v.district_id', $id));
    }

    public function harvested(Builder $query): Builder
    {
        $query->whereNotNull('p.harvest_quantity');

        return $this->filters->applyDateRange($query, 'p.harvest_date');
    }

    public function pending(Builder $query): Builder
    {
        $query->whereNull('p.harvest_date');

        return $this->filters->applyDateRange($query, 'p.estimated_harvest_date');
    }

    public function late(Builder $query): Builder
    {
        return $query
            ->whereNull('p.harvest_date')
            ->where('p.estimated_harvest_date', '<', $this->today->toDateString());
    }

    public function upcoming(Builder $query): Builder
    {
        return $query
            ->whereNull('p.harvest_date')
            ->where('p.estimated_harvest_date', '>=', $this->today->toDateString())
            ->where('p.estimated_harvest_date', '<', $this->today->copy()->addDays(self::UPCOMING_DAYS + 1)->toDateString());
    }

    private function groupByArea(Builder $query, string $sumColumn): Builder
    {
        [$id, $name] = $this->areaLevel() === 'village' ? ['v.id', 'v.name'] : ['d.id', 'd.name'];

        return $query
            ->groupBy($id, $name)
            ->select("{$id} as id", "{$name} as name")
            ->selectRaw("COALESCE(SUM({$sumColumn}), 0) AS total")
            ->selectRaw('COUNT(*) AS cycles')
            ->orderByDesc('total')
            ->orderBy($name);
    }

    private function castAreaRow(object $row): object
    {
        return (object) [
            'id' => (int) $row->id,
            'name' => $row->name,
            'total' => (float) $row->total,
            'cycles' => (int) $row->cycles,
            'date' => isset($row->date) ? substr((string) $row->date, 0, 10) : null,
        ];
    }
}
