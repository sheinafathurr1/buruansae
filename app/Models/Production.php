<?php

namespace App\Models;

use App\Enums\PlantingCategory;
use Database\Factories\ProductionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** Satu siklus produksi (semua sektor) */
class Production extends Model
{
    /** @use HasFactory<ProductionFactory> */
    use HasFactory;

    protected $fillable = [
        'farmer_group_id', 'commodity_id', 'planting_category', 'start_date', 'initial_quantity',
        'estimated_harvest_date', 'estimated_harvest_quantity', 'harvest_date', 'harvest_quantity',
        'harvest_head_count', 'selling_price', 'notes', 'image',
    ];

    protected function casts(): array
    {
        return [
            'planting_category' => PlantingCategory::class,
            'start_date' => 'date',
            'estimated_harvest_date' => 'date',
            'harvest_date' => 'date',
            'initial_quantity' => 'decimal:2',
            'estimated_harvest_quantity' => 'decimal:3',
            'harvest_quantity' => 'decimal:3',
            'harvest_head_count' => 'decimal:2',
            'selling_price' => 'integer',
        ];
    }

    public function farmerGroup(): BelongsTo
    {
        return $this->belongsTo(FarmerGroup::class);
    }

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }

    public function inputs(): HasMany
    {
        return $this->hasMany(ProductionInput::class);
    }

    public function processedProductDetail(): HasOne
    {
        return $this->hasOne(ProcessedProductDetail::class);
    }

    public function seedlingDetail(): HasOne
    {
        return $this->hasOne(SeedlingDetail::class);
    }

    /** Production::inSector('SAYUR')->get() */
    public function scopeInSector(Builder $query, string $sectorCode): void
    {
        $query->whereHas('commodity.sector', fn (Builder $q) => $q->where('code', $sectorCode));
    }

    /** Production::harvestedBetween('2025-01-01', '2025-12-31')->get() */
    public function scopeHarvestedBetween(Builder $query, string $from, string $to): void
    {
        $query->whereBetween('harvest_date', [$from, $to]);
    }

    /** Belum dipanen (tanggal panen belum diisi). */
    public function scopeNotHarvested(Builder $query): void
    {
        $query->whereNull('harvest_date');
    }
}
