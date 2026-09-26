<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Detail produk olahan */
#[Fillable(['production_id', 'base_ingredient', 'brand', 'recipe', 'lab_test', 'halal_permit', 'pirt_permit'])]
class ProcessedProductDetail extends Model
{
    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }
}
