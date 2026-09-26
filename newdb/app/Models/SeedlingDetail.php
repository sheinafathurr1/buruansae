<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Detail bibit */
#[Fillable(['production_id', 'origin'])]
class SeedlingDetail extends Model
{
    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }
}
