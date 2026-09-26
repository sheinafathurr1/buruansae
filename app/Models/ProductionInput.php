<?php

namespace App\Models;

use App\Enums\InputType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Pemupukan / pemberian pakan */
class ProductionInput extends Model
{
    protected $fillable = ['production_id', 'type', 'name', 'quantity', 'applied_date'];

    protected function casts(): array
    {
        return [
            'type' => InputType::class,
            'quantity' => 'decimal:2',
            'applied_date' => 'date',
        ];
    }

    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }
}
