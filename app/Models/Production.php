<?php

namespace App\Models;

use App\Enums\PlantingCategory;
use App\Support\PublicDataCache;
use Database\Factories\ProductionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/** Satu siklus produksi (semua sektor) */
class Production extends Model
{
    /** @use HasFactory<ProductionFactory> */
    use HasFactory;

    /** Folder foto hasil panen di disk "public". */
    public const IMAGE_DIRECTORY = 'images/panen';

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

    protected static function booted(): void
    {
        static::saved(fn () => PublicDataCache::flush());
        static::deleted(fn () => PublicDataCache::flush());
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

    /** 'harvested' (sudah panen), 'late' (terlambat panen), atau 'pending' (belum panen). */
    public function status(?Carbon $today = null): string
    {
        if ($this->harvest_date !== null) {
            return 'harvested';
        }

        $today ??= Carbon::today();

        return $this->estimated_harvest_date?->lt($today) ? 'late' : 'pending';
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->image
            ? Storage::disk('public')->url(self::IMAGE_DIRECTORY.'/'.$this->image)
            : null);
    }

    /** Belum dipanen (tanggal panen belum diisi). */
    public function scopeNotHarvested(Builder $query): void
    {
        $query->whereNull('harvest_date');
    }
}
