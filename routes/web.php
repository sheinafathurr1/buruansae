<?php

use App\Enums\SectorType;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\SectorVillageController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{slug}', [NewsController::class, 'show'])->name('news.show');

Route::get('/map', MapController::class)->name('map');

// URL sektor dipertahankan dari aplikasi lama: /vegetable, /fish, /nursery, ...
Route::prefix('{sector}')
    ->whereIn('sector', array_column(SectorType::cases(), 'value'))
    ->name('sectors.')
    ->group(function () {
        Route::get('/', [SectorController::class, 'show'])->name('show');
        Route::get('/kelurahan/{village}/panen', [SectorVillageController::class, 'harvested'])
            ->name('villages.harvested');
        Route::get('/kelurahan/{village}/belum-panen', [SectorVillageController::class, 'pending'])
            ->name('villages.pending');
    });
