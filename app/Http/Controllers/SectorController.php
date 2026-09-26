<?php

namespace App\Http\Controllers;

use App\Models\Bibit;
use App\Models\DataKelompok;
use App\Models\DataKomoditi;
use App\Models\District;
use App\Models\Fish;
use App\Models\Fruit;
use App\Models\LiveStock;
use App\Models\MedicalPlant;
use App\Models\OlahanHasil;
use App\Models\Sampah;
use App\Models\Vegetables;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SectorController extends Controller
{
    public function index(Request $request)
    {
        $sector = $request->query('sector');
        $selectedCommodity = $request->input('commodity');
        $selectedDistrict = $request->input('district');
        $districts = District::all();
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $sectorMap = [
            'sayur' => [Vegetables::class, 'nama_sayur', 'jumlah_panen', 'jumlah_tanam', 'prakiraan_jumlah_panen'],
            'tanaman_obat' => [MedicalPlant::class, 'nama_tanaman_obat', 'jumlah_panen', 'jumlah_tanam', 'prakiraan_jumlah_panen'],
            'ternak' => [LiveStock::class, 'jenis_ternak', 'jumlah_panen_kg', 'jumlah_ternak', 'prakiraan_jumlah_panen'],
            'ikan' => [Fish::class, 'jenis_ikan', 'jumlah_panen_kg', 'jumlah_ikan', 'prakiraan_jumlah_panen'],
            'buah' => [Fruit::class, 'nama_buah', 'jumlah_panen', 'jumlah_tanam', 'prakiraan_jumlah_panen'],
            'bibit' => [Bibit::class, 'nama_sayur', 'jumlah_panen', 'jumlah_semai', 'prakiraan_jumlah_panen'],
            'sampah' => [Sampah::class, 'jenis_pengolahan', 'jumlah_panen', 'jumlah_sampah', 'prakiraan_jumlah_panen'],
        ];

        $commodity = collect();
        $data = collect();
        $totalHarvest = 0;

        if (array_key_exists($sector, $sectorMap)) {
            [$model, $columnName, $harvestColumn, $estHarvestAmountinSeed, $estHarvestAmountinKg] = $sectorMap[$sector];

            try {
                $commodity = $model::select("$columnName as name")
                    ->distinct()
                    ->orderBy('name', 'asc')
                    ->get();

                // List Kelompok per Kecamatan
                $dataKelompok = DataKelompok::where('kecamatan', $selectedDistrict)
                                            ->pluck('nama_kelompok', 'id_kelompok');

                // List Kelompok per Kelurahan
                $dataKelurahan = DataKelompok::where('kecamatan', $selectedDistrict)
                    ->get(['kelurahan', 'id_kelompok'])
                    ->groupBy('kelurahan');

                // Data total panen komoditas yg dipilih
                $dataTotal = $model::when($selectedCommodity, function ($query) use ($selectedCommodity, $columnName) {
                    return $query->where($columnName, $selectedCommodity);
                })->get();

                if ($startDate && $endDate) {
                    // Untuk filtering data per kecamatan
                    $dataPerKecamatan = $model::when($selectedCommodity, function ($query) use ($selectedCommodity, $columnName, $dataKelompok) {
                        return $query->where($columnName, $selectedCommodity)
                                    ->whereIn('id_kelompok', $dataKelompok->keys());
                    })
                    ->whereBetween('waktu_panen', [$startDate, $endDate])
                    ->get();

                    if ($sector == 'bibit' || $sector == 'sampah') {
                        $dataPanenPerKelurahan = $dataKelurahan->map(function ($items, $kelurahan) use ($selectedCommodity, $model, $columnName, $harvestColumn, $dataKelompok, $startDate, $endDate) {
                            $idKelompok = $items->pluck('id_kelompok')->toArray();

                            $data = $model::when($selectedCommodity, function ($query) use ($selectedCommodity, $columnName) {
                                return $query->where($columnName, $selectedCommodity);
                            })
                                        ->whereIn('id_kelompok', $idKelompok)
                                        ->whereBetween('waktu_panen', [$startDate, $endDate])
                                        ->get();

                            $total = $data->sum($harvestColumn ?? 'jumlah_panen');

                            return $total > 0 ? [
                                'kelurahan' => $kelurahan,
                                'data' => $data->map(function ($item) use ($columnName, $dataKelompok) {
                                    return [
                                        'nama' => $item->$columnName,
                                        'id_kelompok' => $item->id_kelompok,
                                        'nama_kelompok' => $dataKelompok[$item->id_kelompok] ?? '-',
                                        'jumlah_panen' => $item->jumlah_panen ?? null,
                                        'waktu_panen' => $item->waktu_panen,
                                        'jumlah_kp' => $item->jumlah_kp,
                                        'jumlah_ms' => $item->jumlah_ms,
                                        'jumlah_sekolah' => $item->jumlah_sekolah,
                                        'jumlah_pkk' => $item->jumlah_pkk,
                                        'jumlah_posyandu' => $item->jumlah_posyandu,
                                        'jumlah_lainnya' => $item->jumlah_lainnya,
                                        'jumlah_kk' => $item->jumlah_kk,
                                        'jumlah_orang' => $item->jumlah_orang,
                                        'jumlah_dijual_pohon' => $item->jumlah_dijual_pohon ?? $item->jumlah_dijual_kg,
                                        'jumlah_dijual_orang' => $item->jumlah_dijual_orang,
                                        'jumlah_dijual_kk' => $item->jumlah_dijual_kk,
                                        'harga_jual' => $item->harga_jual,
                                    ];
                                }),
                                'total' => $total,
                            ] : null;
                        })
                        ->filter()
                        ->values();
                    } else {
                        $dataPanenPerKelurahan = $dataKelurahan->map(function ($items, $kelurahan) use ($selectedCommodity, $model, $columnName, $harvestColumn, $dataKelompok, $startDate, $endDate) {
                            $idKelompok = $items->pluck('id_kelompok')->toArray();

                            $data = $model::when($selectedCommodity, function ($query) use ($selectedCommodity, $columnName) {
                                return $query->where($columnName, $selectedCommodity);
                            })
                                        ->whereIn('id_kelompok', $idKelompok)
                                        ->whereBetween('waktu_panen', [$startDate, $endDate])
                                        ->get();

                            $total = $data->sum($harvestColumn ?? 'jumlah_panen');

                            return $total > 0 ? [
                                'kelurahan' => $kelurahan,
                                'data' => $data->map(function ($item) use ($columnName, $dataKelompok) {
                                    return [
                                        'nama' => $item->$columnName,
                                        'id_kelompok' => $item->id_kelompok,
                                        'nama_kelompok' => $dataKelompok[$item->id_kelompok] ?? '-',
                                        'jumlah_panen' => $item->jumlah_panen ?? $item->jumlah_panen_kg ?? 0,
                                        'waktu_panen' => $item->waktu_panen,
                                        'jumlah_berat_kp_kg' => $item->jumlah_berat_kp_kg,
                                        'jumlah_kepala_keluarga_kp_kk' => $item->jumlah_kepala_keluarga_kp_kk,
                                        'jumlah_orang_kp' => $item->jumlah_orang_kp,
                                        'jumlah_berat_dibagikan_stunting_kg' => $item->jumlah_berat_dibagikan_stunting_kg,
                                        'jumlah_kepala_keluarga_dibagikan_stunting' => $item->jumlah_kepala_keluarga_dibagikan_stunting,
                                        'jumlah_orang_dibagikan_stunting' => $item->jumlah_orang_dibagikan_stunting,
                                        'jumlah_berat_dibagikan_mm_kg' => $item->jumlah_berat_dibagikan_mm_kg,
                                        'jumlah_kepala_keluarga_dibagikan_mm' => $item->jumlah_kepala_keluarga_dibagikan_mm,
                                        'jumlah_orang_dibagikan_mm' => $item->jumlah_orang_dibagikan_mm,
                                        'jumlah_berat_dibagikan_lansia_kg' => $item->jumlah_berat_dibagikan_lansia_kg,
                                        'jumlah_kepala_keluarga_dibagikan_lansia' => $item->jumlah_kepala_keluarga_dibagikan_lansia,
                                        'jumlah_orang_dibagikan_lansia' => $item->jumlah_orang_dibagikan_lansia,
                                        'jumlah_berat_dibagikan_posyandu_kg' => $item->jumlah_berat_dibagikan_posyandu_kg,
                                        'jumlah_kepala_keluarga_dibagikan_posyandu' => $item->jumlah_kepala_keluarga_dibagikan_posyandu,
                                        'jumlah_orang_dibagikan_posyandu' => $item->jumlah_orang_dibagikan_posyandu,
                                        'jumlah_berat_dijual_kg' => $item->jumlah_berat_dijual_kg,
                                        'jumlah_orang_dijual' => $item->jumlah_orang_dijual,
                                        'harga_jual' => $item->harga_jual,
                                    ];
                                }),
                                'total' => $total,
                            ] : null;
                        })
                        ->filter()
                        ->values();
                    }

                    $dataBelumPanenPerKelurahan = $dataKelurahan->map(function ($items, $kelurahan) use ($selectedCommodity, $model, $columnName, $startDate, $endDate, $dataKelompok) {
                        $idKelompok = $items->pluck('id_kelompok')->toArray();

                        $data = $model::when($selectedCommodity, function ($query) use ($selectedCommodity, $columnName) {
                            return $query->where($columnName, $selectedCommodity);
                        })
                                    ->whereIn('id_kelompok', $idKelompok)
                                    ->whereBetween('waktu_prakiraan_panen', [$startDate, $endDate])
                                    ->whereNull('waktu_panen')
                                    ->get();

                        $total = $data->sum($estHarvestAmountinKg ?? 'prakiraan_jumlah_panen');
                        $avgWaktuPanen = $data->map(function ($i) {
                            if ($i->waktu_prakiraan_panen && $i->tanggal_tanam) {
                                return Carbon::parse($i->waktu_prakiraan_panen)
                                    ->diffInDays(Carbon::parse($i->tanggal_tanam));
                            }

                            return null;
                        })->filter()->avg();

                        return $total > 0 ? [
                            'kelurahan' => $kelurahan,
                            'data' => $data->map(function ($item) use ($columnName, $dataKelompok) {
                                return [
                                    'nama' => $item->$columnName,
                                    'id_kelompok' => $item->id_kelompok,
                                    'nama_kelompok' => $dataKelompok[$item->id_kelompok] ?? '-',
                                    'jumlah_panen' => $item->jumlah_panen ?? null,
                                    'waktu_prakiraan_panen' => $item->waktu_prakiraan_panen ?? null,
                                ];
                            }),
                            'waktuPanen' => $avgWaktuPanen,
                            'total' => $total,
                        ] : null;
                    })
                    ->filter()
                    ->values();

                    // dd($dataBelumPanenPerKelurahan);

                    $dataPanen7HariKedepan = $dataKelurahan->map(function ($items, $kelurahan) use ($selectedCommodity, $model, $columnName, $endDate) {
                        $idKelompok = $items->pluck('id_kelompok')->toArray();
                        $startRange = Carbon::parse($endDate);
                        $endRange = $startRange->copy()->addDays(7);
                        $data = $model::when($selectedCommodity, function ($query) use ($selectedCommodity, $columnName) {
                            return $query->where($columnName, $selectedCommodity);
                        })
                                    ->whereIn('id_kelompok', $idKelompok)
                                    ->whereBetween('waktu_prakiraan_panen', [$startRange, $endRange])
                                    ->get();

                        $total = $data->sum($estHarvestAmountinKg ?? 'prakiraan_jumlah_panen');

                        return $total > 0 ? [
                            'kelurahan' => $kelurahan,
                            'data' => $data,
                            'total' => $total,
                        ] : null;
                    })
                    ->filter()
                    ->values();

                    $totalHarvestPerKec = $dataPerKecamatan->sum($harvestColumn);
                    $totalHarvest = $dataTotal->sum($harvestColumn);
                    $totalEstHarvest = $dataPerKecamatan->sum($estHarvestAmountinKg);

                    $belumPaneninSeed = $model::when($selectedCommodity, function ($query) use ($selectedCommodity, $columnName, $dataKelompok, $startDate, $endDate) {
                        return $query->where($columnName, $selectedCommodity)
                                            ->whereBetween('waktu_prakiraan_panen', [$startDate, $endDate])
                                     ->whereIn('id_kelompok', $dataKelompok->keys())
                                    ->whereNull('waktu_panen');
                    })->sum($estHarvestAmountinSeed);

                    $belumPaneninKg = $model::when($selectedCommodity, function ($query) use ($selectedCommodity, $columnName, $dataKelompok, $startDate, $endDate) {
                        return $query->where($columnName, $selectedCommodity)
                                        ->whereBetween('waktu_prakiraan_panen', [$startDate, $endDate])
                                         ->whereIn('id_kelompok', $dataKelompok->keys())
                                        ->whereNull('waktu_panen');
                    })->sum('prakiraan_jumlah_panen');
                } else {
                    // Untuk filtering data per kecamatan
                    $dataPerKecamatan = $model::when($selectedCommodity, function ($query) use ($selectedCommodity, $columnName, $dataKelompok) {
                        return $query->where($columnName, $selectedCommodity)
                                     ->whereIn('id_kelompok', $dataKelompok->keys());
                    })->get();

                    if ($sector == 'bibit' || $sector == 'sampah') {
                        $dataPanenPerKelurahan = $dataKelurahan->map(function ($items, $kelurahan) use ($selectedCommodity, $model, $columnName, $harvestColumn, $dataKelompok) {
                            $idKelompok = $items->pluck('id_kelompok')->toArray();

                            $data = $model::when($selectedCommodity, function ($query) use ($selectedCommodity, $columnName) {
                                return $query->where($columnName, $selectedCommodity);
                            })
                                        ->whereIn('id_kelompok', $idKelompok)
                                        ->get();

                            $total = $data->sum($harvestColumn ?? 'jumlah_panen');

                            return $total > 0 ? [
                                'kelurahan' => $kelurahan,
                                'data' => $data->map(function ($item) use ($columnName, $dataKelompok) {
                                    return [
                                        'nama' => $item->$columnName,
                                        'id_kelompok' => $item->id_kelompok,
                                        'nama_kelompok' => $dataKelompok[$item->id_kelompok] ?? '-',
                                        'jumlah_panen' => $item->jumlah_panen ?? null,
                                        'waktu_panen' => $item->waktu_panen,
                                        'jumlah_kp' => $item->jumlah_kp,
                                        'jumlah_ms' => $item->jumlah_ms,
                                        'jumlah_sekolah' => $item->jumlah_sekolah,
                                        'jumlah_pkk' => $item->jumlah_pkk,
                                        'jumlah_posyandu' => $item->jumlah_posyandu,
                                        'jumlah_lainnya' => $item->jumlah_lainnya,
                                        'jumlah_kk' => $item->jumlah_kk,
                                        'jumlah_orang' => $item->jumlah_orang,
                                        'jumlah_dijual_pohon' => $item->jumlah_dijual_pohon ?? $item->jumlah_dijual_kg,
                                        'jumlah_dijual_orang' => $item->jumlah_dijual_orang,
                                        'jumlah_dijual_kk' => $item->jumlah_dijual_kk,
                                        'harga_jual' => $item->harga_jual,
                                    ];
                                }),
                                'total' => $total,
                            ] : null;
                        })
                        ->filter()
                        ->values();
                    } else {
                        $dataPanenPerKelurahan = $dataKelurahan->map(function ($items, $kelurahan) use ($selectedCommodity, $model, $columnName, $harvestColumn, $dataKelompok) {
                            $idKelompok = $items->pluck('id_kelompok')->toArray();

                            $data = $model::when($selectedCommodity, function ($query) use ($selectedCommodity, $columnName) {
                                return $query->where($columnName, $selectedCommodity);
                            })
                                        ->whereIn('id_kelompok', $idKelompok)
                                        ->get();

                            $total = $data->sum($harvestColumn ?? 'jumlah_panen');

                            return $total > 0 ? [
                                'kelurahan' => $kelurahan,
                                'data' => $data->map(function ($item) use ($columnName, $dataKelompok) {
                                    return [
                                        'nama' => $item->$columnName,
                                        'id_kelompok' => $item->id_kelompok,
                                        'nama_kelompok' => $dataKelompok[$item->id_kelompok] ?? '-',
                                        'jumlah_panen' => $item->jumlah_panen ?? $item->jumlah_panen_kg ?? 0,
                                        'waktu_panen' => $item->waktu_panen,
                                        'jumlah_berat_kp_kg' => $item->jumlah_berat_kp_kg,
                                        'jumlah_kepala_keluarga_kp_kk' => $item->jumlah_kepala_keluarga_kp_kk,
                                        'jumlah_orang_kp' => $item->jumlah_orang_kp,
                                        'jumlah_berat_dibagikan_stunting_kg' => $item->jumlah_berat_dibagikan_stunting_kg,
                                        'jumlah_kepala_keluarga_dibagikan_stunting' => $item->jumlah_kepala_keluarga_dibagikan_stunting,
                                        'jumlah_orang_dibagikan_stunting' => $item->jumlah_orang_dibagikan_stunting,
                                        'jumlah_berat_dibagikan_mm_kg' => $item->jumlah_berat_dibagikan_mm_kg,
                                        'jumlah_kepala_keluarga_dibagikan_mm' => $item->jumlah_kepala_keluarga_dibagikan_mm,
                                        'jumlah_orang_dibagikan_mm' => $item->jumlah_orang_dibagikan_mm,
                                        'jumlah_berat_dibagikan_lansia_kg' => $item->jumlah_berat_dibagikan_lansia_kg,
                                        'jumlah_kepala_keluarga_dibagikan_lansia' => $item->jumlah_kepala_keluarga_dibagikan_lansia,
                                        'jumlah_orang_dibagikan_lansia' => $item->jumlah_orang_dibagikan_lansia,
                                        'jumlah_berat_dibagikan_posyandu_kg' => $item->jumlah_berat_dibagikan_posyandu_kg,
                                        'jumlah_kepala_keluarga_dibagikan_posyandu' => $item->jumlah_kepala_keluarga_dibagikan_posyandu,
                                        'jumlah_orang_dibagikan_posyandu' => $item->jumlah_orang_dibagikan_posyandu,
                                        'jumlah_berat_dijual_kg' => $item->jumlah_berat_dijual_kg,
                                        'jumlah_orang_dijual' => $item->jumlah_orang_dijual,
                                        'harga_jual' => $item->harga_jual,
                                    ];
                                }),
                                'total' => $total,
                            ] : null;
                        })
                        ->filter()
                        ->values();
                    }
                    // dd($dataPanenPerKelurahan);
                    $dataBelumPanenPerKelurahan = $dataKelurahan->map(function ($items, $kelurahan) use ($selectedCommodity, $model, $columnName, $dataKelompok) {
                        $idKelompok = $items->pluck('id_kelompok')->toArray();
                        $data = $model::when($selectedCommodity, function ($query) use ($selectedCommodity, $columnName) {
                            return $query->where($columnName, $selectedCommodity);
                        })
                                    ->whereIn('id_kelompok', $idKelompok)
                                    ->whereNull('waktu_panen')
                                    ->get();

                        $total = $data->sum($estHarvestAmountinKg ?? 'prakiraan_jumlah_panen');
                        $avgWaktuPanen = $data->map(function ($i) {
                            if ($i->waktu_prakiraan_panen && $i->tanggal_tanam) {
                                return Carbon::parse($i->waktu_prakiraan_panen)
                                    ->diffInDays(Carbon::parse($i->tanggal_tanam));
                            }

                            return null;
                        })->filter()->avg();

                        return $total > 0 ? [
                            'kelurahan' => $kelurahan,
                            'data' => $data->map(function ($item) use ($columnName, $dataKelompok) {
                                return [
                                    'nama' => $item->$columnName,
                                    'id_kelompok' => $item->id_kelompok,
                                    'nama_kelompok' => $dataKelompok[$item->id_kelompok] ?? '-',
                                    'jumlah_panen' => $item->jumlah_panen ?? null,
                                    'waktu_prakiraan_panen' => $item->waktu_prakiraan_panen ?? null,
                                    'waktu_tanam' => $item->tanggal_tanam ?? null,
                                ];
                            }),
                            'waktuPanen' => $avgWaktuPanen,
                            'total' => $total,
                        ] : null;
                    })
                    ->filter()
                    ->values();
                    // dd($dataBelumPanenPerKelurahan);

                    $dataPanen7HariKedepan = $dataKelurahan->map(function ($items, $kelurahan) use ($selectedCommodity, $model, $columnName) {
                        $idKelompok = $items->pluck('id_kelompok')->toArray();
                        $startRange = Carbon::now();
                        $endRange = $startRange->copy()->addDays(7);
                        $data = $model::when($selectedCommodity, function ($query) use ($selectedCommodity, $columnName) {
                            return $query->where($columnName, $selectedCommodity);
                        })
                                    ->whereIn('id_kelompok', $idKelompok)
                                    ->whereBetween('waktu_prakiraan_panen', [$startRange, $endRange])
                                    ->get();

                        $total = $data->sum($estHarvestAmountinKg ?? 'prakiraan_jumlah_panen');

                        return $total > 0 ? [
                            'kelurahan' => $kelurahan,
                            'data' => $data,
                            'total' => $total,
                        ] : null;
                    })
                    ->filter()
                    ->values();
                    $totalHarvestPerKec = $dataPerKecamatan->sum($harvestColumn);
                    $totalHarvest = $dataTotal->sum($harvestColumn);
                    $totalEstHarvest = $dataPerKecamatan->sum($estHarvestAmountinKg);
                    $belumPaneninSeed = $model::when($selectedCommodity, function ($query) use ($selectedCommodity, $columnName, $dataKelompok) {
                        return $query->where($columnName, $selectedCommodity)
                                     ->whereIn('id_kelompok', $dataKelompok->keys())
                                    ->whereNull('waktu_panen');
                    })->sum($estHarvestAmountinSeed);
                    $belumPaneninKg = $model::when($selectedCommodity, function ($query) use ($selectedCommodity, $columnName, $dataKelompok) {
                        return $query->where($columnName, $selectedCommodity)
                                     ->whereIn('id_kelompok', $dataKelompok->keys())
                                    ->whereNull('waktu_panen');
                    })->sum('prakiraan_jumlah_panen');
                    // dd($selectedCommodity, $columnName);
                }

                $dataTelatPanenPerKelurahan = $dataKelurahan->map(function ($items, $kelurahan) use ($selectedCommodity, $model, $columnName) {
                    $idKelompok = $items->pluck('id_kelompok')->toArray();
                    $now = Carbon::now();
                    $data = $model::when($selectedCommodity, function ($query) use ($selectedCommodity, $columnName) {
                        return $query->where($columnName, $selectedCommodity);
                    })
                                ->whereIn('id_kelompok', $idKelompok)
                                ->whereNull('waktu_panen')
                                ->where('waktu_prakiraan_panen', '<', $now)
                                ->get();

                    $total = $data->sum($estHarvestAmountinKg ?? 'prakiraan_jumlah_panen');

                    return $total > 0 ? [
                        'kelurahan' => $kelurahan,
                        'data' => $data,
                        'total' => $total,
                    ] : null;
                })
                ->filter()
                ->values();
                $gambar = DataKomoditi::where('nama_komoditi', $selectedCommodity)
                                        ->value('gambar');
                $terlambatPanen = $model::when($selectedCommodity, function ($query) use ($selectedCommodity, $columnName, $dataKelompok) {
                    $now = Carbon::now();

                    return $query->where($columnName, $selectedCommodity)
                                 ->whereIn('id_kelompok', $dataKelompok->keys())
                                ->whereNull('waktu_panen')
                                ->where('waktu_prakiraan_panen', '<', $now);
                })->sum($estHarvestAmountinKg);
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('Invalid query in sector map: '.$e->getMessage());
            }
        }

        return view('home.sector', compact(
            'commodity', 'districts', 'selectedCommodity',
            'totalHarvest', 'totalHarvestPerKec', 'sector', 'belumPaneninSeed',
            'dataPanenPerKelurahan', 'terlambatPanen', 'dataTotal', 'dataBelumPanenPerKelurahan',
            'dataTelatPanenPerKelurahan', 'startDate', 'endDate', 'dataPanen7HariKedepan', 'belumPaneninKg', 'gambar'
        ));
    }

    // public function olahanHasil(Request $request)
    // {
    //     $sector = $request->query('sector');
    //     $selectedCommodity = $request->input('commodity');
    //     $selectedDistrict = $request->input('district');
    //     $districts = District::all();
    //     $startDate = $request->input('start_date');
    //     $endDate = $request->input('end_date');

    //     $sectorMap = [
    //         'olahan_hasil' => [OlahanHasil::class, 'jenis_olahan', 'jumlah_panen', 'prakiraan_jumlah_panen'],
    //     ];
    //     $commodity = collect();
    //     $data = collect();
    //     $gambar = DataKomoditi::where('nama_komoditi', $selectedCommodity)->value('gambar');
    //     $totalHarvest = 0;
    //     if (array_key_exists($sector, $sectorMap)) {
    //         [$model, $columnName, $harvestColumn] = $sectorMap[$sector];
    //         try {
    //             $dataOlahanHasil = collect();

    //             $commodity = $model::select("$columnName as name")
    //             ->distinct()
    //             ->orderBy('name', 'asc')
    //             ->get();
    //             // dd($selectedCommodity);

    //             $dataOlahanHasil = OlahanHasil::where('jenis_olahan', $selectedCommodity)->first();
    //             // dd($dataOlahanHasil);

    //             // List Kelompok per Kecamatan
    //             $dataKelompok = DataKelompok::where('kecamatan', $selectedDistrict)
    //                                         ->pluck('nama_kelompok', 'id_kelompok');

    //             // List Kelompok per Kelurahan
    //             $dataKelurahan = DataKelompok::where('kecamatan', $selectedDistrict)
    //                 ->get(['kelurahan', 'id_kelompok'])
    //                 ->groupBy('kelurahan');
    //             $dataTotal = $model::when($selectedCommodity, function ($query) use ($selectedCommodity, $columnName) {
    //                 return $query->where($columnName, $selectedCommodity);
    //             })->get();
    //             $totalHarvest = $dataTotal->sum($harvestColumn);
    //             // Data total panen komoditas yg dipilih
    //         } catch (\Illuminate\Database\QueryException $e) {
    //             Log::error('Invalid query in sector map: '.$e->getMessage());
    //         }
    //     }

    //     return view('home.olahanhasil', compact(
    //         'sector', 'districts', 'selectedCommodity', 'commodity',
    //         'startDate', 'endDate', 'gambar', 'totalHarvest', 'dataOlahanHasil'
    //     ));
    // }
}
