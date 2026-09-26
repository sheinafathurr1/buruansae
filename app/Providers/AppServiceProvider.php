<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Tangkap query N+1 lebih awal saat pengembangan & pengujian.
        Model::preventLazyLoading(! $this->app->isProduction());

        // URL resource berbahasa Indonesia: /admin/kelompok/tambah, /admin/kelompok/{id}/ubah
        Route::resourceVerbs(['create' => 'tambah', 'edit' => 'ubah']);

        Paginator::defaultView('partials.pagination-numbered');
    }
}
