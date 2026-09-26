<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Data CONTOH untuk mencoba aplikasi di lokal/staging.
 *
 *     php artisan migrate:fresh --seed
 *     php artisan db:seed --class=DemoSeeder
 *
 * Nama kecamatan & kelurahan memakai nama asli Kota Bandung, tetapi koordinat,
 * kelompok, dan angka produksi DIBUAT-BUAT. Data sebenarnya berasal dari
 * database/sql/import_data_lama.sql. Seeder ini menolak berjalan di production
 * atau bila tabel kelompok sudah berisi data.
 */
class DemoSeeder extends Seeder
{
    /** Kecamatan => [lat, lng pusat perkiraan, daftar kelurahan]. */
    private const AREAS = [
        'ANDIR' => [-6.9105, 107.5780, ['CAMPAKA', 'CIROYOM', 'DUNGUS CARIANG', 'GARUDA', 'KEBON JERUK', 'MALEBER']],
        'ANTAPANI' => [-6.9145, 107.6600, ['ANTAPANI KIDUL', 'ANTAPANI KULON', 'ANTAPANI TENGAH', 'ANTAPANI WETAN']],
        'ARCAMANIK' => [-6.9160, 107.6770, ['CISARANTEN BINA HARAPAN', 'CISARANTEN ENDAH', 'CISARANTEN KULON', 'SUKAMISKIN']],
        'ASTANAANYAR' => [-6.9280, 107.6000, ['CIBADAK', 'KARANG ANYAR', 'KARASAK', 'NYENGSERET', 'PANJUNAN', 'PELINDUNG HEWAN']],
        'BABAKAN CIPARAY' => [-6.9380, 107.5800, ['BABAKAN', 'BABAKAN CIPARAY', 'CIRANGRANG', 'MARGAHAYU UTARA', 'MARGASUKA', 'SUKAHAJI']],
        'BANDUNG KIDUL' => [-6.9570, 107.6320, ['BATUNUNGGAL', 'KUJANGSARI', 'MENGGER', 'WATES']],
        'BANDUNG KULON' => [-6.9270, 107.5700, ['CARINGIN', 'CIBUNTU', 'CIGONDEWAH KALER', 'CIGONDEWAH KIDUL', 'CIJERAH', 'GEMPOLSARI', 'WARUNG MUNCANG']],
        'BANDUNG WETAN' => [-6.9010, 107.6150, ['CIHAPIT', 'CITARUM', 'TAMANSARI']],
        'BATUNUNGGAL' => [-6.9300, 107.6280, ['BINONG', 'CIBANGKONG', 'GUMURUH', 'KACAPIRING', 'KEBON GEDANG', 'KEBONWARU', 'MALEER', 'SAMOJA']],
        'BOJONGLOA KALER' => [-6.9300, 107.5880, ['BABAKAN ASIH', 'BABAKAN TAROGONG', 'JAMIKA', 'KOPO', 'SUKA ASIH']],
        'BOJONGLOA KIDUL' => [-6.9480, 107.5950, ['CIBADUYUT', 'CIBADUYUT KIDUL', 'CIBADUYUT WETAN', 'KEBON LEGA', 'MEKARWANGI', 'SITUSAEUR']],
        'BUAHBATU' => [-6.9500, 107.6500, ['CIJAWURA', 'JATISARI', 'MARGASARI', 'SEKEJATI']],
        'CIBEUNYING KALER' => [-6.8960, 107.6330, ['CIGADUNG', 'CIHAUR GEULIS', 'NEGLASARI', 'SUKALUYU']],
        'CIBEUNYING KIDUL' => [-6.9060, 107.6450, ['CICADAS', 'CIKUTRA', 'PADASUKA', 'PASIRLAYUNG', 'SUKAMAJU', 'SUKAPADA']],
        'CIBIRU' => [-6.9180, 107.7200, ['CIPADUNG', 'CISURUPAN', 'PALASARI', 'PASIRBIRU']],
        'CICENDO' => [-6.9050, 107.5900, ['ARJUNA', 'HUSEN SASTRANEGARA', 'PAJAJARAN', 'PAMOYANAN', 'PASIRKALIKI', 'SUKARAJA']],
        'CIDADAP' => [-6.8650, 107.6030, ['CIUMBULEUIT', 'HEGARMANAH', 'LEDENG']],
        'CINAMBO' => [-6.9330, 107.6950, ['BABAKAN PENGHULU', 'CISARANTEN WETAN', 'PAKEMITAN', 'SUKAMULYA']],
        'COBLONG' => [-6.8870, 107.6130, ['CIPAGANTI', 'DAGO', 'LEBAKGEDE', 'LEBAKSILIWANGI', 'SADANGSERANG', 'SEKELOA']],
        'GEDEBAGE' => [-6.9500, 107.6900, ['CIMINCRANG', 'CISARANTEN KIDUL', 'RANCABOLANG', 'RANCANUMPANG']],
        'KIARACONDONG' => [-6.9250, 107.6450, ['BABAKAN SARI', 'BABAKAN SURABAYA', 'CICAHEUM', 'KEBON JAYANTI', 'KEBUN KANGKUNG', 'SUKAPURA']],
        'LENGKONG' => [-6.9300, 107.6200, ['BURANGRANG', 'CIJAGRA', 'CIKAWAO', 'LINGKAR SELATAN', 'MALABAR', 'PALEDANG', 'TURANGGA']],
        'MANDALAJATI' => [-6.9050, 107.6680, ['JATIHANDAP', 'KARANG PAMULANG', 'PASIR IMPUN', 'SINDANG JAYA']],
        'PANYILEUKAN' => [-6.9320, 107.7080, ['CIPADUNG KIDUL', 'CIPADUNG KULON', 'CIPADUNG WETAN', 'MEKARMULYA']],
        'RANCASARI' => [-6.9500, 107.6720, ['CIPAMOKOLAN', 'DARWATI', 'MANJAHLEGA', 'MEKAR JAYA']],
        'REGOL' => [-6.9400, 107.6100, ['ANCOL', 'BALONGGEDE', 'CIATEUL', 'CIGERELENG', 'CISEUREUH', 'PASIRLUYU', 'PUNGKUR']],
        'SUKAJADI' => [-6.8900, 107.5950, ['CIPEDES', 'PASTEUR', 'SUKABUNGAH', 'SUKAGALIH', 'SUKAWARNA']],
        'SUKASARI' => [-6.8700, 107.5850, ['GEGERKALONG', 'ISOLA', 'SARIJADI', 'SUKARASA']],
        'SUMUR BANDUNG' => [-6.9150, 107.6150, ['BABAKAN CIAMIS', 'BRAGA', 'KEBON PISANG', 'MERDEKA']],
        'UJUNGBERUNG' => [-6.9120, 107.7000, ['CIGENDING', 'PASANGGRAHAN', 'PASIRENDAH', 'PASIRJATI', 'PASIRWANGI']],
    ];

