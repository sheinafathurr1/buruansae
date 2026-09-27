<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Titik kelurahan untuk peta sebaran kelompok.
 * Kunci id/name/latitude/longitude/total_kelompok sama dengan API lama.
 */
class LocationController extends Controller
{
    public const CACHE_KEY = 'api.locations';

    public function __invoke(): JsonResponse
    {
        $ttl = (int) config('buruansae.map.cache_seconds');

        $locations = Cache::remember(self::CACHE_KEY, $ttl, function () {
            $bounds = config('buruansae.map.locations_bounds');

            // select() harus sebelum withCount(); kalau tidak, withCount memakai villages.*.
            // Alias district_name dipakai agar tidak bertabrakan dengan relasi Village::district().
            return Village::query()
                ->join('districts', 'districts.id', '=', 'villages.district_id')
                ->select(['villages.id', 'villages.name', 'villages.district_id', 'districts.name as district_name', 'villages.latitude', 'villages.longitude'])
                ->whereBetween('villages.latitude', [$bounds['south'], $bounds['north']])
                ->whereBetween('villages.longitude', [$bounds['west'], $bounds['east']])
                ->withCount([
                    'farmerGroups as total_kelompok',
                    'farmerGroups as active_kelompok' => fn (Builder $q) => $q->where('is_active', true),
                ])
                ->orderBy('villages.name')
                ->get()
                ->map(fn (Village $village) => [
                    'id' => $village->id,
                    'name' => $village->name,
                    'district_id' => (int) $village->district_id,
                    'district' => $village->district_name,
                    'latitude' => (float) $village->latitude,
                    'longitude' => (float) $village->longitude,
                    'total_kelompok' => (int) $village->total_kelompok,
                    'active_kelompok' => (int) $village->active_kelompok,
                ])
                ->all();
        });

        return response()
            ->json($locations)
            ->header('Cache-Control', "public, max-age={$ttl}");
    }
}
