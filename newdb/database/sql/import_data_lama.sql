-- =============================================================================
--  IMPOR DATA LAMA (CodeIgniter) → DATABASE LARAVEL
--
--  Dijalankan SEKALI, di database Laravel yang baru selesai
--      php artisan migrate --seed
--  (tabel masih kosong kecuali sectors & recipient_categories).
--
--  Database lama harus ada di SERVER YANG SAMA dengan nama `buruansae_lama`
--  (kalau namanya lain, cari-ganti `buruansae_lama` di file ini), dan sudah:
--    1. dijalankan file 01_perbaikan_fase1.sql, dan
--    2. dibereskan temuan laporan 02 bagian A (data uji) & B (kelurahan tidak
--       dikenal). Kalau belum, script berhenti dengan error
--       "Column 'village_id' cannot be null" — disengaja.
--
--  Script ini hanya MEMBACA database lama. Id kecamatan, kelurahan, kelompok,
--  komoditas, rekap, dan user dipertahankan; id produksi dibuat baru.
--  Menjalankan ulang akan gagal dengan "Duplicate entry" (bukan menggandakan
--  data). Untuk mengulang: php artisan migrate:fresh --seed, lalu impor lagi.
-- =============================================================================

SET NAMES utf8mb4;
SET @OLD_SQL_MODE := @@SESSION.sql_mode;
SET SESSION sql_mode = 'STRICT_ALL_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';


-- -----------------------------------------------------------------------------
-- 1. WILAYAH & KELOMPOK
-- -----------------------------------------------------------------------------
INSERT INTO `districts` (id, name)
SELECT id, name FROM `buruansae_lama`.`data_kecamatan` ORDER BY id;

INSERT INTO `villages` (id, district_id, name, latitude, longitude)
SELECT id, id_kecamatan, name, latitude, longitude FROM `buruansae_lama`.`data_kelurahan` ORDER BY id;

INSERT INTO `farmer_groups`
  (id, village_id, rw, name, leader_name, phone, extension_officer, facilitator,
   land_area_m2, land_status, is_active, status_note, land_photo, leader_photo,
   description_url, created_at, updated_at)
SELECT g.id_kelompok, v.id, CAST(g.rw AS UNSIGNED), g.nama_kelompok, g.nama_ketua, g.nomor_kontak,
       g.penyuluh, g.pendamping, g.luas_lahan, g.status_lahan,
       CASE UPPER(g.status_keaktifan) WHEN 'AKTIF' THEN 1 WHEN 'TIDAK AKTIF' THEN 0 END,
       g.keterangan_status, g.foto_lahan, g.foto_ketua, g.link_deskripsi, g.created_at, g.updated_at
FROM `buruansae_lama`.`data_kelompok` g
LEFT JOIN `districts` d ON d.name = g.kecamatan
LEFT JOIN `villages` v ON v.name = g.kelurahan AND v.district_id = d.id
ORDER BY g.id_kelompok;


-- -----------------------------------------------------------------------------
-- 2. KOMODITAS
--    Master lama dipindah apa adanya; nama yang dipakai di data tapi belum ada
--    di master ditambahkan (huruf besar) — cek ejaannya setelah impor.
-- -----------------------------------------------------------------------------
INSERT INTO `commodities` (id, sector_id, name, growing_days, image)
SELECT k.id, s.id, TRIM(k.nama_komoditi), k.durasi_tanam, k.gambar
FROM `buruansae_lama`.`data_komoditi` k
JOIN `sectors` s ON s.code = REPLACE(k.sektor, ' ', '_')
ORDER BY k.id;

INSERT INTO `commodities` (sector_id, name)
SELECT s.id, x.name
FROM (
        SELECT 'SAYUR' AS code, UPPER(TRIM(nama_sayur)) AS name FROM `buruansae_lama`.`data_sayur`
  UNION SELECT 'BUAH',          UPPER(TRIM(nama_buah))         FROM `buruansae_lama`.`data_buah`
  UNION SELECT 'TANAMAN_OBAT',  UPPER(TRIM(nama_tanaman_obat)) FROM `buruansae_lama`.`data_tanaman_obat`
  UNION SELECT 'IKAN',          UPPER(TRIM(jenis_ikan))        FROM `buruansae_lama`.`data_ikan`
  UNION SELECT 'TERNAK',        UPPER(TRIM(jenis_ternak))      FROM `buruansae_lama`.`data_ternak`
  UNION SELECT 'OLAHAN_HASIL',  UPPER(TRIM(jenis_olahan))      FROM `buruansae_lama`.`data_olahan_hasil`
  UNION SELECT 'OLAHAN_SAMPAH', UPPER(TRIM(jenis_pengolahan))  FROM `buruansae_lama`.`data_sampah`
  UNION SELECT 'BIBIT',         UPPER(TRIM(nama_sayur))        FROM `buruansae_lama`.`data_bibit`
) x
JOIN `sectors` s ON s.code = x.code
WHERE x.name IS NOT NULL AND x.name <> ''
  AND NOT EXISTS (SELECT 1 FROM `commodities` c WHERE c.sector_id = s.id AND c.name = x.name);

SELECT COUNT(*) AS komoditas_ditambahkan_dari_data FROM `commodities` WHERE created_at IS NULL
  AND id > (SELECT IFNULL(MAX(id), 0) FROM `buruansae_lama`.`data_komoditi`);