    /** Kode sektor => [komoditas => durasi tanam (hari)]. */
    private const COMMODITIES = [
        'SAYUR' => ['KANGKUNG' => 25, 'BAYAM' => 30, 'SAWI' => 35, 'SELADA' => 40, 'CABAI RAWIT' => 90, 'TOMAT' => 75, 'TERONG' => 80],
        'BUAH' => ['JERUK' => 180, 'JAMBU KRISTAL' => 150, 'PEPAYA' => 210, 'MELON' => 70],
        'TANAMAN_OBAT' => ['JAHE' => 200, 'KUNYIT' => 200, 'SEREH' => 120, 'LENGKUAS' => 200],
        'IKAN' => ['LELE' => 90, 'NILA' => 120, 'GURAME' => 180],
        'TERNAK' => ['AYAM KAMPUNG' => 90, 'AYAM PETELUR' => 120, 'DOMBA' => 180],
        'OLAHAN_HASIL' => ['KERIPIK SINGKONG' => null, 'SAMBAL' => null, 'MINUMAN JAHE' => null],
        'OLAHAN_SAMPAH' => ['KOMPOS' => 30, 'MAGGOT BSF' => 20, 'ECO ENZYME' => 90],
        'BIBIT' => ['CABAI' => 30, 'TOMAT' => 25, 'SELADA' => 20],
    ];

