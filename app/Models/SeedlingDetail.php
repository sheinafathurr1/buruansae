<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Detail bibit */
class SeedlingDetail extends Model
{
    protected $fillable = ['production_id', 'origin'];

    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }
}
