<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataKelurahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $sw_lat = -7.05;
        $sw_lng = 107.45;
        $ne_lat = -6.80;
        $ne_lng = 107.75;

        $locations = DataKelurahan::select([
            'id',
            'name',
            'latitude',
            'longitude',
            DB::raw('(SELECT COUNT(*) FROM data_kelompok WHERE data_kelompok.kelurahan  = data_kelurahan.name) AS total_kelompok'),
        ])->whereBetween('latitude', [$sw_lat, $ne_lat])
                             ->whereBetween('longitude', [$sw_lng, $ne_lng])
                             ->get(['id', 'name', 'latitude', 'longitude']);

        return response()->json($locations);
    }
}
