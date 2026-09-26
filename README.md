# Buruan SAE — Portal Publik & Dashboard Pengelola

Aplikasi program **Buruan SAE**, urban farming terintegrasi Dinas Ketahanan Pangan dan Pertanian (DKPP) Kota Bandung.
Satu aplikasi **Laravel 12** (PHP ≥ 8.3) berisi:

- **Portal publik** — pengganti aplikasi Laravel 8 lama di repo ini.
- **Dashboard pengelola** (`/admin`) — pengganti aplikasi CodeIgniter 4 `caatis-coe/buruansae-dashboard`.

Keduanya memakai struktur database baru (dulu di folder `newdb/`, lihat [`docs/DATABASE.md`](docs/DATABASE.md)),
sehingga data yang diinput di dashboard langsung tampil di portal publik.

## Portal publik

| Halaman | URL | Isi |
|---|---|---|
| Beranda | `/` | Profil program, angka ringkas (kelompok, kelurahan, hasil panen & penerima manfaat tahun berjalan), 8 sektor, berita terbaru |
| Dashboard sektor | `/vegetable`, `/medicalplant`, `/fruit`, `/livestock`, `/fish`, `/processed-products`, `/waste-processing`, `/nursery` | Filter komoditas, kecamatan, dan rentang tanggal; total panen; belum panen; terlambat panen; akan panen 7 hari ke depan; grafik & tabel per kecamatan → per kelurahan; rincian per kelompok (modal) termasuk penyaluran hasil |
| Peta sebaran | `/map` | Peta Leaflet jumlah kelompok per kelurahan, filter kecamatan & pencarian. **Gunakan lokasi saya**: kelurahan terdekat yang punya kelompok + jarak, dihitung di browser (lokasi tidak dikirim ke server; perlu HTTPS). `/map?lokasi=saya` langsung mencari |
| Berita | `/news`, `/news/{slug}` | Artikel kegiatan kelompok |
| API peta | `/api/locations` | JSON kelurahan + jumlah kelompok (kunci lama `total_kelompok` tetap ada) |

URL lama dipertahankan, jadi tautan yang sudah beredar tetap berfungsi. Yang berubah/bertambah:

- Semua angka dihitung langsung di database (SUM/GROUP BY), bukan memuat seluruh baris ke memori.
- Tanpa filter kecamatan, rincian ditampilkan **per kecamatan**; klik kecamatan untuk turun ke **per kelurahan**, lalu klik kelurahan untuk rincian kelompok.
- Kartu **Penyaluran hasil** (konsumsi pribadi / dibagikan / dijual, termasuk jumlah KK & orang penerima) dari tabel `distributions`.
- Sektor **Olahan Hasil** kini aktif (dulu "Coming soon"), lengkap dengan merek, bahan dasar, izin PIRT, sertifikat halal, dan uji lab.
- Filter yang tidak valid diabaikan dan pesannya ditampilkan (tidak ada halaman error).

## Dashboard pengelola (`/admin`)

Fitur sama dengan aplikasi CodeIgniter `buruansae-dashboard`, disesuaikan dengan struktur database baru:

| Menu | URL | Isi |
|---|---|---|
| Masuk / keluar | `/admin/masuk` | Login dengan username **atau** email |
| Ringkasan | `/admin` | Angka ringkas, kartu input per sektor (berjalan / terlambat / panen bulan ini), daftar siklus yang perlu dicatat panennya |
| Data produksi | `/admin/produksi/{sektor}` | Per sektor (8 sektor, termasuk Pembibitan): daftar dengan tab Semua / Belum panen / Terlambat / Sudah panen, pencarian kelompok, filter komoditas & kecamatan |
| Tambah / ubah data tanam | `…/tambah`, `…/{id}/ubah` | Kelompok (dengan info penyuluh, pendamping, kelurahan), komoditas, kategori tanam, jumlah, perkiraan panen. **Perkiraan tanggal panen dihitung otomatis** dari tanggal tanam + durasi tanam komoditas. Ikan/ternak: pakan. Olahan hasil: bahan dasar, merek, resep, PIRT, halal, uji lab. Pembibitan: asal bibit |
| Data panen / produksi | `…/{id}/panen` | Tanggal panen, foto hasil, penyaluran per kategori (jumlah, KK, orang), harga jual. **Jumlah panen = total konsumsi pribadi + dibagikan + dijual** (sama dengan perubahan terakhir di aplikasi lama). Ikan/ternak: jumlah ekor. Buah: pemupukan |
| Kelompok | `/admin/kelompok` | Tambah/ubah/hapus kelompok: kelurahan, RW, ketua, kontak, penyuluh, pendamping, lahan, status keaktifan, foto lahan & ketua |
| Komoditas | `/admin/komoditas` | Tambah/ubah/hapus komoditas per sektor dengan durasi tanam dan gambar (gambar tampil di portal publik) |
| Profil | `/admin/profil` | Ubah nama, email, dan kata sandi sendiri |