    /** Kode sektor => [min, max] jumlah awal, [min, max] perkiraan hasil. */
    private const QUANTITIES = [
        'SAYUR' => [[20, 200], [5, 60]],
        'BUAH' => [[5, 40], [10, 80]],
        'TANAMAN_OBAT' => [[10, 100], [3, 30]],
        'IKAN' => [[100, 1000], [20, 150]],
        'TERNAK' => [[10, 100], [10, 100]],
        'OLAHAN_HASIL' => [[0, 0], [5, 50]],
        'OLAHAN_SAMPAH' => [[50, 500], [20, 200]],
        'BIBIT' => [[100, 1000], [80, 900]],
    ];

    private const GROUP_NAMES = [
        'Mekar Sari', 'Sauyunan', 'Rahayu', 'Harapan Jaya', 'Walagri', 'Hejo Lestari', 'Sabilulungan',
        'Tunas Harapan', 'Bina Tani', 'Sri Rejeki', 'Asri', 'Berkah', 'Mandiri', 'Motekar', 'Someah',
        'Gemah Ripah', 'Cahaya', 'Mitra Tani', 'Lembur Hejo', 'Sajuta Saratus', 'Seger Waras', 'Makmur',
    ];

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('DemoSeeder tidak boleh dijalankan di production.');

