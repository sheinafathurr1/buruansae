<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
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

    /** @return string nama file (tanpa folder) */
    public static function store(UploadedFile $file, string $directory): string
    {
        $extension = strtolower($file->extension() ?: 'jpg');
        $name = Str::random(32).'.'.$extension;
        $contents = self::downscale($file->getRealPath(), $extension) ?? $file->get();

        Storage::disk('public')->put($directory.'/'.$name, $contents);

        return $name;
    }

    public static function delete(?string $name, string $directory): void
    {
        if ($name) {
            Storage::disk('public')->delete($directory.'/'.$name);
        }
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
