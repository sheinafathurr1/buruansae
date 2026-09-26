# Struktur database Buruan SAE

Struktur hasil redesign (fase 2) yang dulu dikirim di folder `newdb/`, kini sudah menjadi bagian aplikasi:

```
app/Enums/PlantingCategory.php, InputType.php   PHP enum + label Indonesia
app/Models/                                     12 model + User (username, is_active)
database/migrations/2026_09_26_*                13 migration struktur baru
database/seeders/SectorSeeder.php               8 sektor (data acuan)
database/seeders/RecipientCategorySeeder.php    10 kategori penerima (data acuan)
database/seeders/DemoSeeder.php                 data contoh untuk lokal/staging
database/sql/import_data_lama.sql               pemindahan data dari database lama
```

Perbedaan dari paket `newdb/` asli:

- Paket asli ditulis untuk Laravel 13 (atribut `#[Fillable]` / `#[Hidden]`). Laravel 12 belum mengenal atribut
  itu, jadi model memakai properti `$fillable` / `$hidden`. Isi kolomnya sama persis.
- Model yang punya factory memakai trait `HasFactory` (untuk pengujian & data contoh).
- `RecipientCategorySeeder` sudah memakai kepanjangan resmi yang dipakai tampilan aplikasi lama:
  KP = **Konsumsi Pribadi**, MM = **Masyarakat Miskin**, MS = **Masyarakat Sekitar**. Nama ikut diperbarui bila
  seeder dijalankan ulang.
- Migration, enum, dan skrip impor tidak diubah.

## Konvensi

- **Nama tabel** jamak berbahasa Inggris (`farmer_groups`, `productions`), primary key `id` BIGINT UNSIGNED,
  foreign key `{nama_tunggal}_id`.
- **Timestamps** di semua tabel, plus `deleted_at` (soft delete) di `farmer_groups`. Kelompok yang di-soft delete
  tidak ikut dihitung di dashboard maupun peta.
- **Kolom tanggal** berakhiran `_date`, kolom waktu berakhiran `_at`.
- **Enum**: nilai di database berbahasa Inggris (`seed`), label tampilan berbahasa Indonesia (`->label()` → "Benih").
- **CHECK constraint** ditambahkan lewat `DB::statement` (Laravel belum punya API-nya). Di SQLite (pengujian)
  dilewati; di MariaDB/MySQL berlaku — sudah diverifikasi di MariaDB 10.11.

## Tabel

| Tabel | Isi |
|---|---|
| `districts` | Kecamatan |
| `villages` | Kelurahan (+ `district_id`, `latitude`, `longitude`) |
| `farmer_groups` | Kelompok Buruan SAE (RW, ketua, penyuluh, pendamping, luas & status lahan, `is_active`) |
| `sectors` | 8 sektor + `harvest_unit` (satuan hasil: `kg`, atau `pohon` untuk bibit) |
| `commodities` | Komoditas per sektor (+ `growing_days`, `image`) |
| `productions` | Satu siklus tanam → panen untuk **semua** sektor |
| `recipient_categories` | Kategori penerima hasil (KP, Stunting, MM, Lansia, Posyandu, ..., Dijual) |
| `distributions` | Hasil yang disalurkan per (produksi, kategori): jumlah, KK, orang |
| `production_inputs` | Pemupukan (buah) dan pemberian pakan (ikan, ternak) |
| `processed_product_details` | Detail produk olahan: bahan dasar, merek, resep, uji lab, halal, PIRT |
| `seedling_details` | Detail bibit: asal bibit |
| `monthly_distribution_recaps` | Rekap distribusi bulanan per kelurahan |

Relasi utama: `districts` → `villages` → `farmer_groups` → `productions` ← `commodities` ← `sectors`;
`productions` → `distributions` ← `recipient_categories`.

## Nama lama → nama baru

| CodeIgniter / Laravel 8 lama | Struktur baru |
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

| Kolom lama | Kolom baru |
|---|---|
| `nama_ketua` | `leader_name` |
| `penyuluh` | `extension_officer` |
| `pendamping` | `facilitator` |
| `status_keaktifan` ('Aktif') | `is_active` (true / false / NULL = belum diketahui) |
| `tanggal_tanam`, `tanggal_produksi`, `tanggal_masuk` | `start_date` |
| `jumlah_tanam`, `jumlah_ikan`, `jumlah_ternak`, `jumlah_semai`, `jumlah_sampah` | `initial_quantity` |
| `waktu_prakiraan_panen`, `prakiraan_jumlah_panen` | `estimated_harvest_date`, `estimated_harvest_quantity` |
| `waktu_panen`, `jumlah_panen`, `jumlah_panen_kg` | `harvest_date`, `harvest_quantity` (satuan dari `sectors.harvest_unit`) |
| `jumlah_panen_ekor` | `harvest_head_count` |
| `jumlah_kepala_keluarga_*` | `distributions.household_count` |
| `jumlah_orang_*` | `distributions.person_count` |

