<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Data acuan yang dibutuhkan aplikasi. Aman dijalankan berulang (upsert).
     *
     * Data contoh untuk lokal/demo: php artisan db:seed --class=DemoSeeder
     */
    public function run(): void
    {
        $this->call([
            SectorSeeder::class,
            RecipientCategorySeeder::class,
        ]);
    }
}
