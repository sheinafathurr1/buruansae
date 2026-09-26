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

    /** URL gambar komoditas, atau null bila belum ada gambar atau filenya tidak ada. */
    protected function imageUrl(): Attribute
    {
        // Sebagian foto lama tidak ikut tersalin; jangan tampilkan gambar rusak.
        return Attribute::get(function (): ?string {
            $path = self::IMAGE_DIRECTORY.'/'.$this->image;

            return $this->image && Storage::disk('public')->exists($path) ? Storage::disk('public')->url($path) : null;
        });
    }
}
