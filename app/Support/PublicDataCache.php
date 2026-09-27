<?php

namespace App\Support;

use App\Http\Controllers\Api\LocationController;
use App\Services\HomeStatistics;
use Illuminate\Support\Facades\Cache;

/**
 * Cache angka beranda & titik peta. Dikosongkan otomatis setiap kali data
 * kelompok, kelurahan, atau produksi berubah (lihat booted() di model terkait),
 * sehingga perubahan dari dashboard langsung tampil di portal publik.
 */
final class PublicDataCache
{
    public static function flush(): void
    {
        Cache::forget(HomeStatistics::CACHE_KEY);
        Cache::forget(LocationController::CACHE_KEY);
    }
}