Perbedaan dari aplikasi CodeIgniter:

- **Keamanan login**: dibatasi 5 percobaan per menit per akun (+20 per menit per IP), akun bisa dinonaktifkan, tidak ada
  pendaftaran akun publik. Akun dibuat lewat perintah `buruansae:user` (lihat bawah).
- **Hapus kelompok** kini *soft delete*: data produksinya tetap tersimpan tetapi tidak lagi dihitung di portal
  (dulu seluruh data produksi kelompok ikut terhapus permanen).
- **Komoditas yang sudah dipakai** tidak bisa dihapus atau dipindah sektor.
- **Foto** boleh sampai 8 MB; foto dari ponsel otomatis diperkecil ke lebar 1.600 px.
- Olahan Hasil & Pengolahan Sampah memakai form penyaluran yang sama dengan sektor lain (sesuai data produksi di
  database), bukan kolom lama seperti "lokasi pembeli".
- Tampilan responsif (daftar berubah menjadi kartu di ponsel) dan lolos audit aksesibilitas axe-core.

### Membuat akun pengelola

```bash
php artisan buruansae:user admin --email=admin@bandung.go.id --name="Admin DKPP"   # akun baru (kata sandi ditanyakan)
php artisan buruansae:user admin                                                   # setel ulang kata sandi
php artisan buruansae:user penyuluh1 --deactivate                                  # nonaktifkan (--activate untuk mengaktifkan)
```

Kata sandi minimal 8 karakter berisi huruf dan angka. Akun hasil impor data lama wajib disetel ulang kata sandinya
karena format hash Myth/Auth berbeda (lihat `docs/DATABASE.md`).

## Teknologi

- Laravel 12, PHP 8.3+ (lockfile dikunci ke platform PHP 8.3)
- MariaDB 10.6+ / MySQL 8 (produksi), SQLite (pengujian)
- Blade + Tailwind CSS 4 + Alpine.js, dibundel dengan Vite
- Chart.js (grafik), Leaflet + OpenStreetMap (peta), Tom Select (pilihan dengan pencarian)
- Ikon: Blade Heroicons · Huruf: Plus Jakarta Sans (di-hosting sendiri)

## Menjalankan di komputer lokal

Prasyarat: PHP 8.3+ (ekstensi `pdo_mysql`, `mbstring`; `gd` untuk memperkecil foto), Composer 2, MariaDB/MySQL.
Node.js 20+ hanya perlu bila mengubah tampilan.

```bash
composer install
cp .env.example .env
php artisan key:generate

# Buat database kosong "buruansae", isi DB_* di .env, lalu:
php artisan migrate --seed               # tabel + data acuan (sektor & kategori penerima)
php artisan db:seed --class=DemoSeeder   # opsional: data contoh untuk mencoba
php artisan storage:link                 # agar foto unggahan bisa ditampilkan
php artisan buruansae:user admin --email=admin@example.com

php artisan serve
```

Buka `http://localhost:8000` (portal) dan `http://localhost:8000/admin` (dashboard).

Hasil build tampilan (`public/build`) sudah ada di repo, jadi `npm` tidak wajib. Bila mengubah Blade/CSS/JS,
jalankan `npm install` lalu `npm run dev` (selama mengembangkan) atau `npm run build` (sebelum commit).

> `DemoSeeder` berisi data **buatan** (nama wilayah asli, angka fiktif). Seeder ini menolak berjalan di
> `APP_ENV=production` atau bila tabel kelompok sudah berisi data.

## Pengujian

```bash
php artisan test
```

63 pengujian (unit + fitur) memakai SQLite in-memory, antara lain:

- **Portal**: perhitungan dashboard terhadap data yang totalnya dihitung manual (termasuk kelompok yang sudah dihapus
  & sektor lain yang tidak boleh ikut terhitung), validasi filter, semua halaman, API peta, header keamanan.
