<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Kelurahan */
#[Fillable(['district_id', 'name', 'latitude', 'longitude'])]
class Village extends Model
{
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
