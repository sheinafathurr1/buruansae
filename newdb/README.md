# Database Buruan SAE untuk Laravel 13

Isi paket ini adalah struktur database hasil redesign (Fase 2), ditulis ulang mengikuti konvensi Laravel. Disertai model Eloquent, seeder, dan script untuk memindahkan data dari aplikasi CodeIgniter lama.

```
app/Enums/                  PlantingCategory, InputType (PHP enum + label Indonesia)
app/Models/                 12 model + User.php (versi bawaan + username, is_active)
database/migrations/        13 migration
database/seeders/           SectorSeeder, RecipientCategorySeeder, DatabaseSeeder
database/sql/import_data_lama.sql   pemindahan data dari database lama
```

## Konvensi Laravel yang dipakai

- **Nama tabel** jamak dan berbahasa Inggris (`farmer_groups`, `productions`). Dengan begitu model, relasi, dan `constrained()` bekerja tanpa konfigurasi tambahan. Kalau `Kelompok` dipakai sebagai nama model, Laravel akan mencari tabel `kelompoks`.
- **Primary key** `id` BIGINT UNSIGNED. **Foreign key** `{nama_tunggal}_id`, misalnya `farmer_group_id`.
- **Timestamps:** `created_at` dan `updated_at` di semua tabel, plus `deleted_at` (soft delete) di `farmer_groups`.
- **Kolom tanggal** memakai akhiran `_date`, sedangkan kolom waktu memakai `_at`.
- **Enum:** kolom enum di database dipadukan dengan PHP enum di model. Nilai di database memakai bahasa Inggris (`seed`), label tampilan memakai bahasa Indonesia (`->label()` menghasilkan "Benih").
- **Model** memakai gaya atribut Laravel 13 (`#[Fillable]`), sama seperti `User.php` bawaan skeleton.
- **CHECK constraint.** Laravel belum punya API untuk CHECK, jadi ditambahkan lewat `DB::statement`. Di SQLite (database bawaan untuk testing) CHECK dilewati; di MariaDB tetap berlaku.

## Nama lama → nama baru

| CodeIgniter | Laravel |
|---|---|
| `data_kecamatan` | `districts` |
| `data_kelurahan` | `villages` (+ `district_id`, dulu tidak ada relasinya) |
| `data_kelompok` | `farmer_groups` |
| `data_komoditi` | `commodities` + `sectors` |
| `data_sayur`, `data_buah`, `data_tanaman_obat`, `data_ikan`, `data_ternak`, `data_olahan_hasil`, `data_bibit`, `data_sampah` | `productions` |
| ±20 kolom `jumlah_*_stunting/mm/lansia/…` | `distributions` + `recipient_categories` |
| `jenis_pupuk`, `jumlah_pakan`, `waktu_pakan`, … | `production_inputs` |
| kolom khusus olahan / bibit | `processed_product_details` / `seedling_details` |
| `data_distribusi` | `monthly_distribution_recaps` |
| `users` + 9 tabel `auth_*` (Myth/Auth) | `users`, `sessions`, `password_reset_tokens` (bawaan Laravel) |

Beberapa kolom yang namanya berubah cukup jauh:

| Lama | Baru |
|---|---|
| `nama_ketua` | `leader_name` |
| `penyuluh` | `extension_officer` |
| `pendamping` | `facilitator` |
| `status_keaktifan` ('Aktif') | `is_active` (true / false / NULL = belum diketahui) |
| `tanggal_tanam`, `tanggal_produksi`, `tanggal_masuk` | `start_date` |
| `jumlah_tanam`, `jumlah_ikan`, `jumlah_ternak`, `jumlah_semai`, `jumlah_sampah` | `initial_quantity` |
| `jumlah_panen`, `jumlah_panen_kg` | `harvest_quantity` (satuannya dari `sectors.harvest_unit`) |
| `jumlah_panen_ekor` | `harvest_head_count` |
| `jumlah_kepala_keluarga_*` | `distributions.household_count` |
| `jumlah_orang_*` | `distributions.person_count` |

## Cara pasang

1. Buat project baru:
   ```bash
   composer create-project laravel/laravel buruansae
   ```
2. Salin folder `app/` dan `database/` dari paket ini ke dalam project. File `User.php` dan `DatabaseSeeder.php` memang sengaja menimpa versi bawaan.
3. Di `.env`, ganti `DB_CONNECTION` dari `sqlite` (bawaan Laravel 13) ke MariaDB:
   ```
   DB_CONNECTION=mariadb
   DB_DATABASE=buruansae
   DB_USERNAME=...
   DB_PASSWORD=...
   ```
4. Jalankan migration dan seeder:
   ```bash
   php artisan migrate --seed
   ```

## Memindahkan data lama

Pemindahan dikerjakan di komputer lokal (XAMPP/Laragon), lalu hasilnya diunggah ke hosting.