- **Dashboard**: login (username/email, akun nonaktif, pembatasan percobaan), CRUD kelompok & komoditas (termasuk foto),
  input tanam per sektor, input panen (total = jumlah penyaluran, validasi tanggal/foto/harga), hapus data, dan
  pengosongan cache portal setelah data berubah.

Pengujian yang sama juga lolos di MariaDB 10.11.

## Deploy ke hosting

Folder `public/build` (hasil `npm run build`) **ikut di-commit**, jadi server tidak memerlukan Node.js.
Jalankan ulang `npm run build` dan commit hasilnya setiap kali mengubah tampilan (Blade/CSS/JS).

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env     # lalu isi: APP_ENV=production, APP_DEBUG=false, APP_URL, DB_*
php artisan key:generate
php artisan migrate --force --seed
php artisan storage:link
php artisan buruansae:user admin --email=...   # akun pengelola pertama
php artisan optimize        # cache config, route, view
php artisan icons:cache
```

- Idealnya document root domain diarahkan ke folder `public/`. Bila hosting memaksa document root di folder proyek,
  file `.htaccess` di root meneruskan semua permintaan ke `public/` dan menolak akses ke file sensitif.
- File unggahan disimpan di `storage/app/public/images/`: `panen/` (foto hasil panen), `kelompok/` (foto lahan & ketua),
  dan langsung di `images/` (gambar komoditas, sama seperti aplikasi lama `storage/images`). Foto dari aplikasi lama
  ikut di repo; unggahan baru tidak. `public/storage` sebaiknya symlink dari `php artisan storage:link` (bukan folder
  biasa); bila hosting tidak mengizinkan symlink, gambar disajikan lewat route cadangan. Rincian di
  [`docs/DATABASE.md`](docs/DATABASE.md#gambar-dari-aplikasi-lama).
- Setelah mengubah `.env` di server, jalankan `php artisan optimize` lagi.

## Memindahkan data dari database lama

Struktur tabel, pemetaan nama kolom lama → baru, dan langkah impor lengkap ada di [`docs/DATABASE.md`](docs/DATABASE.md).
Ringkasnya:

```bash
mysql -u root buruansae_lama < database/sql/persiapan_data_lama.sql   # di salinan database lama
php artisan migrate:fresh --seed
mysql -u root buruansae < database/sql/import_data_lama.sql
php artisan cache:clear
```

## Struktur kode

```
app/Enums/SectorType.php            8 sektor: slug URL ↔ kode sektor di DB, label, satuan, isian form per sektor
app/Services/SectorDashboard.php    angka dashboard sektor publik (query agregat)
app/Services/HomeStatistics.php     angka ringkas beranda (di-cache, dikosongkan otomatis saat data berubah)
app/Services/ProductionRecorder.php simpan data tanam, panen & penyaluran, hapus (dashboard pengelola)
app/Http/Controllers/…              portal publik (Home, Sector, SectorVillage, News, Map, Api/Location)
app/Http/Controllers/Admin/…        dashboard pengelola (Auth, Dashboard, FarmerGroup, Commodity, Production, Harvest, Profile)
app/Http/Requests/…                 validasi form (filter publik, login, form dashboard)
app/Console/Commands/ManageUser.php perintah buruansae:user
app/Support/…                       format angka/tanggal, penyimpanan foto, cache portal
routes/web.php, admin.php, api.php  rute portal, dashboard (/admin), dan API
config/buruansae.php                kontak DKPP & pengaturan peta
resources/content/news.php          isi berita
resources/views/…                   Blade: portal publik, admin/, components/ (layout, form, dll.)
resources/js/…                      app.js (Alpine), dashboard.js (Chart.js), map.js (Leaflet), admin.js
database/migrations, seeders, sql   struktur DB baru, data acuan, data contoh, skrip impor data lama
tests/                              pengujian unit & fitur (portal dan dashboard)
```

## Catatan keamanan

- Repository versi lama menyimpan file `.env` beserta kredensial database. File itu kini tidak lagi dilacak Git
  (ada di `.gitignore`), tetapi masih tersimpan di riwayat commit lama — **segera ganti password database** yang pernah
  tercantum di sana.
- Repository `buruansae-dashboard` lama menyimpan dump database (`db_buruansae.sql`) dan berkas `pwlogin.txt`.
  Setelah dashboard ini dipakai, sebaiknya repository itu dijadikan privat/diarsipkan dan kata sandi yang pernah
  tersimpan di sana diganti.
