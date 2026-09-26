<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Komoditas */
#[Fillable(['sector_id', 'name', 'growing_days', 'image'])]
class Commodity extends Model
{
    protected function casts(): array
    {
        return [
            'growing_days' => 'integer',
        ];
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function productions(): HasMany
    {
        return $this->hasMany(Production::class);
    }
}
