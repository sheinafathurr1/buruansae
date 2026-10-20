<?php

namespace App\Models;

use Database\Factories\DistrictFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/** Kecamatan */
class District extends Model
{
    /** @use HasFactory<DistrictFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    public function villages(): HasMany
    {
        return $this->hasMany(Village::class);
    }

    public function farmerGroups(): HasManyThrough
    {
        return $this->hasManyThrough(FarmerGroup::class, Village::class);
    }

    /** Pilihan filter kecamatan: semua untuk admin, hanya kecamatan kelompok binaan untuk staf. */
    public function scopeWithGroupsManagedBy(Builder $query, User $user): void
    {
        if (! $user->isAdmin()) {
            $query->whereIn('id', Village::query()
                ->select('district_id')
                ->whereIn('id', FarmerGroup::query()->managedBy($user)->select('village_id')));
        }
    }
}
