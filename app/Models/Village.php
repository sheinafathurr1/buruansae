<?php

namespace App\Models;

use Database\Factories\VillageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Kelurahan */
class Village extends Model
{
    /** @use HasFactory<VillageFactory> */
    use HasFactory;

    protected $fillable = ['district_id', 'name', 'latitude', 'longitude'];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function farmerGroups(): HasMany
    {
        return $this->hasMany(FarmerGroup::class);
    }

    public function monthlyDistributionRecaps(): HasMany
    {
        return $this->hasMany(MonthlyDistributionRecap::class);
    }
}
