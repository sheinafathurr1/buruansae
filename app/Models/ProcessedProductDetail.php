<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Detail produk olahan */
class ProcessedProductDetail extends Model
{
    protected $fillable = ['production_id', 'base_ingredient', 'brand', 'recipe', 'lab_test', 'halal_permit', 'pirt_permit'];

    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }
}
