<?php

namespace App\Models;

use App\Support\PublicDataCache;
use Database\Factories\FarmerGroupFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/** Kelompok */
class FarmerGroup extends Model
{
    /** @use HasFactory<FarmerGroupFactory> */
    use HasFactory, SoftDeletes;

    /** Folder foto lahan & ketua di disk "public". */
    public const PHOTO_DIRECTORY = 'images/kelompok';

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

    protected static function booted(): void
    {
        static::saved(fn () => PublicDataCache::flush());
        static::deleted(fn () => PublicDataCache::flush());
        static::restored(fn () => PublicDataCache::flush());
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function productions(): HasMany
    {
        return $this->hasMany(Production::class);
    }

    protected function landPhotoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->photoUrl($this->land_photo));
    }

    protected function leaderPhotoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->photoUrl($this->leader_photo));
    }

    private function photoUrl(?string $file): ?string
    {
        return $file ? Storage::disk('public')->url(self::PHOTO_DIRECTORY.'/'.$file) : null;
    }
}