            return;
        }

        if (DB::table('farmer_groups')->exists()) {
            $this->command?->warn('Tabel farmer_groups sudah berisi data; DemoSeeder dilewati.');

            return;
        }

        $this->call([SectorSeeder::class, RecipientCategorySeeder::class]);

        mt_srand(2026);
        $now = now();
        $today = Carbon::today();

        $sectorIds = DB::table('sectors')->pluck('id', 'code');
        $categoryIds = DB::table('recipient_categories')->pluck('id', 'code');

        [$villageIds] = $this->seedAreas($now);
        $groupIds = $this->seedGroups($villageIds, $now);
        $commodities = $this->seedCommodities($sectorIds, $now);

        $productions = $distributions = $inputs = $processed = $seedlings = [];
        $productionId = 0;

        foreach ($groupIds as $groupId) {
            // Tiap kelompok aktif di 1–3 sektor; sayur paling umum.
            $sectors = array_unique(array_merge(['SAYUR'], $this->pickMany(array_keys(self::COMMODITIES), mt_rand(0, 2))));

            foreach ($sectors as $code) {
                foreach (range(1, mt_rand(2, 6)) as $_) {
                    [$commodityId, $growingDays] = $commodities[$code][array_rand($commodities[$code])];
                    [[$initMin, $initMax], [$estMin, $estMax]] = self::QUANTITIES[$code];
                    $estimate = $this->decimal($estMin, $estMax);
                    $start = $today->copy()->subDays(mt_rand(5, 260));
                    $row = [
                        'id' => ++$productionId,
                        'farmer_group_id' => $groupId,
                        'commodity_id' => $commodityId,
                        'planting_category' => in_array($code, ['SAYUR', 'BUAH', 'TANAMAN_OBAT'], true)
                            ? ['seed', 'seedling', 'tree'][mt_rand(0, $code === 'BUAH' ? 2 : 1)] : null,
                        'start_date' => $start->toDateString(),
                        'initial_quantity' => $initMax > 0 ? mt_rand($initMin, $initMax) : null,
                        'estimated_harvest_date' => null,
                        'estimated_harvest_quantity' => null,
                        'harvest_date' => null,
                        'harvest_quantity' => null,
                        'harvest_head_count' => null,
                        'selling_price' => null,
                        'notes' => null,
                        'image' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    if ($growingDays === null) {
                        // Produk olahan: diproduksi & tercatat di hari yang sama.
                        $row['harvest_date'] = $row['start_date'];
                        $row['harvest_quantity'] = $estimate;
                    } else {
                        $estimatedDate = $start->copy()->addDays($growingDays + mt_rand(-5, 5));
                        $row['estimated_harvest_date'] = $estimatedDate->toDateString();
                        $row['estimated_harvest_quantity'] = $estimate;

                        // Yang perkiraannya sudah lewat: sebagian besar sudah panen, sisanya terlambat.
                        if ($estimatedDate->lt($today->copy()->subDays(3)) && mt_rand(1, 100) <= 85) {
                            $harvestDate = $estimatedDate->copy()->addDays(mt_rand(-4, 6));
                            $row['harvest_date'] = min($harvestDate, $today)->toDateString();
                            $row['harvest_quantity'] = round($estimate * mt_rand(70, 120) / 100, 3);
                        }
                    }

                    if ($row['harvest_quantity'] !== null) {
                        if (in_array($code, ['IKAN', 'TERNAK'], true)) {
                            $row['harvest_head_count'] = (int) round($row['harvest_quantity'] * ($code === 'IKAN' ? 7 : 1.2));
                        }
                        $row['selling_price'] = mt_rand(8, 40) * 1000;
                        array_push($distributions, ...$this->distributionsFor($productionId, $code, (float) $row['harvest_quantity'], $categoryIds, $now));
                    }

                    $productions[] = $row;

                    if ($code === 'BUAH' && mt_rand(1, 100) <= 60) {
                        $inputs[] = ['production_id' => $productionId, 'type' => 'fertilizer', 'name' => ['Pupuk Kandang', 'Kompos', 'NPK'][mt_rand(0, 2)], 'quantity' => mt_rand(2, 20), 'applied_date' => $start->copy()->addDays(14)->toDateString(), 'created_at' => $now, 'updated_at' => $now];
                    }
                    if (in_array($code, ['IKAN', 'TERNAK'], true)) {
                        $inputs[] = ['production_id' => $productionId, 'type' => 'feed', 'name' => $code === 'IKAN' ? 'Pelet' : 'Konsentrat', 'quantity' => mt_rand(5, 60), 'applied_date' => $start->copy()->addDays(7)->toDateString(), 'created_at' => $now, 'updated_at' => $now];
                    }
                    if ($code === 'OLAHAN_HASIL') {
                        $processed[] = [
                            'production_id' => $productionId,
                            'base_ingredient' => ['Singkong', 'Cabai', 'Jahe'][mt_rand(0, 2)],
                            'brand' => 'SAE '.self::GROUP_NAMES[mt_rand(0, count(self::GROUP_NAMES) - 1)],
                            'recipe' => null,
                            'lab_test' => mt_rand(0, 1) ? 'Sudah' : null,
                            'halal_permit' => mt_rand(0, 1) ? 'Sudah' : 'Dalam proses',
                            'pirt_permit' => mt_rand(0, 1) ? 'Sudah' : null,
                            'created_at' => $now, 'updated_at' => $now,
                        ];
                    }
                    if ($code === 'BIBIT') {
                        $seedlings[] = ['production_id' => $productionId, 'origin' => ['DKPP', 'Swadaya', 'Bantuan CSR'][mt_rand(0, 2)], 'created_at' => $now, 'updated_at' => $now];
                    }
                }
            }
        }

        foreach ([
            'productions' => $productions,
            'distributions' => $distributions,
            'production_inputs' => $inputs,
            'processed_product_details' => $processed,
            'seedling_details' => $seedlings,
        ] as $table => $rows) {
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table($table)->insert($chunk);
            }
        }

        $this->command?->info(sprintf(
            'Data contoh: %d kelompok, %d produksi, %d distribusi.',
            count($groupIds), count($productions), count($distributions),
        ));
    }

    /** @return array{0: list<int>} */
    private function seedAreas(Carbon $now): array
    {
        $villageIds = [];

        foreach (self::AREAS as $district => [$lat, $lng, $villages]) {
            $districtId = DB::table('districts')->insertGetId(['name' => $district, 'created_at' => $now, 'updated_at' => $now]);

            foreach ($villages as $i => $village) {
                $angle = 2 * M_PI * $i / count($villages);
                $villageIds[] = DB::table('villages')->insertGetId([
                    'district_id' => $districtId,
                    'name' => $village,
                    'latitude' => round($lat + 0.007 * sin($angle), 7),
                    'longitude' => round($lng + 0.008 * cos($angle), 7),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        return [$villageIds];
    }

    /** @return list<int> */
    private function seedGroups(array $villageIds, Carbon $now): array
    {
        $ids = [];

        foreach ($villageIds as $villageId) {
            for ($n = mt_rand(0, 3); $n > 0; $n--) {
                $rw = mt_rand(1, 15);
                $ids[] = DB::table('farmer_groups')->insertGetId([
                    'village_id' => $villageId,
                    'rw' => $rw,
                    'name' => 'Buruan SAE '.self::GROUP_NAMES[mt_rand(0, count(self::GROUP_NAMES) - 1)].' RW '.str_pad((string) $rw, 2, '0', STR_PAD_LEFT),
                    'leader_name' => ['Ibu Siti', 'Bapak Ujang', 'Ibu Euis', 'Bapak Asep', 'Ibu Neneng', 'Bapak Dadang', 'Ibu Yanti'][mt_rand(0, 6)],
                    'land_area_m2' => mt_rand(20, 600),
                    'land_status' => ['Milik Pribadi', 'Fasos/Fasum', 'Pinjam Pakai'][mt_rand(0, 2)],
                    'is_active' => mt_rand(1, 100) <= 88,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        return $ids;
    }

    /** @return array<string, list<array{0: int, 1: ?int}>> */
    private function seedCommodities($sectorIds, Carbon $now): array
    {
        $result = [];

        foreach (self::COMMODITIES as $code => $items) {
            foreach ($items as $name => $days) {
                $result[$code][] = [
                    DB::table('commodities')->insertGetId([
                        'sector_id' => $sectorIds[$code],
                        'name' => $name,
                        'growing_days' => $days,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]),
                    $days,
                ];
            }
        }

        return $result;
    }

    private function distributionsFor(int $productionId, string $sectorCode, float $harvest, $categoryIds, Carbon $now): array
    {
        $shared = in_array($sectorCode, ['BIBIT', 'OLAHAN_SAMPAH'], true)
            ? ['MS', 'SEKOLAH', 'PKK', 'POSYANDU', 'LAINNYA']
            : ['STUNTING', 'MM', 'LANSIA', 'POSYANDU'];

        $portions = ['KP' => mt_rand(15, 40)];
        foreach ($this->pickMany($shared, mt_rand(1, 3)) as $code) {
            $portions[$code] = mt_rand(5, 20);
        }
        $portions['DIJUAL'] = max(0, 100 - array_sum($portions));

        $rows = [];
        foreach ($portions as $code => $percent) {
            if ($percent === 0) {
                continue;
            }
            $households = mt_rand(1, 12);
            $rows[] = [
                'production_id' => $productionId,
                'recipient_category_id' => $categoryIds[$code],
                'quantity' => round($harvest * $percent / 100, 3),
                'household_count' => $code === 'DIJUAL' ? null : $households,
                'person_count' => $households * mt_rand(2, 5),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $rows;
    }

    private function pickMany(array $items, int $count): array
    {
        if ($count <= 0) {
            return [];
        }
        $keys = (array) array_rand($items, min($count, count($items)));

        return array_values(array_intersect_key($items, array_flip($keys)));
    }

    private function decimal(int $min, int $max): float
    {
        return round($min + mt_rand() / mt_getrandmax() * ($max - $min), 3);
    }
}
