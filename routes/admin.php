<?php

use App\Enums\SectorType;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\CommodityController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FarmerGroupController;
use App\Http\Controllers\Admin\HarvestController;
use App\Http\Controllers\Admin\ProductionController;
use App\Http\Controllers\Admin\ProfileController;
use Illuminate\Support\Facades\Route;

/*
| Dashboard pengelola (pengganti aplikasi CodeIgniter buruansae-dashboard).
| Semua halaman di bawah /admin wajib login dengan akun aktif.
*/

Route::middleware('guest')->group(function () {
    Route::get('/admin/masuk', [LoginController::class, 'create'])->name('login');
    // Selain batas 5x per username (LoginRequest), batasi juga per IP untuk percobaan dengan banyak username.
    Route::post('/admin/masuk', [LoginController::class, 'store'])->middleware('throttle:20,1')->name('login.store');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'active'])->group(function () {
    Route::post('/keluar', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('kelompok', FarmerGroupController::class)
        ->except('show')
        ->parameters(['kelompok' => 'farmerGroup']);

    Route::resource('komoditas', CommodityController::class)
        ->except('show')
        ->parameters(['komoditas' => 'commodity']);

    Route::prefix('produksi/{sector}')
        ->whereIn('sector', array_column(SectorType::cases(), 'value'))
        ->name('productions.')
        ->group(function () {
            Route::get('/', [ProductionController::class, 'index'])->name('index');
            Route::get('/tambah', [ProductionController::class, 'create'])->name('create');
            Route::post('/', [ProductionController::class, 'store'])->name('store');
            Route::get('/{production}/ubah', [ProductionController::class, 'edit'])->name('edit');
            Route::put('/{production}', [ProductionController::class, 'update'])->name('update');
            Route::delete('/{production}', [ProductionController::class, 'destroy'])->name('destroy');
            Route::get('/{production}/panen', [HarvestController::class, 'edit'])->name('harvest.edit');
            Route::put('/{production}/panen', [HarvestController::class, 'update'])->name('harvest.update');
        });

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/kata-sandi', [ProfileController::class, 'updatePassword'])->name('profile.password');
});
