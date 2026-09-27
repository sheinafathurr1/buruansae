<?php

namespace App\Support;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Carbon;

/** Filter dashboard sektor yang sudah tervalidasi. */
final readonly class DashboardFilters
{
    public function __construct(
        public ?int $commodityId = null,
        public ?int $districtId = null,
        public ?Carbon $startDate = null,
        public ?Carbon $endDate = null,
    ) {}

    /** @param  array<string, mixed>  $valid  hanya field yang lolos validasi */
    public static function fromArray(array $valid): self
    {
        return new self(
            commodityId: isset($valid['commodity']) ? (int) $valid['commodity'] : null,
            districtId: isset($valid['district']) ? (int) $valid['district'] : null,
            startDate: isset($valid['start_date']) ? Carbon::parse($valid['start_date'])->startOfDay() : null,
            endDate: isset($valid['end_date']) ? Carbon::parse($valid['end_date'])->startOfDay() : null,
        );
    }

    public function hasDateRange(): bool
    {
        return $this->startDate !== null || $this->endDate !== null;
    }

    public function isFiltered(): bool
    {
        return $this->commodityId !== null || $this->districtId !== null || $this->hasDateRange();
    }

    /**
     * Terapkan rentang tanggal (inklusif) pada kolom DATE tertentu.
     * Batas akhir ditulis "< hari berikutnya" supaya tetap benar bila nilai
     * tersimpan dengan jam (mis. "2026-06-10 00:00:00" di SQLite).
     */
    public function applyDateRange(Builder $query, string $column): Builder
    {
        return $query
            ->when($this->startDate, fn (Builder $q, Carbon $d) => $q->where($column, '>=', $d->toDateString()))
            ->when($this->endDate, fn (Builder $q, Carbon $d) => $q->where($column, '<', $d->copy()->addDay()->toDateString()));
    }

    /** Parameter query string untuk membuat ulang URL dengan filter ini. */
    public function toQuery(array $overrides = []): array
    {
        return array_filter([
            'commodity' => $this->commodityId,
            'district' => $this->districtId,
            'start_date' => $this->startDate?->toDateString(),
            'end_date' => $this->endDate?->toDateString(),
            ...$overrides,
        ], fn ($value) => $value !== null && $value !== '');
    }
}
