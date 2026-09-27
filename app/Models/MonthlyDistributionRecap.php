<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Rekap distribusi bulanan per kelurahan */
class MonthlyDistributionRecap extends Model
{
    protected $fillable = ['village_id', 'period_date', 'harvest', 'self_consumption', 'distributed', 'sold'];

    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'harvest' => 'decimal:3',
            'self_consumption' => 'decimal:3',
            'distributed' => 'decimal:3',
            'sold' => 'decimal:3',
        ];
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }
}
