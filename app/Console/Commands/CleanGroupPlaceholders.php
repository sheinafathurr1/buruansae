<?php

namespace App\Console\Commands;

use App\Support\PublicDataCache;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Mengosongkan isian pengganti "kosong" dari aplikasi lama di data kelompok:
 * "0", "00", atau "-" di nama ketua, kontak, status lahan, dan tautan, serta
 * luas lahan 0 m². Spasi di awal/akhir isian tersebut juga dirapikan (mis.
 * tautan " https://..." yang ditolak validasi saat kelompok disimpan).
 *
 * Nama kelompok tidak disentuh. Aman dijalankan berulang, mis. setelah impor
 * data lama berikutnya.
 *
 *     php artisan buruansae:bersihkan-kelompok --dry-run   # lihat dulu, tanpa mengubah
 *     php artisan buruansae:bersihkan-kelompok
 */
class CleanGroupPlaceholders extends Command
{
    protected $signature = 'buruansae:bersihkan-kelompok
        {--dry-run : Hanya tampilkan yang akan dibersihkan}
        {--force : Jalankan tanpa konfirmasi}';

    protected $description = 'Kosongkan isian "0" / "-" dari aplikasi lama di data kelompok';

    public const PLACEHOLDERS = ['0', '00', '-'];

    public const TEXT_COLUMNS = [
        'leader_name' => 'Nama ketua',
        'phone' => 'Nomor kontak',
        'land_status' => 'Status lahan',
        'description_url' => 'Tautan',
    ];

    public function handle(): int
    {
        $rows = [];
        foreach (self::TEXT_COLUMNS as $column => $label) {
            $rows[] = [$label, $this->placeholders($column)->count(), $this->untrimmed($column)->count()];
        }
        $rows[] = ['Luas lahan (0 m²)', $this->zeroArea()->count(), 0];

        $this->table(['Kolom', 'Dikosongkan', 'Spasi dirapikan'], $rows);
        $total = array_sum(array_map(fn ($row) => $row[1] + $row[2], $rows));
        $groups = $this->affectedGroups()->count();

        if ($total === 0) {
            $this->info('Tidak ada isian yang perlu dibersihkan.');

            return self::SUCCESS;
        }

        $this->line("{$groups} kelompok akan diperbarui.");

        if ($this->option('dry-run')) {
            $this->comment('Mode --dry-run: tidak ada data yang diubah.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Lanjutkan membersihkan?', true)) {
            return self::FAILURE;
        }

        DB::transaction(function () {
            foreach (array_keys(self::TEXT_COLUMNS) as $column) {
                $this->placeholders($column)->update([$column => null]);
                $this->untrimmed($column)->update([$column => DB::raw("TRIM({$column})")]);
            }
            $this->zeroArea()->update(['land_area_m2' => null]);
        });

        PublicDataCache::flush();
        $this->info("Selesai: {$groups} kelompok dibersihkan.");

        return self::SUCCESS;
    }

    /** updated_at sengaja tidak diubah: ini perbaikan data impor, bukan isian baru. */
    private function groups(): Builder
    {
        return DB::table('farmer_groups');
    }

    private function placeholders(string $column): Builder
    {
        return $this->groups()->whereIn(DB::raw("TRIM({$column})"), self::PLACEHOLDERS);
    }

    /**
     * Ada spasi di awal/akhir. Dibandingkan lewat LENGTH karena collation MySQL
     * menganggap 'abc ' = 'abc'.
     */
    private function untrimmed(string $column): Builder
    {
        return $this->groups()
            ->whereNotNull($column)
            ->whereNotIn(DB::raw("TRIM({$column})"), self::PLACEHOLDERS)
            ->whereRaw("LENGTH({$column}) <> LENGTH(TRIM({$column}))");
    }

    private function zeroArea(): Builder
    {
        return $this->groups()->where('land_area_m2', 0);
    }

    private function affectedGroups(): Builder
    {
        return $this->groups()->where(function (Builder $query) {
            foreach (array_keys(self::TEXT_COLUMNS) as $column) {
                $query->orWhereIn(DB::raw("TRIM({$column})"), self::PLACEHOLDERS)
                    ->orWhereRaw("LENGTH({$column}) <> LENGTH(TRIM({$column}))");
            }
            $query->orWhere('land_area_m2', 0);
        });
    }
}
