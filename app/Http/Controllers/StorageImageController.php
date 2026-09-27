<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menyajikan gambar unggahan bila symlink public/storage tidak ada, mis. di
 * shared hosting yang mematikan fungsi symlink() sehingga `php artisan
 * storage:link` gagal. Bila symlink ada, server web langsung menyajikan file
 * dan route ini tidak pernah terpanggil.
 */
class StorageImageController extends Controller
{
    public function __invoke(string $path): Response
    {
        $path = 'images/'.$path;
        $disk = Storage::disk('public');

        abort_unless($disk->exists($path), 404);

        // Nama file unik dan tidak pernah ditimpa, jadi aman di-cache lama oleh browser.
        return $disk->response($path, null, ['Cache-Control' => 'public, max-age=2592000, immutable']);
    }
}
