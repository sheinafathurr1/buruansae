<?php

namespace App\Models;

use Database\Factories\DistributionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Hasil yang diberikan/dijual ke satu kategori penerima */
class Distribution extends Model
{
    /** @use HasFactory<DistributionFactory> */
    use HasFactory;

    protected $fillable = ['production_id', 'recipient_category_id', 'quantity', 'household_count', 'person_count'];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'household_count' => 'integer',
            'person_count' => 'integer',
        ];
    }

    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }

    public function recipientCategory(): BelongsTo
    {
        return $this->belongsTo(RecipientCategory::class);
    }
}
