<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecipientCategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // TODO: ganti nama KP / MM / MS dengan kepanjangan resmi DKPP.
        DB::table('recipient_categories')->upsert([
            ['code' => 'KP',       'name' => 'KP',       'sort_order' => 1],
            ['code' => 'STUNTING', 'name' => 'Stunting', 'sort_order' => 2],
            ['code' => 'MM',       'name' => 'MM',       'sort_order' => 3],
            ['code' => 'LANSIA',   'name' => 'Lansia',   'sort_order' => 4],
            ['code' => 'POSYANDU', 'name' => 'Posyandu', 'sort_order' => 5],
            ['code' => 'MS',       'name' => 'MS',       'sort_order' => 6],
            ['code' => 'SEKOLAH',  'name' => 'Sekolah',  'sort_order' => 7],
            ['code' => 'PKK',      'name' => 'PKK',      'sort_order' => 8],
            ['code' => 'LAINNYA',  'name' => 'Lainnya',  'sort_order' => 9],
            ['code' => 'DIJUAL',   'name' => 'Dijual',   'sort_order' => 20],
        ], uniqueBy: ['code'], update: ['sort_order']);

        DB::table('recipient_categories')->whereNull('created_at')->update(['created_at' => $now, 'updated_at' => $now]);
    }
}