## Bagaimana dashboard membaca data

Didefinisikan di `app/Services/SectorDashboard.php` (sama dengan logika aplikasi lama):

| Angka | Kondisi | Filter tanggal memakai |
|---|---|---|
| Sudah panen | `harvest_quantity` terisi | `harvest_date` |
| Belum panen | `harvest_date` kosong | `estimated_harvest_date` |
| Terlambat panen | belum panen & `estimated_harvest_date` < hari ini | – |
| Akan panen 7 hari ke depan | belum panen & `estimated_harvest_date` hari ini s.d. +7 hari | – |

Sektor ditentukan dari `productions.commodity_id → commodities.sector_id`.

## Memindahkan data lama

Dikerjakan di komputer lokal (XAMPP/Laragon), lalu hasilnya diunggah ke hosting.

1. **Siapkan database lama.** Buat database `buruansae_lama` dan import dump produksi terbaru ke dalamnya.
   Paket `newdb/` asli merujuk dua file persiapan, `01_perbaikan_fase1.sql` dan `02_laporan_review.sql`
   (bereskan temuan bagian A — data uji — dan B — kelurahan tidak dikenal). **Kedua file itu tidak ikut di folder
   `newdb/`**, jadi minta dari penyusun paket database. Tanpa perbaikan itu, skrip impor sengaja berhenti dengan
   error `Column 'village_id' cannot be null`.
2. **Siapkan database Laravel** `buruansae` di server MariaDB yang sama:
   ```bash
   php artisan migrate:fresh --seed
   ```
3. **Jalankan skrip impor** (hanya membaca database lama):
   ```bash
   mysql -u root buruansae < database/sql/import_data_lama.sql
   ```
4. **Cek hasil verifikasi** di akhir output: setiap tabel harus `baris_berbeda = 0`, dan query distribusi tidak
   mengembalikan baris sama sekali.
5. **Setel ulang password** (lihat di bawah), lalu `php artisan cache:clear` supaya angka beranda & peta diperbarui.
6. **Unggah ke hosting:** export database `buruansae`, import ke database hosting.
7. **Pindahkan file gambar** (kolom database hanya menyimpan nama file), lalu jalankan `php artisan storage:link`:
   - gambar komoditas → `storage/app/public/images/`
   - foto hasil panen (aplikasi CodeIgniter menyimpannya di `public/asset/`) → `storage/app/public/images/panen/`
   - foto lahan & ketua kelompok → `storage/app/public/images/kelompok/`

Id kecamatan, kelurahan, kelompok, komoditas, rekap, dan user dipertahankan; id produksi dibuat baru. Menjalankan
ulang skrip akan gagal dengan "Duplicate entry" (bukan menggandakan data). Untuk mengulang: `migrate:fresh --seed`
lalu impor lagi.

### Password lama tidak bisa dipakai

Myth/Auth menyimpan `bcrypt(base64(sha384(password)))`, sedangkan Laravel memakai `bcrypt(password)`. Hash lama tetap
disalin, tetapi login ke dashboard dengan password lama akan gagal. Setel ulang tiap akun:

```bash
php artisan buruansae:user namauser        # kata sandi baru ditanyakan
```

## Hal yang masih perlu diputuskan

- **Aturan tanggal.** CHECK `harvest_date >= start_date` di migration `productions` masih dikomentari karena data lama
  punya ±114 baris panen sebelum tanam. Aktifkan setelah data itu dibetulkan.
- **Data uji** di database lama (mis. `data_sampah` id 1 yang semua nilainya 1) sebaiknya dihapus sebelum impor.
- **Hak akses.** Semua akun aktif di dashboard `/admin` punya akses yang sama (seperti aplikasi CodeIgniter lama).
  Bila perlu membedakan admin dan penyuluh, pasang `spatie/laravel-permission`.
- **Masa transisi.** Selama aplikasi CodeIgniter lama masih dipakai, data yang diinput di sana masuk ke tabel lama.
  Hentikan input di aplikasi lama sebelum impor terakhir, lalu gunakan dashboard `/admin` ini.
- **Wilayah.** Kecamatan & kelurahan (termasuk koordinat peta) berasal dari impor data lama; dashboard belum punya
  menu untuk mengubahnya.
