<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Kategori penerima hasil: KP, STUNTING, MM, LANSIA, POSYANDU, ..., DIJUAL */
#[Fillable(['code', 'name', 'sort_order'])]
class RecipientCategory extends Model
{
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
}
