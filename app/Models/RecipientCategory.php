<?php

namespace App\Models;

use App\Enums\DistributionGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Kategori penerima hasil: KP, STUNTING, MM, LANSIA, POSYANDU, ..., DIJUAL */
class RecipientCategory extends Model
{
    protected $fillable = ['code', 'name', 'sort_order'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }

    /** Kelompok tampilan: konsumsi pribadi, dibagikan, atau dijual. */
    public function group(): DistributionGroup
    {
        return DistributionGroup::fromCategoryCode($this->code);
    }
}
