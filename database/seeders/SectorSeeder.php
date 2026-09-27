<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectorSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('sectors')->upsert([
            ['code' => 'SAYUR',         'name' => 'Sayur',         'harvest_unit' => 'kg'],
            ['code' => 'BUAH',          'name' => 'Buah',          'harvest_unit' => 'kg'],
            ['code' => 'TANAMAN_OBAT',  'name' => 'Tanaman Obat',  'harvest_unit' => 'kg'],
            ['code' => 'IKAN',          'name' => 'Ikan',          'harvest_unit' => 'kg'],
            ['code' => 'TERNAK',        'name' => 'Ternak',        'harvest_unit' => 'kg'],
            ['code' => 'OLAHAN_HASIL',  'name' => 'Olahan Hasil',  'harvest_unit' => 'kg'],
            ['code' => 'OLAHAN_SAMPAH', 'name' => 'Olahan Sampah', 'harvest_unit' => 'kg'],
            ['code' => 'BIBIT',         'name' => 'Bibit',         'harvest_unit' => 'pohon'],
        ], uniqueBy: ['code'], update: ['name', 'harvest_unit']);

        DB::table('sectors')->whereNull('created_at')->update(['created_at' => $now, 'updated_at' => $now]);
    }
}
