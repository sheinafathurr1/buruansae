<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/** Sektor: SAYUR, BUAH, TANAMAN_OBAT, IKAN, TERNAK, OLAHAN_HASIL, OLAHAN_SAMPAH, BIBIT */
#[Fillable(['code', 'name', 'harvest_unit'])]
class Sector extends Model
{
    public function commodities(): HasMany
    {
        return $this->hasMany(Commodity::class);
    }

    public function productions(): HasManyThrough
    {
        return $this->hasManyThrough(Production::class, Commodity::class);
    }
}
