# Buruan SAE — Portal Publik

Portal data publik program **Buruan SAE**, urban farming terintegrasi Dinas Ketahanan Pangan dan Pertanian (DKPP) Kota Bandung.
Versi ini dibangun ulang dari nol dengan **Laravel 12** dan **PHP ≥ 8.3**, memakai struktur database baru (dulu di folder `newdb/`).

## Fitur

Fitur sama dengan aplikasi lama, dengan tampilan baru yang responsif dan aksesibel:

| Halaman | URL | Isi |
|---|---|---|
| Beranda | `/` | Profil program, angka ringkas (kelompok, kelurahan, hasil panen & penerima manfaat tahun berjalan), 8 sektor, berita terbaru |
| Dashboard sektor | `/vegetable`, `/medicalplant`, `/fruit`, `/livestock`, `/fish`, `/processed-products`, `/waste-processing`, `/nursery` | Filter komoditas, kecamatan, dan rentang tanggal; total panen; belum panen; terlambat panen; akan panen 7 hari ke depan; grafik & tabel per kecamatan → per kelurahan; rincian per kelompok (modal) termasuk penyaluran hasil |
| Peta sebaran | `/map` | Peta Leaflet jumlah kelompok per kelurahan, filter kecamatan & pencarian |
| Berita | `/news`, `/news/{slug}` | Artikel kegiatan kelompok |
| API peta | `/api/locations` | JSON kelurahan + jumlah kelompok (kunci lama `total_kelompok` tetap ada) |

URL lama dipertahankan, jadi tautan yang sudah beredar tetap berfungsi. Yang berubah/bertambah:

- Semua angka dihitung langsung di database (SUM/GROUP BY), bukan memuat seluruh baris ke memori.
- Tanpa filter kecamatan, rincian ditampilkan **per kecamatan**; klik kecamatan untuk turun ke **per kelurahan**, lalu klik kelurahan untuk rincian kelompok.
- Kartu **Penyaluran hasil** (konsumsi pribadi / dibagikan / dijual, termasuk jumlah KK & orang penerima) dari tabel `distributions`.
- Sektor **Olahan Hasil** kini aktif (dulu "Coming soon"), lengkap dengan merek, bahan dasar, izin PIRT, sertifikat halal, dan uji lab.
- Filter yang tidak valid diabaikan dan pesannya ditampilkan (tidak ada halaman error).
- Aksesibilitas: lolos audit axe-core (WCAG 2 A/AA) di desktop dan ponsel; grafik punya tampilan tabel; navigasi keyboard & pembaca layar.

## Teknologi

- Laravel 12, PHP 8.3+ (lockfile dikunci ke platform PHP 8.3)
- MariaDB 10.6+ / MySQL 8 (produksi), SQLite (pengujian)
- Blade + Tailwind CSS 4 + Alpine.js, dibundel dengan Vite
- Chart.js (grafik), Leaflet + OpenStreetMap (peta), Tom Select (pilihan dengan pencarian)
- Ikon: Blade Heroicons · Huruf: Plus Jakarta Sans (di-hosting sendiri)

## Menjalankan di komputer lokal

Prasyarat: PHP 8.3+ (ekstensi `pdo_mysql`, `mbstring`, `gd` tidak wajib), Composer 2, Node.js 20+, MariaDB/MySQL.

```bash
composer install
cp .env.example .env
php artisan key:generate

# Buat database kosong "buruansae", isi DB_* di .env, lalu:
php artisan migrate --seed          # tabel + data acuan (sektor & kategori penerima)
php artisan db:seed --class=DemoSeeder   # opsional: data contoh untuk mencoba

npm install
npm run dev        # atau: npm run build
php artisan serve
```

> `DemoSeeder` berisi data **buatan** (nama wilayah asli, angka fiktif). Seeder ini menolak berjalan di
> `APP_ENV=production` atau bila tabel kelompok sudah berisi data.

## Pengujian

```bash
php artisan test
```

38 pengujian (unit + fitur) memakai SQLite in-memory: perhitungan dashboard terhadap data yang totalnya dihitung manual
(termasuk kelompok yang sudah dihapus & sektor lain yang tidak boleh ikut terhitung), validasi filter, semua halaman,
API peta, dan header keamanan. Pengujian yang sama juga lolos di MariaDB 10.11.

## Deploy ke hosting

Folder `public/build` (hasil `npm run build`) **ikut di-commit**, jadi server tidak memerlukan Node.js.
Jalankan ulang `npm run build` dan commit hasilnya setiap kali mengubah tampilan (Blade/CSS/JS).

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env     # lalu isi: APP_ENV=production, APP_DEBUG=false, APP_URL, DB_*
php artisan key:generate
php artisan migrate --force --seed
php artisan storage:link
php artisan optimize        # cache config, route, view
php artisan icons:cache
```

- Idealnya document root domain diarahkan ke folder `public/`. Bila hosting memaksa document root di folder proyek,
  file `.htaccess` di root meneruskan semua permintaan ke `public/` dan menolak akses ke file sensitif.
- Gambar komoditas dibaca dari `storage/app/public/images/{nama_file}` (sama seperti aplikasi lama `storage/images`).
  Salin file gambar lama ke folder tersebut.
- Setelah mengubah `.env` di server, jalankan `php artisan optimize` lagi.

## Memindahkan data dari database lama

Struktur tabel, pemetaan nama kolom lama → baru, dan langkah impor lengkap ada di [`docs/DATABASE.md`](docs/DATABASE.md).
Ringkasnya:

```bash
php artisan migrate:fresh --seed
mysql -u root buruansae < database/sql/import_data_lama.sql
php artisan cache:clear
```

## Struktur kode

```
app/Enums/SectorType.php            8 sektor: slug URL ↔ kode sektor di DB, label, satuan, istilah
app/Services/SectorDashboard.php    semua angka dashboard sektor (query agregat)
app/Services/HomeStatistics.php     angka ringkas beranda (di-cache 10 menit)
app/Http/Requests/SectorDashboardRequest.php  validasi filter (GET, tanpa redirect)
app/Http/Controllers/…              Home, Sector, SectorVillage (modal), News, Map, Api/Location
app/Models/…                        12 model struktur database baru
config/buruansae.php                kontak DKPP & pengaturan peta
resources/content/news.php          isi berita
resources/views/…                   Blade (layout, beranda, dashboard sektor, peta, berita, halaman error)
resources/js/dashboard.js, map.js   grafik Chart.js & peta Leaflet
database/migrations, seeders, sql   struktur DB baru, data acuan, data contoh, skrip impor data lama
tests/                              pengujian unit & fitur
```

## Catatan keamanan

Repository versi lama menyimpan file `.env` beserta kredensial database. File itu kini tidak lagi dilacak Git
(ada di `.gitignore`), tetapi masih tersimpan di riwayat commit lama — **segera ganti password database** yang pernah
tercantum di sana.
