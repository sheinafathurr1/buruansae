<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\SectorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('/')->group(function () {
    Route::get('{sector}', [SectorController::class, 'index'])
        ->where('sector', 'vegetable|medicalplant|fruit|livestock|fish|waste-processing|nursery');
});
Route::get('/processed-products', [SectorController::class, 'olahanHasil']);

Route::get('/', [HomeController::class, 'index']);
Route::get('/news', [HomeController::class, 'news'])->name('blog.news');
Route::get('/news/{slug}', [HomeController::class, 'blog'])->name('blog.show');

Route::view('/map', 'home.maps');
