<?php

namespace App\Support;

use App\Models\Commodity;
use App\Models\FarmerGroup;
use App\Models\Production;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menyimpan foto unggahan di disk "public" dengan nama acak. Foto dari ponsel
 * yang lebih lebar dari MAX_WIDTH diperkecil (bila ekstensi GD tersedia),
 * menggantikan kompresi Intervention Image di aplikasi lama.
 */
final class ImageStore
{
    public const MAX_WIDTH = 1600;

    /**
     * Tabel & kolom yang menyimpan nama file di tiap folder. Foto dari aplikasi
     * lama dipakai bersama oleh banyak baris (duplikatnya sudah dirapikan), jadi
     * file baru dihapus bila tidak ada baris lain yang masih memakainya.
     */
    private const REFERENCES = [
        Production::IMAGE_DIRECTORY => ['productions' => ['image']],
        Commodity::IMAGE_DIRECTORY => ['commodities' => ['image']],
        // Termasuk kelompok yang di-soft delete: fotonya kembali bila dipulihkan.
        FarmerGroup::PHOTO_DIRECTORY => ['farmer_groups' => ['land_photo', 'leader_photo']],
    ];

    /** @return string nama file (tanpa folder) */
    public static function store(UploadedFile $file, string $directory): string
    {
        $extension = strtolower($file->extension() ?: 'jpg');
        $name = Str::random(32).'.'.$extension;
        $contents = self::downscale($file->getRealPath(), $extension) ?? $file->get();

        Storage::disk('public')->put($directory.'/'.$name, $contents);

        return $name;
    }

    /** Panggil SETELAH baris pemilik file diubah/dihapus. */
    public static function delete(?string $name, string $directory): void
    {
        if ($name && ! self::isReferenced($name, $directory)) {
            Storage::disk('public')->delete($directory.'/'.$name);
        }
    }

    private static function isReferenced(string $name, string $directory): bool
    {
        foreach (self::REFERENCES[$directory] ?? [] as $table => $columns) {
            $query = DB::table($table)->where(function ($query) use ($columns, $name) {
                foreach ($columns as $column) {
                    $query->orWhere($column, $name);
                }
            });

            if ($query->exists()) {
                return true;
            }
        }

        return false;
    }

    private static function downscale(string $path, string $extension): ?string
    {
        if (! function_exists('imagecreatefromstring') || ! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return null;
        }

        $info = @getimagesize($path);
        if (! $info || $info[0] <= self::MAX_WIDTH) {
            return null;
        }

        $source = @imagecreatefromstring((string) file_get_contents($path));
        if (! $source) {
            return null;
        }

        $scaled = imagescale($source, self::MAX_WIDTH);
        if ($extension === 'png') {
            imagealphablending($scaled, false);
            imagesavealpha($scaled, true);
        }

        ob_start();
        match ($extension) {
            'png' => imagepng($scaled, null, 8),
            'webp' => imagewebp($scaled, null, 82),
            default => imagejpeg($scaled, null, 82),
        };

        return ob_get_clean() ?: null;
    }
}