-- -----------------------------------------------------------------------------
-- 3. PRODUKSI (8 tabel lama → productions)
--    Lewat tabel bantu _import_productions supaya tiap baris lama tahu id
--    barunya (dipakai untuk distributions dan detail). Dihapus di akhir.
-- -----------------------------------------------------------------------------
CREATE TABLE `_import_productions` (
  `new_id`                     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `legacy_table`               VARCHAR(30) NOT NULL,
  `legacy_id`                  INT UNSIGNED NOT NULL,
  `farmer_group_id`            BIGINT UNSIGNED NOT NULL,
  `commodity_id`               BIGINT UNSIGNED NOT NULL,
  `planting_category`          VARCHAR(10) NULL,
  `start_date`                 DATE NULL,
  `initial_quantity`           DECIMAL(12,2) NULL,
  `estimated_harvest_date`     DATE NULL,
  `estimated_harvest_quantity` DECIMAL(12,3) NULL,
  `harvest_date`               DATE NULL,
  `harvest_quantity`           DECIMAL(12,3) NULL,
  `harvest_head_count`         DECIMAL(12,2) NULL,
  `selling_price`              BIGINT UNSIGNED NULL,
  `notes`                      VARCHAR(255) NULL,
  `image`                      VARCHAR(255) NULL,
  `created_at`                 DATETIME NULL,
  `updated_at`                 DATETIME NULL,
  PRIMARY KEY (`new_id`),
  UNIQUE KEY `uq_legacy` (`legacy_table`, `legacy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `_import_productions`
  (legacy_table, legacy_id, farmer_group_id, commodity_id, planting_category, start_date, initial_quantity,
   estimated_harvest_date, estimated_harvest_quantity, harvest_date, harvest_quantity, harvest_head_count,
   selling_price, notes, image, created_at, updated_at)
SELECT 'data_sayur', s.`id_sayur`, s.id_kelompok, c.id, CASE UPPER(s.`kategori_tumbuhan`) WHEN 'BENIH' THEN 'seed' WHEN 'BIBIT' THEN 'seedling' WHEN 'POHON' THEN 'tree' END, s.`tanggal_tanam`, s.`jumlah_tanam`,
       s.`waktu_prakiraan_panen`, s.`prakiraan_jumlah_panen`, s.`waktu_panen`, s.`jumlah_panen`, NULL,
       s.`harga_jual`, NULL, s.`gambar`, s.created_at, s.updated_at
FROM `buruansae_lama`.`data_sayur` s
JOIN `commodities` c ON c.name = TRIM(s.`nama_sayur`)
JOIN `sectors` sec ON sec.id = c.sector_id AND sec.code = 'SAYUR'
ORDER BY s.`id_sayur`;

INSERT INTO `_import_productions`
  (legacy_table, legacy_id, farmer_group_id, commodity_id, planting_category, start_date, initial_quantity,
   estimated_harvest_date, estimated_harvest_quantity, harvest_date, harvest_quantity, harvest_head_count,
   selling_price, notes, image, created_at, updated_at)
SELECT 'data_buah', s.`id_buah`, s.id_kelompok, c.id, CASE UPPER(s.`kategori_tumbuhan`) WHEN 'BENIH' THEN 'seed' WHEN 'BIBIT' THEN 'seedling' WHEN 'POHON' THEN 'tree' END, s.`tanggal_tanam`, s.`jumlah_tanam`,
       s.`waktu_prakiraan_panen`, s.`prakiraan_jumlah_panen`, s.`waktu_panen`, s.`jumlah_panen`, NULL,
       s.`harga_jual`, NULL, s.`gambar`, s.created_at, s.updated_at
FROM `buruansae_lama`.`data_buah` s
JOIN `commodities` c ON c.name = TRIM(s.`nama_buah`)
JOIN `sectors` sec ON sec.id = c.sector_id AND sec.code = 'BUAH'
ORDER BY s.`id_buah`;

INSERT INTO `_import_productions`
  (legacy_table, legacy_id, farmer_group_id, commodity_id, planting_category, start_date, initial_quantity,
   estimated_harvest_date, estimated_harvest_quantity, harvest_date, harvest_quantity, harvest_head_count,
   selling_price, notes, image, created_at, updated_at)
SELECT 'data_tanaman_obat', s.`id_tanaman_obat`, s.id_kelompok, c.id, CASE UPPER(s.`kategori_tumbuhan`) WHEN 'BENIH' THEN 'seed' WHEN 'BIBIT' THEN 'seedling' WHEN 'POHON' THEN 'tree' END, s.`tanggal_tanam`, s.`jumlah_tanam`,
       s.`waktu_prakiraan_panen`, s.`prakiraan_jumlah_panen`, s.`waktu_panen`, s.`jumlah_panen`, NULL,
       s.`harga_jual`, NULL, s.`gambar`, s.created_at, s.updated_at
FROM `buruansae_lama`.`data_tanaman_obat` s
JOIN `commodities` c ON c.name = TRIM(s.`nama_tanaman_obat`)
JOIN `sectors` sec ON sec.id = c.sector_id AND sec.code = 'TANAMAN_OBAT'
ORDER BY s.`id_tanaman_obat`;

INSERT INTO `_import_productions`
  (legacy_table, legacy_id, farmer_group_id, commodity_id, planting_category, start_date, initial_quantity,
   estimated_harvest_date, estimated_harvest_quantity, harvest_date, harvest_quantity, harvest_head_count,
   selling_price, notes, image, created_at, updated_at)
SELECT 'data_ikan', s.`id_ikan`, s.id_kelompok, c.id, NULL, NULL, s.`jumlah_ikan`,
       s.`waktu_prakiraan_panen`, s.`prakiraan_jumlah_panen`, s.`waktu_panen`, s.`jumlah_panen_kg`, s.`jumlah_panen_ekor`,
       s.`harga_jual`, NULL, s.`gambar`, s.created_at, s.updated_at
FROM `buruansae_lama`.`data_ikan` s
JOIN `commodities` c ON c.name = TRIM(s.`jenis_ikan`)
JOIN `sectors` sec ON sec.id = c.sector_id AND sec.code = 'IKAN'
ORDER BY s.`id_ikan`;

INSERT INTO `_import_productions`
  (legacy_table, legacy_id, farmer_group_id, commodity_id, planting_category, start_date, initial_quantity,
   estimated_harvest_date, estimated_harvest_quantity, harvest_date, harvest_quantity, harvest_head_count,
   selling_price, notes, image, created_at, updated_at)
SELECT 'data_ternak', s.`id_ternak`, s.id_kelompok, c.id, NULL, NULL, s.`jumlah_ternak`,
       s.`waktu_prakiraan_panen`, s.`prakiraan_jumlah_panen`, s.`waktu_panen`, s.`jumlah_panen_kg`, s.`jumlah_panen_ekor`,
       s.`harga_jual`, NULL, s.`gambar`, s.created_at, s.updated_at
FROM `buruansae_lama`.`data_ternak` s
JOIN `commodities` c ON c.name = TRIM(s.`jenis_ternak`)
JOIN `sectors` sec ON sec.id = c.sector_id AND sec.code = 'TERNAK'
ORDER BY s.`id_ternak`;

INSERT INTO `_import_productions`
  (legacy_table, legacy_id, farmer_group_id, commodity_id, planting_category, start_date, initial_quantity,
   estimated_harvest_date, estimated_harvest_quantity, harvest_date, harvest_quantity, harvest_head_count,
   selling_price, notes, image, created_at, updated_at)
SELECT 'data_olahan_hasil', s.`id_data_olahan_hasil`, s.id_kelompok, c.id, NULL, s.`tanggal_produksi`, NULL,
       NULL, NULL, s.`waktu_panen`, s.`jumlah_panen`, NULL,
       s.`harga_jual`, NULL, s.`gambar`, s.created_at, s.updated_at
FROM `buruansae_lama`.`data_olahan_hasil` s
JOIN `commodities` c ON c.name = TRIM(s.`jenis_olahan`)
JOIN `sectors` sec ON sec.id = c.sector_id AND sec.code = 'OLAHAN_HASIL'
ORDER BY s.`id_data_olahan_hasil`;

INSERT INTO `_import_productions`
  (legacy_table, legacy_id, farmer_group_id, commodity_id, planting_category, start_date, initial_quantity,
   estimated_harvest_date, estimated_harvest_quantity, harvest_date, harvest_quantity, harvest_head_count,
   selling_price, notes, image, created_at, updated_at)
SELECT 'data_bibit', s.`id_bibit`, s.id_kelompok, c.id, NULL, s.`tanggal_tanam`, s.`jumlah_semai`,
       s.`waktu_prakiraan_panen`, s.`prakiraan_jumlah_panen`, s.`waktu_panen`, s.`jumlah_panen`, NULL,
       s.`harga_jual`, s.`keterangan`, s.`gambar`, s.created_at, s.updated_at
FROM `buruansae_lama`.`data_bibit` s
JOIN `commodities` c ON c.name = TRIM(s.`nama_sayur`)
JOIN `sectors` sec ON sec.id = c.sector_id AND sec.code = 'BIBIT'
ORDER BY s.`id_bibit`;

INSERT INTO `_import_productions`
  (legacy_table, legacy_id, farmer_group_id, commodity_id, planting_category, start_date, initial_quantity,
   estimated_harvest_date, estimated_harvest_quantity, harvest_date, harvest_quantity, harvest_head_count,
   selling_price, notes, image, created_at, updated_at)
SELECT 'data_sampah', s.`id_data_sampah`, s.id_kelompok, c.id, NULL, s.`tanggal_masuk`, s.`jumlah_sampah`,
       s.`waktu_prakiraan_panen`, s.`prakiraan_jumlah_panen`, s.`waktu_panen`, s.`jumlah_panen`, NULL,
       s.`harga_jual`, NULL, s.`gambar`, s.created_at, s.updated_at
FROM `buruansae_lama`.`data_sampah` s
JOIN `commodities` c ON c.name = TRIM(s.`jenis_pengolahan`)
JOIN `sectors` sec ON sec.id = c.sector_id AND sec.code = 'OLAHAN_SAMPAH'
ORDER BY s.`id_data_sampah`;

INSERT INTO `productions`
  (id, farmer_group_id, commodity_id, planting_category, start_date, initial_quantity,
   estimated_harvest_date, estimated_harvest_quantity, harvest_date, harvest_quantity,
   harvest_head_count, selling_price, notes, image, created_at, updated_at)
SELECT new_id, farmer_group_id, commodity_id, planting_category, start_date, initial_quantity,
       estimated_harvest_date, estimated_harvest_quantity, harvest_date, harvest_quantity,
       harvest_head_count, selling_price, notes, image, created_at, updated_at
FROM `_import_productions`
ORDER BY new_id;


-- -----------------------------------------------------------------------------
-- 4. DETAIL KHUSUS SEKTOR
-- -----------------------------------------------------------------------------
INSERT INTO `production_inputs` (production_id, type, name, quantity, applied_date)
SELECT ip.new_id, 'fertilizer', s.jenis_pupuk, s.jumlah_pupuk, s.waktu_pupuk
FROM `buruansae_lama`.`data_buah` s
JOIN `_import_productions` ip ON ip.legacy_table = 'data_buah' AND ip.legacy_id = s.id_buah
WHERE s.jenis_pupuk IS NOT NULL OR s.jumlah_pupuk IS NOT NULL OR s.waktu_pupuk IS NOT NULL;

INSERT INTO `production_inputs` (production_id, type, name, quantity, applied_date)
SELECT ip.new_id, 'feed', NULL, s.jumlah_pakan, s.waktu_pakan
FROM `buruansae_lama`.`data_ikan` s
JOIN `_import_productions` ip ON ip.legacy_table = 'data_ikan' AND ip.legacy_id = s.id_ikan
WHERE s.jumlah_pakan IS NOT NULL OR s.waktu_pakan IS NOT NULL;

INSERT INTO `production_inputs` (production_id, type, name, quantity, applied_date)
SELECT ip.new_id, 'feed', NULL, s.jumlah_pakan, s.waktu_pakan
FROM `buruansae_lama`.`data_ternak` s
JOIN `_import_productions` ip ON ip.legacy_table = 'data_ternak' AND ip.legacy_id = s.id_ternak
WHERE s.jumlah_pakan IS NOT NULL OR s.waktu_pakan IS NOT NULL;

INSERT INTO `processed_product_details`
  (production_id, base_ingredient, brand, recipe, lab_test, halal_permit, pirt_permit)
SELECT ip.new_id, s.bahan_dasar, s.merk, s.resep, s.uji_lab, s.izin_halal, s.izin_pirt
FROM `buruansae_lama`.`data_olahan_hasil` s
JOIN `_import_productions` ip ON ip.legacy_table = 'data_olahan_hasil' AND ip.legacy_id = s.id_data_olahan_hasil;

INSERT INTO `seedling_details` (production_id, origin)
SELECT ip.new_id, s.asal_bibit
FROM `buruansae_lama`.`data_bibit` s
JOIN `_import_productions` ip ON ip.legacy_table = 'data_bibit' AND ip.legacy_id = s.id_bibit;


-- -----------------------------------------------------------------------------
-- 5. DISTRIBUSI (kolom lebar → baris)
--    Kategori DIBAGIKAN_REKAP hanya dibuat kalau data lama bibit/sampah
--    memang punya rekap KK/orang (tabel lama menyimpannya sebagai total).
-- -----------------------------------------------------------------------------
INSERT INTO `recipient_categories` (code, name, sort_order, created_at, updated_at)
SELECT 'DIBAGIKAN_REKAP', 'Rekap dibagikan (data lama bibit/sampah)', 19, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `recipient_categories` WHERE code = 'DIBAGIKAN_REKAP')
  AND (EXISTS (SELECT 1 FROM `buruansae_lama`.`data_bibit`  WHERE jumlah_kk IS NOT NULL OR jumlah_orang IS NOT NULL)
    OR EXISTS (SELECT 1 FROM `buruansae_lama`.`data_sampah` WHERE jumlah_kk IS NOT NULL OR jumlah_orang IS NOT NULL));

INSERT INTO `distributions` (production_id, recipient_category_id, quantity, household_count, person_count)
SELECT ip.new_id, rc.id, x.qty, x.kk, x.orang
FROM (
  SELECT `id_sayur` AS legacy_id, 'KP' AS code, o.`jumlah_berat_kp_kg` AS qty, o.`jumlah_kepala_keluarga_kp_kk` AS kk, o.`jumlah_orang_kp` AS orang FROM `buruansae_lama`.`data_sayur` o
  UNION ALL
  SELECT `id_sayur` AS legacy_id, 'STUNTING' AS code, o.`jumlah_berat_dibagikan_stunting_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_stunting` AS kk, o.`jumlah_orang_dibagikan_stunting` AS orang FROM `buruansae_lama`.`data_sayur` o
  UNION ALL
  SELECT `id_sayur` AS legacy_id, 'MM' AS code, o.`jumlah_berat_dibagikan_mm_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_mm` AS kk, o.`jumlah_orang_dibagikan_mm` AS orang FROM `buruansae_lama`.`data_sayur` o
  UNION ALL
  SELECT `id_sayur` AS legacy_id, 'LANSIA' AS code, o.`jumlah_berat_dibagikan_lansia_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_lansia` AS kk, o.`jumlah_orang_dibagikan_lansia` AS orang FROM `buruansae_lama`.`data_sayur` o
  UNION ALL
  SELECT `id_sayur` AS legacy_id, 'POSYANDU' AS code, o.`jumlah_berat_dibagikan_posyandu_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_posyandu` AS kk, o.`jumlah_orang_dibagikan_posyandu` AS orang FROM `buruansae_lama`.`data_sayur` o
  UNION ALL
  SELECT `id_sayur` AS legacy_id, 'DIJUAL' AS code, o.`jumlah_berat_dijual_kg` AS qty, NULL AS kk, o.`jumlah_orang_dijual` AS orang FROM `buruansae_lama`.`data_sayur` o
) x
JOIN `_import_productions` ip ON ip.legacy_table = 'data_sayur' AND ip.legacy_id = x.legacy_id
JOIN `recipient_categories` rc ON rc.code = x.code
WHERE x.qty IS NOT NULL OR x.kk IS NOT NULL OR x.orang IS NOT NULL;

INSERT INTO `distributions` (production_id, recipient_category_id, quantity, household_count, person_count)
SELECT ip.new_id, rc.id, x.qty, x.kk, x.orang
FROM (
  SELECT `id_buah` AS legacy_id, 'KP' AS code, o.`jumlah_berat_kp_kg` AS qty, o.`jumlah_kepala_keluarga_kp_kk` AS kk, o.`jumlah_orang_kp` AS orang FROM `buruansae_lama`.`data_buah` o
  UNION ALL
  SELECT `id_buah` AS legacy_id, 'STUNTING' AS code, o.`jumlah_berat_dibagikan_stunting_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_stunting` AS kk, o.`jumlah_orang_dibagikan_stunting` AS orang FROM `buruansae_lama`.`data_buah` o
  UNION ALL
  SELECT `id_buah` AS legacy_id, 'MM' AS code, o.`jumlah_berat_dibagikan_mm_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_mm` AS kk, o.`jumlah_orang_dibagikan_mm` AS orang FROM `buruansae_lama`.`data_buah` o
  UNION ALL
  SELECT `id_buah` AS legacy_id, 'LANSIA' AS code, o.`jumlah_berat_dibagikan_lansia_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_lansia` AS kk, o.`jumlah_orang_dibagikan_lansia` AS orang FROM `buruansae_lama`.`data_buah` o
  UNION ALL
  SELECT `id_buah` AS legacy_id, 'POSYANDU' AS code, o.`jumlah_berat_dibagikan_posyandu_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_posyandu` AS kk, o.`jumlah_orang_dibagikan_posyandu` AS orang FROM `buruansae_lama`.`data_buah` o
  UNION ALL
  SELECT `id_buah` AS legacy_id, 'DIJUAL' AS code, o.`jumlah_berat_dijual_kg` AS qty, NULL AS kk, o.`jumlah_orang_dijual` AS orang FROM `buruansae_lama`.`data_buah` o
) x
JOIN `_import_productions` ip ON ip.legacy_table = 'data_buah' AND ip.legacy_id = x.legacy_id
JOIN `recipient_categories` rc ON rc.code = x.code
WHERE x.qty IS NOT NULL OR x.kk IS NOT NULL OR x.orang IS NOT NULL;

INSERT INTO `distributions` (production_id, recipient_category_id, quantity, household_count, person_count)
SELECT ip.new_id, rc.id, x.qty, x.kk, x.orang
FROM (
  SELECT `id_tanaman_obat` AS legacy_id, 'KP' AS code, o.`jumlah_berat_kp_kg` AS qty, o.`jumlah_kepala_keluarga_kp_kk` AS kk, o.`jumlah_orang_kp` AS orang FROM `buruansae_lama`.`data_tanaman_obat` o
  UNION ALL
  SELECT `id_tanaman_obat` AS legacy_id, 'STUNTING' AS code, o.`jumlah_berat_dibagikan_stunting_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_stunting` AS kk, o.`jumlah_orang_dibagikan_stunting` AS orang FROM `buruansae_lama`.`data_tanaman_obat` o
  UNION ALL
  SELECT `id_tanaman_obat` AS legacy_id, 'MM' AS code, o.`jumlah_berat_dibagikan_mm_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_mm` AS kk, o.`jumlah_orang_dibagikan_mm` AS orang FROM `buruansae_lama`.`data_tanaman_obat` o
  UNION ALL
  SELECT `id_tanaman_obat` AS legacy_id, 'LANSIA' AS code, o.`jumlah_berat_dibagikan_lansia_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_lansia` AS kk, o.`jumlah_orang_dibagikan_lansia` AS orang FROM `buruansae_lama`.`data_tanaman_obat` o
  UNION ALL
  SELECT `id_tanaman_obat` AS legacy_id, 'POSYANDU' AS code, o.`jumlah_berat_dibagikan_posyandu_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_posyandu` AS kk, o.`jumlah_orang_dibagikan_posyandu` AS orang FROM `buruansae_lama`.`data_tanaman_obat` o
  UNION ALL
  SELECT `id_tanaman_obat` AS legacy_id, 'DIJUAL' AS code, o.`jumlah_berat_dijual_kg` AS qty, NULL AS kk, o.`jumlah_orang_dijual` AS orang FROM `buruansae_lama`.`data_tanaman_obat` o
) x
JOIN `_import_productions` ip ON ip.legacy_table = 'data_tanaman_obat' AND ip.legacy_id = x.legacy_id
JOIN `recipient_categories` rc ON rc.code = x.code
WHERE x.qty IS NOT NULL OR x.kk IS NOT NULL OR x.orang IS NOT NULL;

INSERT INTO `distributions` (production_id, recipient_category_id, quantity, household_count, person_count)
SELECT ip.new_id, rc.id, x.qty, x.kk, x.orang
FROM (
  SELECT `id_ikan` AS legacy_id, 'KP' AS code, o.`jumlah_berat_kp_kg` AS qty, o.`jumlah_kepala_keluarga_kp_kk` AS kk, o.`jumlah_orang_kp` AS orang FROM `buruansae_lama`.`data_ikan` o
  UNION ALL
  SELECT `id_ikan` AS legacy_id, 'STUNTING' AS code, o.`jumlah_berat_dibagikan_stunting_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_stunting` AS kk, o.`jumlah_orang_dibagikan_stunting` AS orang FROM `buruansae_lama`.`data_ikan` o
  UNION ALL
  SELECT `id_ikan` AS legacy_id, 'MM' AS code, o.`jumlah_berat_dibagikan_mm_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_mm` AS kk, o.`jumlah_orang_dibagikan_mm` AS orang FROM `buruansae_lama`.`data_ikan` o
  UNION ALL
  SELECT `id_ikan` AS legacy_id, 'LANSIA' AS code, o.`jumlah_berat_dibagikan_lansia_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_lansia` AS kk, o.`jumlah_orang_dibagikan_lansia` AS orang FROM `buruansae_lama`.`data_ikan` o
  UNION ALL
  SELECT `id_ikan` AS legacy_id, 'POSYANDU' AS code, o.`jumlah_berat_dibagikan_posyandu_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_posyandu` AS kk, o.`jumlah_orang_dibagikan_posyandu` AS orang FROM `buruansae_lama`.`data_ikan` o
  UNION ALL
  SELECT `id_ikan` AS legacy_id, 'DIJUAL' AS code, o.`jumlah_berat_dijual_kg` AS qty, NULL AS kk, o.`jumlah_orang_dijual` AS orang FROM `buruansae_lama`.`data_ikan` o
) x
JOIN `_import_productions` ip ON ip.legacy_table = 'data_ikan' AND ip.legacy_id = x.legacy_id
JOIN `recipient_categories` rc ON rc.code = x.code
WHERE x.qty IS NOT NULL OR x.kk IS NOT NULL OR x.orang IS NOT NULL;

INSERT INTO `distributions` (production_id, recipient_category_id, quantity, household_count, person_count)
SELECT ip.new_id, rc.id, x.qty, x.kk, x.orang
FROM (
  SELECT `id_ternak` AS legacy_id, 'KP' AS code, o.`jumlah_berat_kp_kg` AS qty, o.`jumlah_kepala_keluarga_kp_kk` AS kk, o.`jumlah_orang_kp` AS orang FROM `buruansae_lama`.`data_ternak` o
  UNION ALL
  SELECT `id_ternak` AS legacy_id, 'STUNTING' AS code, o.`jumlah_berat_dibagikan_stunting_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_stunting` AS kk, o.`jumlah_orang_dibagikan_stunting` AS orang FROM `buruansae_lama`.`data_ternak` o
  UNION ALL
  SELECT `id_ternak` AS legacy_id, 'MM' AS code, o.`jumlah_berat_dibagikan_mm_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_mm` AS kk, o.`jumlah_orang_dibagikan_mm` AS orang FROM `buruansae_lama`.`data_ternak` o
  UNION ALL
  SELECT `id_ternak` AS legacy_id, 'LANSIA' AS code, o.`jumlah_berat_dibagikan_lansia_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_lansia` AS kk, o.`jumlah_orang_dibagikan_lansia` AS orang FROM `buruansae_lama`.`data_ternak` o
  UNION ALL
  SELECT `id_ternak` AS legacy_id, 'POSYANDU' AS code, o.`jumlah_berat_dibagikan_posyandu_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_posyandu` AS kk, o.`jumlah_orang_dibagikan_posyandu` AS orang FROM `buruansae_lama`.`data_ternak` o
  UNION ALL
  SELECT `id_ternak` AS legacy_id, 'DIJUAL' AS code, o.`jumlah_berat_dijual_kg` AS qty, NULL AS kk, o.`jumlah_orang_dijual` AS orang FROM `buruansae_lama`.`data_ternak` o
) x
JOIN `_import_productions` ip ON ip.legacy_table = 'data_ternak' AND ip.legacy_id = x.legacy_id
JOIN `recipient_categories` rc ON rc.code = x.code
WHERE x.qty IS NOT NULL OR x.kk IS NOT NULL OR x.orang IS NOT NULL;

INSERT INTO `distributions` (production_id, recipient_category_id, quantity, household_count, person_count)
SELECT ip.new_id, rc.id, x.qty, x.kk, x.orang
FROM (
  SELECT `id_data_olahan_hasil` AS legacy_id, 'KP' AS code, o.`jumlah_berat_kp_kg` AS qty, o.`jumlah_kepala_keluarga_kp_kk` AS kk, o.`jumlah_orang_kp` AS orang FROM `buruansae_lama`.`data_olahan_hasil` o
  UNION ALL
  SELECT `id_data_olahan_hasil` AS legacy_id, 'STUNTING' AS code, o.`jumlah_berat_dibagikan_stunting_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_stunting` AS kk, o.`jumlah_orang_dibagikan_stunting` AS orang FROM `buruansae_lama`.`data_olahan_hasil` o
  UNION ALL
  SELECT `id_data_olahan_hasil` AS legacy_id, 'MM' AS code, o.`jumlah_berat_dibagikan_mm_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_mm` AS kk, o.`jumlah_orang_dibagikan_mm` AS orang FROM `buruansae_lama`.`data_olahan_hasil` o
  UNION ALL
  SELECT `id_data_olahan_hasil` AS legacy_id, 'LANSIA' AS code, o.`jumlah_berat_dibagikan_lansia_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_lansia` AS kk, o.`jumlah_orang_dibagikan_lansia` AS orang FROM `buruansae_lama`.`data_olahan_hasil` o
  UNION ALL
  SELECT `id_data_olahan_hasil` AS legacy_id, 'POSYANDU' AS code, o.`jumlah_berat_dibagikan_posyandu_kg` AS qty, o.`jumlah_kepala_keluarga_dibagikan_posyandu` AS kk, o.`jumlah_orang_dibagikan_posyandu` AS orang FROM `buruansae_lama`.`data_olahan_hasil` o
  UNION ALL
  SELECT `id_data_olahan_hasil` AS legacy_id, 'DIJUAL' AS code, o.`jumlah_berat_dijual_kg` AS qty, NULL AS kk, o.`jumlah_orang_dijual` AS orang FROM `buruansae_lama`.`data_olahan_hasil` o
) x
JOIN `_import_productions` ip ON ip.legacy_table = 'data_olahan_hasil' AND ip.legacy_id = x.legacy_id
JOIN `recipient_categories` rc ON rc.code = x.code
WHERE x.qty IS NOT NULL OR x.kk IS NOT NULL OR x.orang IS NOT NULL;

INSERT INTO `distributions` (production_id, recipient_category_id, quantity, household_count, person_count)
SELECT ip.new_id, rc.id, x.qty, x.kk, x.orang
FROM (
  SELECT `id_bibit` AS legacy_id, 'KP' AS code, o.`jumlah_kp` AS qty, NULL AS kk, NULL AS orang FROM `buruansae_lama`.`data_bibit` o
  UNION ALL
  SELECT `id_bibit` AS legacy_id, 'MS' AS code, o.`jumlah_ms` AS qty, NULL AS kk, NULL AS orang FROM `buruansae_lama`.`data_bibit` o
  UNION ALL
  SELECT `id_bibit` AS legacy_id, 'SEKOLAH' AS code, o.`jumlah_sekolah` AS qty, NULL AS kk, NULL AS orang FROM `buruansae_lama`.`data_bibit` o
  UNION ALL
  SELECT `id_bibit` AS legacy_id, 'PKK' AS code, o.`jumlah_pkk` AS qty, NULL AS kk, NULL AS orang FROM `buruansae_lama`.`data_bibit` o
  UNION ALL
  SELECT `id_bibit` AS legacy_id, 'POSYANDU' AS code, o.`jumlah_posyandu` AS qty, NULL AS kk, NULL AS orang FROM `buruansae_lama`.`data_bibit` o
  UNION ALL
  SELECT `id_bibit` AS legacy_id, 'LAINNYA' AS code, o.`jumlah_lainnya` AS qty, NULL AS kk, NULL AS orang FROM `buruansae_lama`.`data_bibit` o
  UNION ALL
  SELECT `id_bibit` AS legacy_id, 'DIBAGIKAN_REKAP' AS code, NULL AS qty, o.`jumlah_kk` AS kk, o.`jumlah_orang` AS orang FROM `buruansae_lama`.`data_bibit` o
  UNION ALL
  SELECT `id_bibit` AS legacy_id, 'DIJUAL' AS code, o.`jumlah_dijual_pohon` AS qty, o.`jumlah_dijual_kk` AS kk, o.`jumlah_dijual_orang` AS orang FROM `buruansae_lama`.`data_bibit` o
) x
JOIN `_import_productions` ip ON ip.legacy_table = 'data_bibit' AND ip.legacy_id = x.legacy_id
JOIN `recipient_categories` rc ON rc.code = x.code
WHERE x.qty IS NOT NULL OR x.kk IS NOT NULL OR x.orang IS NOT NULL;

INSERT INTO `distributions` (production_id, recipient_category_id, quantity, household_count, person_count)
SELECT ip.new_id, rc.id, x.qty, x.kk, x.orang
FROM (
  SELECT `id_data_sampah` AS legacy_id, 'KP' AS code, o.`jumlah_kp` AS qty, NULL AS kk, NULL AS orang FROM `buruansae_lama`.`data_sampah` o
  UNION ALL
  SELECT `id_data_sampah` AS legacy_id, 'MS' AS code, o.`jumlah_ms` AS qty, NULL AS kk, NULL AS orang FROM `buruansae_lama`.`data_sampah` o
  UNION ALL
  SELECT `id_data_sampah` AS legacy_id, 'SEKOLAH' AS code, o.`jumlah_sekolah` AS qty, NULL AS kk, NULL AS orang FROM `buruansae_lama`.`data_sampah` o
  UNION ALL
  SELECT `id_data_sampah` AS legacy_id, 'PKK' AS code, o.`jumlah_pkk` AS qty, NULL AS kk, NULL AS orang FROM `buruansae_lama`.`data_sampah` o
  UNION ALL
  SELECT `id_data_sampah` AS legacy_id, 'POSYANDU' AS code, o.`jumlah_posyandu` AS qty, NULL AS kk, NULL AS orang FROM `buruansae_lama`.`data_sampah` o
  UNION ALL
  SELECT `id_data_sampah` AS legacy_id, 'LAINNYA' AS code, o.`jumlah_lainnya` AS qty, NULL AS kk, NULL AS orang FROM `buruansae_lama`.`data_sampah` o
  UNION ALL
  SELECT `id_data_sampah` AS legacy_id, 'DIBAGIKAN_REKAP' AS code, NULL AS qty, o.`jumlah_kk` AS kk, o.`jumlah_orang` AS orang FROM `buruansae_lama`.`data_sampah` o
  UNION ALL
  SELECT `id_data_sampah` AS legacy_id, 'DIJUAL' AS code, o.`jumlah_dijual_kg` AS qty, o.`jumlah_dijual_kk` AS kk, o.`jumlah_dijual_orang` AS orang FROM `buruansae_lama`.`data_sampah` o
) x
JOIN `_import_productions` ip ON ip.legacy_table = 'data_sampah' AND ip.legacy_id = x.legacy_id
JOIN `recipient_categories` rc ON rc.code = x.code
WHERE x.qty IS NOT NULL OR x.kk IS NOT NULL OR x.orang IS NOT NULL;


-- -----------------------------------------------------------------------------
-- 6. REKAP BULANAN
-- -----------------------------------------------------------------------------
INSERT INTO `monthly_distribution_recaps`
  (id, village_id, period_date, harvest, self_consumption, distributed, sold)
SELECT r.id_distribusi, v.id, r.tanggal, r.panen, r.konsumsi_sendiri, r.dibagikan, r.dijual
FROM `buruansae_lama`.`data_distribusi` r
LEFT JOIN `districts` d ON d.name = r.kecamatan
LEFT JOIN `villages` v ON v.name = r.kelurahan AND v.district_id = d.id
ORDER BY r.id_distribusi;


-- -----------------------------------------------------------------------------
-- 7. USER
--    Hash password Myth/Auth = bcrypt(base64(sha384(password))), sedangkan
--    Laravel = bcrypt(password). Hash lama disalin apa adanya, tapi TIDAK BISA
--    dipakai login di Laravel — setel ulang password tiap akun (lihat README).
-- -----------------------------------------------------------------------------
INSERT INTO `users` (id, name, username, email, password, is_active, created_at, updated_at)
SELECT id, COALESCE(username, email), username, email, password_hash, COALESCE(active, 1), created_at, updated_at
FROM `buruansae_lama`.`users`
WHERE deleted_at IS NULL
ORDER BY id;


-- -----------------------------------------------------------------------------
-- 8. VERIFIKASI
--    (a) Setiap baris lama dibandingkan kolom per kolom dengan baris barunya.
--        Benar bila baris_lama = baris_baru dan baris_berbeda = 0.
-- -----------------------------------------------------------------------------
SELECT 'data_sayur' AS tabel_lama,
  (SELECT COUNT(*) FROM `buruansae_lama`.`data_sayur`) AS baris_lama,
  (SELECT COUNT(*) FROM `_import_productions` WHERE legacy_table = 'data_sayur') AS baris_baru,
  (SELECT COUNT(*) FROM `buruansae_lama`.`data_sayur` s
    WHERE NOT EXISTS (
      SELECT 1 FROM `_import_productions` ip
      JOIN `productions` pr ON pr.id = ip.new_id
      JOIN `commodities` c ON c.id = pr.commodity_id
      WHERE ip.legacy_table = 'data_sayur' AND ip.legacy_id = s.`id_sayur`
        AND pr.farmer_group_id <=> s.id_kelompok
        AND c.name = TRIM(s.`nama_sayur`)
        AND pr.start_date <=> s.`tanggal_tanam`
        AND pr.initial_quantity <=> s.`jumlah_tanam`
        AND pr.harvest_date <=> s.waktu_panen
        AND pr.harvest_quantity <=> s.`jumlah_panen`
        AND pr.harvest_head_count <=> NULL
        AND pr.selling_price <=> s.harga_jual
        AND pr.image <=> s.gambar
        AND pr.estimated_harvest_date <=> s.waktu_prakiraan_panen
        AND pr.estimated_harvest_quantity <=> s.prakiraan_jumlah_panen
        AND pr.planting_category <=> CASE UPPER(s.`kategori_tumbuhan`) WHEN 'BENIH' THEN 'seed' WHEN 'BIBIT' THEN 'seedling' WHEN 'POHON' THEN 'tree' END)) AS baris_berbeda
UNION ALL
SELECT 'data_buah' AS tabel_lama,
  (SELECT COUNT(*) FROM `buruansae_lama`.`data_buah`) AS baris_lama,
  (SELECT COUNT(*) FROM `_import_productions` WHERE legacy_table = 'data_buah') AS baris_baru,
  (SELECT COUNT(*) FROM `buruansae_lama`.`data_buah` s
    WHERE NOT EXISTS (
      SELECT 1 FROM `_import_productions` ip
      JOIN `productions` pr ON pr.id = ip.new_id
      JOIN `commodities` c ON c.id = pr.commodity_id
      WHERE ip.legacy_table = 'data_buah' AND ip.legacy_id = s.`id_buah`
        AND pr.farmer_group_id <=> s.id_kelompok
        AND c.name = TRIM(s.`nama_buah`)
        AND pr.start_date <=> s.`tanggal_tanam`
        AND pr.initial_quantity <=> s.`jumlah_tanam`
        AND pr.harvest_date <=> s.waktu_panen
        AND pr.harvest_quantity <=> s.`jumlah_panen`
        AND pr.harvest_head_count <=> NULL
        AND pr.selling_price <=> s.harga_jual
        AND pr.image <=> s.gambar
        AND pr.estimated_harvest_date <=> s.waktu_prakiraan_panen
        AND pr.estimated_harvest_quantity <=> s.prakiraan_jumlah_panen
        AND pr.planting_category <=> CASE UPPER(s.`kategori_tumbuhan`) WHEN 'BENIH' THEN 'seed' WHEN 'BIBIT' THEN 'seedling' WHEN 'POHON' THEN 'tree' END)) AS baris_berbeda
UNION ALL
SELECT 'data_tanaman_obat' AS tabel_lama,
  (SELECT COUNT(*) FROM `buruansae_lama`.`data_tanaman_obat`) AS baris_lama,
  (SELECT COUNT(*) FROM `_import_productions` WHERE legacy_table = 'data_tanaman_obat') AS baris_baru,
  (SELECT COUNT(*) FROM `buruansae_lama`.`data_tanaman_obat` s
    WHERE NOT EXISTS (
      SELECT 1 FROM `_import_productions` ip
      JOIN `productions` pr ON pr.id = ip.new_id
      JOIN `commodities` c ON c.id = pr.commodity_id
      WHERE ip.legacy_table = 'data_tanaman_obat' AND ip.legacy_id = s.`id_tanaman_obat`
        AND pr.farmer_group_id <=> s.id_kelompok
        AND c.name = TRIM(s.`nama_tanaman_obat`)
        AND pr.start_date <=> s.`tanggal_tanam`
        AND pr.initial_quantity <=> s.`jumlah_tanam`
        AND pr.harvest_date <=> s.waktu_panen
        AND pr.harvest_quantity <=> s.`jumlah_panen`
        AND pr.harvest_head_count <=> NULL
        AND pr.selling_price <=> s.harga_jual
        AND pr.image <=> s.gambar
        AND pr.estimated_harvest_date <=> s.waktu_prakiraan_panen
        AND pr.estimated_harvest_quantity <=> s.prakiraan_jumlah_panen
        AND pr.planting_category <=> CASE UPPER(s.`kategori_tumbuhan`) WHEN 'BENIH' THEN 'seed' WHEN 'BIBIT' THEN 'seedling' WHEN 'POHON' THEN 'tree' END)) AS baris_berbeda
UNION ALL
SELECT 'data_ikan' AS tabel_lama,
  (SELECT COUNT(*) FROM `buruansae_lama`.`data_ikan`) AS baris_lama,
  (SELECT COUNT(*) FROM `_import_productions` WHERE legacy_table = 'data_ikan') AS baris_baru,
  (SELECT COUNT(*) FROM `buruansae_lama`.`data_ikan` s
    WHERE NOT EXISTS (
      SELECT 1 FROM `_import_productions` ip
      JOIN `productions` pr ON pr.id = ip.new_id
      JOIN `commodities` c ON c.id = pr.commodity_id
      WHERE ip.legacy_table = 'data_ikan' AND ip.legacy_id = s.`id_ikan`
        AND pr.farmer_group_id <=> s.id_kelompok
        AND c.name = TRIM(s.`jenis_ikan`)
        AND pr.start_date <=> NULL
        AND pr.initial_quantity <=> s.`jumlah_ikan`
        AND pr.harvest_date <=> s.waktu_panen
        AND pr.harvest_quantity <=> s.`jumlah_panen_kg`
        AND pr.harvest_head_count <=> s.`jumlah_panen_ekor`
        AND pr.selling_price <=> s.harga_jual
        AND pr.image <=> s.gambar
        AND pr.estimated_harvest_date <=> s.waktu_prakiraan_panen
        AND pr.estimated_harvest_quantity <=> s.prakiraan_jumlah_panen)) AS baris_berbeda
UNION ALL
SELECT 'data_ternak' AS tabel_lama,
  (SELECT COUNT(*) FROM `buruansae_lama`.`data_ternak`) AS baris_lama,
  (SELECT COUNT(*) FROM `_import_productions` WHERE legacy_table = 'data_ternak') AS baris_baru,
  (SELECT COUNT(*) FROM `buruansae_lama`.`data_ternak` s
    WHERE NOT EXISTS (
      SELECT 1 FROM `_import_productions` ip
      JOIN `productions` pr ON pr.id = ip.new_id
      JOIN `commodities` c ON c.id = pr.commodity_id
      WHERE ip.legacy_table = 'data_ternak' AND ip.legacy_id = s.`id_ternak`
        AND pr.farmer_group_id <=> s.id_kelompok
        AND c.name = TRIM(s.`jenis_ternak`)
        AND pr.start_date <=> NULL
        AND pr.initial_quantity <=> s.`jumlah_ternak`
        AND pr.harvest_date <=> s.waktu_panen
        AND pr.harvest_quantity <=> s.`jumlah_panen_kg`
        AND pr.harvest_head_count <=> s.`jumlah_panen_ekor`
        AND pr.selling_price <=> s.harga_jual
        AND pr.image <=> s.gambar
        AND pr.estimated_harvest_date <=> s.waktu_prakiraan_panen
        AND pr.estimated_harvest_quantity <=> s.prakiraan_jumlah_panen)) AS baris_berbeda
UNION ALL
SELECT 'data_olahan_hasil' AS tabel_lama,
  (SELECT COUNT(*) FROM `buruansae_lama`.`data_olahan_hasil`) AS baris_lama,
  (SELECT COUNT(*) FROM `_import_productions` WHERE legacy_table = 'data_olahan_hasil') AS baris_baru,
  (SELECT COUNT(*) FROM `buruansae_lama`.`data_olahan_hasil` s
    WHERE NOT EXISTS (
      SELECT 1 FROM `_import_productions` ip
      JOIN `productions` pr ON pr.id = ip.new_id
      JOIN `commodities` c ON c.id = pr.commodity_id
      WHERE ip.legacy_table = 'data_olahan_hasil' AND ip.legacy_id = s.`id_data_olahan_hasil`
        AND pr.farmer_group_id <=> s.id_kelompok
        AND c.name = TRIM(s.`jenis_olahan`)
        AND pr.start_date <=> s.`tanggal_produksi`
        AND pr.initial_quantity <=> NULL
        AND pr.harvest_date <=> s.waktu_panen
        AND pr.harvest_quantity <=> s.`jumlah_panen`
        AND pr.harvest_head_count <=> NULL
        AND pr.selling_price <=> s.harga_jual
        AND pr.image <=> s.gambar)) AS baris_berbeda
UNION ALL
SELECT 'data_bibit' AS tabel_lama,
  (SELECT COUNT(*) FROM `buruansae_lama`.`data_bibit`) AS baris_lama,
  (SELECT COUNT(*) FROM `_import_productions` WHERE legacy_table = 'data_bibit') AS baris_baru,
  (SELECT COUNT(*) FROM `buruansae_lama`.`data_bibit` s
    WHERE NOT EXISTS (
      SELECT 1 FROM `_import_productions` ip
      JOIN `productions` pr ON pr.id = ip.new_id
      JOIN `commodities` c ON c.id = pr.commodity_id
      WHERE ip.legacy_table = 'data_bibit' AND ip.legacy_id = s.`id_bibit`
        AND pr.farmer_group_id <=> s.id_kelompok
        AND c.name = TRIM(s.`nama_sayur`)
        AND pr.start_date <=> s.`tanggal_tanam`
        AND pr.initial_quantity <=> s.`jumlah_semai`
        AND pr.harvest_date <=> s.waktu_panen
        AND pr.harvest_quantity <=> s.`jumlah_panen`
        AND pr.harvest_head_count <=> NULL
        AND pr.selling_price <=> s.harga_jual
        AND pr.image <=> s.gambar
        AND pr.estimated_harvest_date <=> s.waktu_prakiraan_panen
        AND pr.estimated_harvest_quantity <=> s.prakiraan_jumlah_panen
        AND pr.notes <=> s.`keterangan`)) AS baris_berbeda
UNION ALL
SELECT 'data_sampah' AS tabel_lama,
  (SELECT COUNT(*) FROM `buruansae_lama`.`data_sampah`) AS baris_lama,
  (SELECT COUNT(*) FROM `_import_productions` WHERE legacy_table = 'data_sampah') AS baris_baru,
  (SELECT COUNT(*) FROM `buruansae_lama`.`data_sampah` s
    WHERE NOT EXISTS (
      SELECT 1 FROM `_import_productions` ip
      JOIN `productions` pr ON pr.id = ip.new_id
      JOIN `commodities` c ON c.id = pr.commodity_id
      WHERE ip.legacy_table = 'data_sampah' AND ip.legacy_id = s.`id_data_sampah`
        AND pr.farmer_group_id <=> s.id_kelompok
        AND c.name = TRIM(s.`jenis_pengolahan`)
        AND pr.start_date <=> s.`tanggal_masuk`
        AND pr.initial_quantity <=> s.`jumlah_sampah`
        AND pr.harvest_date <=> s.waktu_panen
        AND pr.harvest_quantity <=> s.`jumlah_panen`
        AND pr.harvest_head_count <=> NULL
        AND pr.selling_price <=> s.harga_jual
        AND pr.image <=> s.gambar
        AND pr.estimated_harvest_date <=> s.waktu_prakiraan_panen
        AND pr.estimated_harvest_quantity <=> s.prakiraan_jumlah_panen)) AS baris_berbeda;

--    (b) Setiap nilai distribusi lama (berat/jumlah, KK, orang) per kategori
--        dibandingkan dengan tabel distributions. Benar bila HASILNYA KOSONG.
SELECT tabel_lama, kategori, selisih FROM (
  SELECT 'data_sayur' AS tabel_lama, 'KP' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_sayur` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_sayur' AND ip.legacy_id = s.`id_sayur`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'KP'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_kp_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_kp_kk` AND d.person_count <=> s.`jumlah_orang_kp`)
  UNION ALL
  SELECT 'data_sayur' AS tabel_lama, 'STUNTING' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_sayur` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_sayur' AND ip.legacy_id = s.`id_sayur`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'STUNTING'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_stunting_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_stunting` AND d.person_count <=> s.`jumlah_orang_dibagikan_stunting`)
  UNION ALL
  SELECT 'data_sayur' AS tabel_lama, 'MM' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_sayur` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_sayur' AND ip.legacy_id = s.`id_sayur`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'MM'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_mm_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_mm` AND d.person_count <=> s.`jumlah_orang_dibagikan_mm`)
  UNION ALL
  SELECT 'data_sayur' AS tabel_lama, 'LANSIA' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_sayur` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_sayur' AND ip.legacy_id = s.`id_sayur`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'LANSIA'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_lansia_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_lansia` AND d.person_count <=> s.`jumlah_orang_dibagikan_lansia`)
  UNION ALL
  SELECT 'data_sayur' AS tabel_lama, 'POSYANDU' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_sayur` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_sayur' AND ip.legacy_id = s.`id_sayur`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'POSYANDU'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_posyandu_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_posyandu` AND d.person_count <=> s.`jumlah_orang_dibagikan_posyandu`)
  UNION ALL
  SELECT 'data_sayur' AS tabel_lama, 'DIJUAL' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_sayur` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_sayur' AND ip.legacy_id = s.`id_sayur`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'DIJUAL'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dijual_kg` AND d.household_count <=> NULL AND d.person_count <=> s.`jumlah_orang_dijual`)
  UNION ALL
  SELECT 'data_buah' AS tabel_lama, 'KP' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_buah` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_buah' AND ip.legacy_id = s.`id_buah`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'KP'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_kp_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_kp_kk` AND d.person_count <=> s.`jumlah_orang_kp`)
  UNION ALL
  SELECT 'data_buah' AS tabel_lama, 'STUNTING' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_buah` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_buah' AND ip.legacy_id = s.`id_buah`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'STUNTING'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_stunting_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_stunting` AND d.person_count <=> s.`jumlah_orang_dibagikan_stunting`)
  UNION ALL
  SELECT 'data_buah' AS tabel_lama, 'MM' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_buah` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_buah' AND ip.legacy_id = s.`id_buah`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'MM'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_mm_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_mm` AND d.person_count <=> s.`jumlah_orang_dibagikan_mm`)
  UNION ALL
  SELECT 'data_buah' AS tabel_lama, 'LANSIA' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_buah` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_buah' AND ip.legacy_id = s.`id_buah`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'LANSIA'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_lansia_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_lansia` AND d.person_count <=> s.`jumlah_orang_dibagikan_lansia`)
  UNION ALL
  SELECT 'data_buah' AS tabel_lama, 'POSYANDU' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_buah` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_buah' AND ip.legacy_id = s.`id_buah`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'POSYANDU'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_posyandu_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_posyandu` AND d.person_count <=> s.`jumlah_orang_dibagikan_posyandu`)
  UNION ALL
  SELECT 'data_buah' AS tabel_lama, 'DIJUAL' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_buah` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_buah' AND ip.legacy_id = s.`id_buah`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'DIJUAL'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dijual_kg` AND d.household_count <=> NULL AND d.person_count <=> s.`jumlah_orang_dijual`)
  UNION ALL
  SELECT 'data_tanaman_obat' AS tabel_lama, 'KP' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_tanaman_obat` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_tanaman_obat' AND ip.legacy_id = s.`id_tanaman_obat`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'KP'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_kp_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_kp_kk` AND d.person_count <=> s.`jumlah_orang_kp`)
  UNION ALL
  SELECT 'data_tanaman_obat' AS tabel_lama, 'STUNTING' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_tanaman_obat` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_tanaman_obat' AND ip.legacy_id = s.`id_tanaman_obat`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'STUNTING'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_stunting_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_stunting` AND d.person_count <=> s.`jumlah_orang_dibagikan_stunting`)
  UNION ALL
  SELECT 'data_tanaman_obat' AS tabel_lama, 'MM' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_tanaman_obat` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_tanaman_obat' AND ip.legacy_id = s.`id_tanaman_obat`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'MM'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_mm_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_mm` AND d.person_count <=> s.`jumlah_orang_dibagikan_mm`)
  UNION ALL
  SELECT 'data_tanaman_obat' AS tabel_lama, 'LANSIA' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_tanaman_obat` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_tanaman_obat' AND ip.legacy_id = s.`id_tanaman_obat`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'LANSIA'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_lansia_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_lansia` AND d.person_count <=> s.`jumlah_orang_dibagikan_lansia`)
  UNION ALL
  SELECT 'data_tanaman_obat' AS tabel_lama, 'POSYANDU' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_tanaman_obat` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_tanaman_obat' AND ip.legacy_id = s.`id_tanaman_obat`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'POSYANDU'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_posyandu_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_posyandu` AND d.person_count <=> s.`jumlah_orang_dibagikan_posyandu`)
  UNION ALL
  SELECT 'data_tanaman_obat' AS tabel_lama, 'DIJUAL' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_tanaman_obat` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_tanaman_obat' AND ip.legacy_id = s.`id_tanaman_obat`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'DIJUAL'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dijual_kg` AND d.household_count <=> NULL AND d.person_count <=> s.`jumlah_orang_dijual`)
  UNION ALL
  SELECT 'data_ikan' AS tabel_lama, 'KP' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_ikan` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_ikan' AND ip.legacy_id = s.`id_ikan`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'KP'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_kp_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_kp_kk` AND d.person_count <=> s.`jumlah_orang_kp`)
  UNION ALL
  SELECT 'data_ikan' AS tabel_lama, 'STUNTING' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_ikan` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_ikan' AND ip.legacy_id = s.`id_ikan`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'STUNTING'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_stunting_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_stunting` AND d.person_count <=> s.`jumlah_orang_dibagikan_stunting`)
  UNION ALL
  SELECT 'data_ikan' AS tabel_lama, 'MM' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_ikan` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_ikan' AND ip.legacy_id = s.`id_ikan`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'MM'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_mm_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_mm` AND d.person_count <=> s.`jumlah_orang_dibagikan_mm`)
  UNION ALL
  SELECT 'data_ikan' AS tabel_lama, 'LANSIA' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_ikan` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_ikan' AND ip.legacy_id = s.`id_ikan`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'LANSIA'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_lansia_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_lansia` AND d.person_count <=> s.`jumlah_orang_dibagikan_lansia`)
  UNION ALL
  SELECT 'data_ikan' AS tabel_lama, 'POSYANDU' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_ikan` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_ikan' AND ip.legacy_id = s.`id_ikan`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'POSYANDU'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_posyandu_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_posyandu` AND d.person_count <=> s.`jumlah_orang_dibagikan_posyandu`)
  UNION ALL
  SELECT 'data_ikan' AS tabel_lama, 'DIJUAL' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_ikan` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_ikan' AND ip.legacy_id = s.`id_ikan`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'DIJUAL'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dijual_kg` AND d.household_count <=> NULL AND d.person_count <=> s.`jumlah_orang_dijual`)
  UNION ALL
  SELECT 'data_ternak' AS tabel_lama, 'KP' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_ternak` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_ternak' AND ip.legacy_id = s.`id_ternak`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'KP'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_kp_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_kp_kk` AND d.person_count <=> s.`jumlah_orang_kp`)
  UNION ALL
  SELECT 'data_ternak' AS tabel_lama, 'STUNTING' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_ternak` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_ternak' AND ip.legacy_id = s.`id_ternak`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'STUNTING'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_stunting_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_stunting` AND d.person_count <=> s.`jumlah_orang_dibagikan_stunting`)
  UNION ALL
  SELECT 'data_ternak' AS tabel_lama, 'MM' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_ternak` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_ternak' AND ip.legacy_id = s.`id_ternak`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'MM'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_mm_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_mm` AND d.person_count <=> s.`jumlah_orang_dibagikan_mm`)
  UNION ALL
  SELECT 'data_ternak' AS tabel_lama, 'LANSIA' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_ternak` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_ternak' AND ip.legacy_id = s.`id_ternak`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'LANSIA'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_lansia_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_lansia` AND d.person_count <=> s.`jumlah_orang_dibagikan_lansia`)
  UNION ALL
  SELECT 'data_ternak' AS tabel_lama, 'POSYANDU' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_ternak` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_ternak' AND ip.legacy_id = s.`id_ternak`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'POSYANDU'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_posyandu_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_posyandu` AND d.person_count <=> s.`jumlah_orang_dibagikan_posyandu`)
  UNION ALL
  SELECT 'data_ternak' AS tabel_lama, 'DIJUAL' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_ternak` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_ternak' AND ip.legacy_id = s.`id_ternak`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'DIJUAL'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dijual_kg` AND d.household_count <=> NULL AND d.person_count <=> s.`jumlah_orang_dijual`)
  UNION ALL
  SELECT 'data_olahan_hasil' AS tabel_lama, 'KP' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_olahan_hasil` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_olahan_hasil' AND ip.legacy_id = s.`id_data_olahan_hasil`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'KP'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_kp_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_kp_kk` AND d.person_count <=> s.`jumlah_orang_kp`)
  UNION ALL
  SELECT 'data_olahan_hasil' AS tabel_lama, 'STUNTING' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_olahan_hasil` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_olahan_hasil' AND ip.legacy_id = s.`id_data_olahan_hasil`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'STUNTING'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_stunting_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_stunting` AND d.person_count <=> s.`jumlah_orang_dibagikan_stunting`)
  UNION ALL
  SELECT 'data_olahan_hasil' AS tabel_lama, 'MM' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_olahan_hasil` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_olahan_hasil' AND ip.legacy_id = s.`id_data_olahan_hasil`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'MM'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_mm_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_mm` AND d.person_count <=> s.`jumlah_orang_dibagikan_mm`)
  UNION ALL
  SELECT 'data_olahan_hasil' AS tabel_lama, 'LANSIA' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_olahan_hasil` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_olahan_hasil' AND ip.legacy_id = s.`id_data_olahan_hasil`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'LANSIA'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_lansia_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_lansia` AND d.person_count <=> s.`jumlah_orang_dibagikan_lansia`)
  UNION ALL
  SELECT 'data_olahan_hasil' AS tabel_lama, 'POSYANDU' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_olahan_hasil` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_olahan_hasil' AND ip.legacy_id = s.`id_data_olahan_hasil`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'POSYANDU'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dibagikan_posyandu_kg` AND d.household_count <=> s.`jumlah_kepala_keluarga_dibagikan_posyandu` AND d.person_count <=> s.`jumlah_orang_dibagikan_posyandu`)
  UNION ALL
  SELECT 'data_olahan_hasil' AS tabel_lama, 'DIJUAL' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_olahan_hasil` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_olahan_hasil' AND ip.legacy_id = s.`id_data_olahan_hasil`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'DIJUAL'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_berat_dijual_kg` AND d.household_count <=> NULL AND d.person_count <=> s.`jumlah_orang_dijual`)
  UNION ALL
  SELECT 'data_bibit' AS tabel_lama, 'KP' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_bibit` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_bibit' AND ip.legacy_id = s.`id_bibit`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'KP'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_kp` AND d.household_count <=> NULL AND d.person_count <=> NULL)
  UNION ALL
  SELECT 'data_bibit' AS tabel_lama, 'MS' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_bibit` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_bibit' AND ip.legacy_id = s.`id_bibit`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'MS'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_ms` AND d.household_count <=> NULL AND d.person_count <=> NULL)
  UNION ALL
  SELECT 'data_bibit' AS tabel_lama, 'SEKOLAH' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_bibit` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_bibit' AND ip.legacy_id = s.`id_bibit`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'SEKOLAH'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_sekolah` AND d.household_count <=> NULL AND d.person_count <=> NULL)
  UNION ALL
  SELECT 'data_bibit' AS tabel_lama, 'PKK' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_bibit` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_bibit' AND ip.legacy_id = s.`id_bibit`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'PKK'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_pkk` AND d.household_count <=> NULL AND d.person_count <=> NULL)
  UNION ALL
  SELECT 'data_bibit' AS tabel_lama, 'POSYANDU' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_bibit` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_bibit' AND ip.legacy_id = s.`id_bibit`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'POSYANDU'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_posyandu` AND d.household_count <=> NULL AND d.person_count <=> NULL)
  UNION ALL
  SELECT 'data_bibit' AS tabel_lama, 'LAINNYA' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_bibit` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_bibit' AND ip.legacy_id = s.`id_bibit`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'LAINNYA'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_lainnya` AND d.household_count <=> NULL AND d.person_count <=> NULL)
  UNION ALL
  SELECT 'data_bibit' AS tabel_lama, 'DIBAGIKAN_REKAP' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_bibit` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_bibit' AND ip.legacy_id = s.`id_bibit`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'DIBAGIKAN_REKAP'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> NULL AND d.household_count <=> s.`jumlah_kk` AND d.person_count <=> s.`jumlah_orang`)
  UNION ALL
  SELECT 'data_bibit' AS tabel_lama, 'DIJUAL' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_bibit` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_bibit' AND ip.legacy_id = s.`id_bibit`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'DIJUAL'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_dijual_pohon` AND d.household_count <=> s.`jumlah_dijual_kk` AND d.person_count <=> s.`jumlah_dijual_orang`)
  UNION ALL
  SELECT 'data_sampah' AS tabel_lama, 'KP' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_sampah` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_sampah' AND ip.legacy_id = s.`id_data_sampah`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'KP'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_kp` AND d.household_count <=> NULL AND d.person_count <=> NULL)
  UNION ALL
  SELECT 'data_sampah' AS tabel_lama, 'MS' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_sampah` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_sampah' AND ip.legacy_id = s.`id_data_sampah`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'MS'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_ms` AND d.household_count <=> NULL AND d.person_count <=> NULL)
  UNION ALL
  SELECT 'data_sampah' AS tabel_lama, 'SEKOLAH' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_sampah` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_sampah' AND ip.legacy_id = s.`id_data_sampah`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'SEKOLAH'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_sekolah` AND d.household_count <=> NULL AND d.person_count <=> NULL)
  UNION ALL
  SELECT 'data_sampah' AS tabel_lama, 'PKK' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_sampah` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_sampah' AND ip.legacy_id = s.`id_data_sampah`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'PKK'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_pkk` AND d.household_count <=> NULL AND d.person_count <=> NULL)
  UNION ALL
  SELECT 'data_sampah' AS tabel_lama, 'POSYANDU' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_sampah` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_sampah' AND ip.legacy_id = s.`id_data_sampah`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'POSYANDU'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_posyandu` AND d.household_count <=> NULL AND d.person_count <=> NULL)
  UNION ALL
  SELECT 'data_sampah' AS tabel_lama, 'LAINNYA' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_sampah` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_sampah' AND ip.legacy_id = s.`id_data_sampah`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'LAINNYA'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_lainnya` AND d.household_count <=> NULL AND d.person_count <=> NULL)
  UNION ALL
  SELECT 'data_sampah' AS tabel_lama, 'DIBAGIKAN_REKAP' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_sampah` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_sampah' AND ip.legacy_id = s.`id_data_sampah`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'DIBAGIKAN_REKAP'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> NULL AND d.household_count <=> s.`jumlah_kk` AND d.person_count <=> s.`jumlah_orang`)
  UNION ALL
  SELECT 'data_sampah' AS tabel_lama, 'DIJUAL' AS kategori, COUNT(*) AS selisih
  FROM `buruansae_lama`.`data_sampah` s
  JOIN `_import_productions` ip ON ip.legacy_table = 'data_sampah' AND ip.legacy_id = s.`id_data_sampah`
  LEFT JOIN `recipient_categories` rc ON rc.code = 'DIJUAL'
  LEFT JOIN `distributions` d ON d.production_id = ip.new_id AND d.recipient_category_id = rc.id
  WHERE NOT (d.quantity <=> s.`jumlah_dijual_kg` AND d.household_count <=> s.`jumlah_dijual_kk` AND d.person_count <=> s.`jumlah_dijual_orang`)
) x WHERE selisih > 0;

--    (c) Master & rekap
SELECT 'districts' AS tabel, (SELECT COUNT(*) FROM `buruansae_lama`.`data_kecamatan`) AS lama, (SELECT COUNT(*) FROM `districts`) AS baru
UNION ALL SELECT 'villages', (SELECT COUNT(*) FROM `buruansae_lama`.`data_kelurahan`), (SELECT COUNT(*) FROM `villages`)
UNION ALL SELECT 'farmer_groups', (SELECT COUNT(*) FROM `buruansae_lama`.`data_kelompok`), (SELECT COUNT(*) FROM `farmer_groups`)
UNION ALL SELECT 'monthly_distribution_recaps', (SELECT COUNT(*) FROM `buruansae_lama`.`data_distribusi`), (SELECT COUNT(*) FROM `monthly_distribution_recaps`)
UNION ALL SELECT 'users (aktif)', (SELECT COUNT(*) FROM `buruansae_lama`.`users` WHERE deleted_at IS NULL), (SELECT COUNT(*) FROM `users`);

DROP TABLE `_import_productions`;
SET SESSION sql_mode = @OLD_SQL_MODE;
