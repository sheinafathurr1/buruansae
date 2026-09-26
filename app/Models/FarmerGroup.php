<?php

namespace App\Models;

use Database\Factories\FarmerGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Kelompok */
class FarmerGroup extends Model
{
    /** @use HasFactory<FarmerGroupFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'village_id', 'rw', 'name', 'leader_name', 'phone', 'extension_officer', 'facilitator',
        'land_area_m2', 'land_status', 'is_active', 'status_note', 'land_photo', 'leader_photo',
        'description_url',
    ];

    protected function casts(): array
    {
        return [
            'rw' => 'integer',
            'land_area_m2' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function productions(): HasMany
    {
        return $this->hasMany(Production::class);
    }
}