1. **Siapkan database lama.** Buat database `buruansae_lama` dan import dump produksi terbaru ke dalamnya. Jalankan `01_perbaikan_fase1.sql`, lalu bereskan temuan laporan `02_laporan_review.sql` bagian A (data uji) dan B (kelurahan tidak dikenal).
2. **Siapkan database Laravel.** Buat database `buruansae` di server MariaDB yang sama, lalu jalankan `php artisan migrate --seed`.
3. **Jalankan script impor** di database `buruansae`:
   ```bash
   mysql -u root buruansae < database/sql/import_data_lama.sql
   ```
4. **Cek hasil verifikasi** di akhir output:
   - Setiap tabel harus menunjukkan `baris_berbeda = 0`.
   - Query distribusi harus tidak mengembalikan baris sama sekali.
5. **Setel ulang password** (lihat bagian berikut).
6. **Unggah ke hosting:** export database `buruansae` lalu import ke database hosting.
7. **Pindahkan file gambar.** Kolom gambar dan foto hanya menyimpan nama file. Salin filenya dari folder upload aplikasi lama ke lokasi penyimpanan Laravel yang Anda pakai.

## Password lama tidak bisa dipakai

Myth/Auth menyimpan `bcrypt(base64(sha384(password)))`, sedangkan Laravel memakai `bcrypt(password)`. Hash lama tetap disalin, tapi login dengan password lama akan gagal.

Karena hanya ada 3 akun, cara paling sederhana adalah menyetel password baru lewat tinker:

```bash
php artisan tinker
>>> App\Models\User::where('username', 'namauser')->first()->update(['password' => 'PasswordBaruYangKuat']);
```

Tidak perlu memanggil `Hash::make` sendiri; cast `'password' => 'hashed'` di model `User` akan meng-hash otomatis.

## Yang perlu diputuskan saat membangun aplikasi

- **Login.** Starter kit resmi Laravel sudah membatasi percobaan login. Ini penting, karena data lama menunjukkan serangan brute-force pada November 2025. Login bawaan memakai email; kolom `username` sudah tersedia kalau ingin login dengan username.
- **Hak akses.** Tabel role belum disertakan. Kalau perlu admin/operator, pasang `spatie/laravel-permission`, yang membawa migration-nya sendiri.
- **Nama kategori penerima.** Nama KP, MM, dan MS di `RecipientCategorySeeder` masih berupa singkatan. Isi dengan kepanjangan resmi.
- **Aturan tanggal.** CHECK `harvest_date >= start_date` di migration `productions` masih dikomentari, karena data lama punya ±114 baris panen sebelum tanam. Aktifkan setelah data itu dibetulkan.
- **Data uji.** Beberapa baris di data lama tampaknya data uji, misalnya `data_sampah` id 1 yang semua nilainya 1. Hapus di database lama sebelum impor.

## Contoh pemakaian

```php
// Semua panen sayur 2025 beserta kelompok, kelurahan, dan distribusinya
$panen = Production::inSector('SAYUR')
    ->harvestedBetween('2025-01-01', '2025-12-31')
    ->with(['farmerGroup.village.district', 'commodity', 'distributions.recipientCategory'])
    ->get();

// Simpan panen + distribusinya sekaligus
DB::transaction(function () use ($group, $data) {
    $production = $group->productions()->create($data['production']);
    $production->distributions()->createMany($data['distributions']);
    // [['recipient_category_id' => 2, 'quantity' => 3, 'household_count' => 2, 'person_count' => 5], ...]
});
```

## Yang sudah diuji

Pengujian memakai kode Laravel 13.33 asli (`Migrator`, Schema Builder, Seeder, Eloquent) di MariaDB 10.11 dan SQLite. Hasilnya:

- **Migration:** 16 migration (3 bawaan + 13 dari paket ini) naik, turun semua (`reset`), lalu naik lagi tanpa error, baik di MariaDB maupun SQLite.
- **Seeder:** dijalankan dua kali tetap menghasilkan 8 sektor dan 10 kategori, tidak dobel.
- **Impor:**
  - 6.730 baris produksi cocok kolom per kolom dengan data lama.
  - Semua nilai distribusi cocok per kategori.
  - Jumlah kecamatan, kelurahan, kelompok, rekap, dan user sama dengan data lama.
  - Verifikasi ini sudah dibuktikan bisa menangkap kesalahan: 4 perubahan yang sengaja dibuat semuanya terdeteksi.
- **Model:**
  - Relasi berantai dan `hasManyThrough` jalan, begitu juga cast enum dan tanggal.
  - Id baru melanjutkan setelah id lama.
  - Kelompok yang punya produksi ditolak dihapus permanen, sedangkan soft delete berhasil dan produksinya tetap utuh.
  - Menghapus produksi ikut menghapus distribusinya, dan angka negatif ditolak oleh CHECK.

Uji dilakukan setelah mensimulasikan keputusan laporan 02 bagian A dan B, yaitu menghapus kelompok uji 566/610 dan menganggap "Cikadung" sebagai CIPADUNG. Keputusan sebenarnya tetap ada di tangan Anda.
