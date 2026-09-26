<?php

namespace App\Models;

use Database\Factories\CommodityFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/** Komoditas */
class Commodity extends Model
{
    /** @use HasFactory<CommodityFactory> */
    use HasFactory;

    /** Folder gambar komoditas di disk "public" (sama dengan aplikasi lama: storage/images). */
    public const IMAGE_DIRECTORY = 'images';

    protected $fillable = ['sector_id', 'name', 'growing_days', 'image'];

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

    /** URL gambar komoditas, atau null bila belum ada gambar. */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->image
            ? Storage::disk('public')->url(self::IMAGE_DIRECTORY.'/'.$this->image)
            : null);
    }
}
