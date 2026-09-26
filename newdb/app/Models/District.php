<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/** Kecamatan */
#[Fillable(['name'])]
class District extends Model
{
    public function villages(): HasMany
    {
        return $this->hasMany(Village::class);
    }

    public function farmerGroups(): HasManyThrough
    {
        return $this->hasManyThrough(FarmerGroup::class, Village::class);
    }
}
