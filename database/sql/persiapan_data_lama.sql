-- =============================================================================
--  PERSIAPAN DATA LAMA (CodeIgniter) sebelum import_data_lama.sql
--
--  Jalankan SEKALI di database `buruansae_lama`, yaitu SALINAN dump produksi
--  aplikasi lama, sebelum menjalankan import_data_lama.sql:
--      mysql -u root buruansae_lama < database/sql/persiapan_data_lama.sql
--
--  Script ini MENGUBAH isi database lama (membersihkan data uji, menyeragamkan
--  nama wilayah, dsb.). JANGAN jalankan di database produksi. Untuk mengulang:
--  drop buruansae_lama, import ulang dump, lalu jalankan script ini lagi.
--
--  Semua temuan dan keputusan di bawah berasal dari pemeriksaan dump produksi
--  23 September 2026. Hasil di akhir script harus menunjukkan:
--    - "kelompok_tanpa_kelurahan" dan "rekap_tanpa_kelurahan" = 0,
--    - daftar "nilai_dibulatkan" kosong,
--    - daftar "tanggal_janggal" untuk dibetulkan manual lewat halaman admin.
-- =============================================================================

SET NAMES utf8mb4;
SET @OLD_SQL_MODE := @@SESSION.sql_mode;
-- Tanpa NO_ZERO_DATE: tabel lama masih berisi tanggal 0000-00-00 yang harus
-- bisa dibaca (dan disalin oleh ALTER TABLE) sebelum dibersihkan.
SET SESSION sql_mode = 'STRICT_ALL_TABLES,NO_ENGINE_SUBSTITUTION,NO_AUTO_VALUE_ON_ZERO';


-- -----------------------------------------------------------------------------
-- 1. SERAGAMKAN CHARSET & COLLATION
--    Tabel lama campuran utf8mb3_general_ci, utf8mb4_general_ci, dan
--    utf8mb4_unicode_ci. Membandingkan kolom beda collation memicu error
--    "Illegal mix of collations", termasuk saat impor ke database Laravel
--    (utf8mb4_unicode_ci).
-- -----------------------------------------------------------------------------
ALTER TABLE `data_kecamatan`    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `data_kelurahan`    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `data_kelompok`     CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `data_komoditi`     CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `data_sayur`        CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `data_buah`         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `data_tanaman_obat` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `data_ikan`         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `data_ternak`       CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `data_olahan_hasil` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `data_bibit`        CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `data_sampah`       CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `data_distribusi`   CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `users`             CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- 2. HAPUS DATA UJI
--    Setiap DELETE juga memeriksa isinya, jadi aman bila dijalankan pada dump
--    yang lebih baru (baris asli dengan id sama tidak ikut terhapus).
-- -----------------------------------------------------------------------------
-- 2a. Kelompok uji 566 & 610 (nama "Tes") beserta seluruh datanya.
CREATE TEMPORARY TABLE `_kelompok_uji` (id INT UNSIGNED PRIMARY KEY)
SELECT id_kelompok AS id FROM `data_kelompok` WHERE id_kelompok IN (566, 610) AND nama_kelompok = 'Tes';

DELETE FROM `data_sayur`        WHERE id_kelompok IN (SELECT id FROM `_kelompok_uji`);
DELETE FROM `data_buah`         WHERE id_kelompok IN (SELECT id FROM `_kelompok_uji`);
DELETE FROM `data_tanaman_obat` WHERE id_kelompok IN (SELECT id FROM `_kelompok_uji`);
DELETE FROM `data_ikan`         WHERE id_kelompok IN (SELECT id FROM `_kelompok_uji`);
DELETE FROM `data_ternak`       WHERE id_kelompok IN (SELECT id FROM `_kelompok_uji`);
DELETE FROM `data_olahan_hasil` WHERE id_kelompok IN (SELECT id FROM `_kelompok_uji`);
DELETE FROM `data_bibit`        WHERE id_kelompok IN (SELECT id FROM `_kelompok_uji`);
DELETE FROM `data_sampah`       WHERE id_kelompok IN (SELECT id FROM `_kelompok_uji`);
DELETE FROM `data_kelompok`     WHERE id_kelompok IN (SELECT id FROM `_kelompok_uji`);

-- 2b. Baris uji yang tercatat atas nama kelompok asli.
--     Olahan hasil 19, 22, 23: merk/resep/izin berisi "Tes"/"tes".
DELETE FROM `data_olahan_hasil` WHERE id_data_olahan_hasil IN (19, 22, 23) AND merk = 'tes' AND resep = 'tes';
--     Bibit 40: komoditas "Tes Bibit".
DELETE FROM `data_bibit` WHERE id_bibit = 40 AND nama_sayur = 'Tes Bibit';
--     Sampah 1: semua angka bernilai 1.
DELETE FROM `data_sampah` WHERE id_data_sampah = 1 AND jumlah_sampah = 1 AND harga_jual = 1 AND jumlah_panen = 1;

-- 2c. Komoditas uji di master (tidak dipakai data mana pun setelah 2a–2b).
DELETE k FROM `data_komoditi` k
WHERE k.id IN (254, 257) AND k.nama_komoditi IN ('Tes', 'Tes Bibit')
  AND NOT EXISTS (SELECT 1 FROM `data_sayur` s WHERE s.nama_sayur = k.nama_komoditi)
  AND NOT EXISTS (SELECT 1 FROM `data_bibit` b WHERE b.nama_sayur = k.nama_komoditi);


-- -----------------------------------------------------------------------------
-- 3. PRODUKSI TANPA KELOMPOK
--    Baris yang id_kelompok-nya tidak ada di data_kelompok (kelompok 0, 69,
--    448 — sudah dihapus di aplikasi lama). Skema baru mewajibkan kelompok,
--    dan di aplikasi lama baris ini pun tidak pernah tampil per wilayah.
-- -----------------------------------------------------------------------------
SELECT 'data_sayur' AS tabel, id_kelompok, COUNT(*) AS baris_dihapus
FROM `data_sayur` p WHERE NOT EXISTS (SELECT 1 FROM `data_kelompok` g WHERE g.id_kelompok = p.id_kelompok)
GROUP BY id_kelompok;

DELETE p FROM `data_sayur`        p WHERE NOT EXISTS (SELECT 1 FROM `data_kelompok` g WHERE g.id_kelompok = p.id_kelompok);
DELETE p FROM `data_buah`         p WHERE NOT EXISTS (SELECT 1 FROM `data_kelompok` g WHERE g.id_kelompok = p.id_kelompok);
DELETE p FROM `data_tanaman_obat` p WHERE NOT EXISTS (SELECT 1 FROM `data_kelompok` g WHERE g.id_kelompok = p.id_kelompok);
DELETE p FROM `data_ikan`         p WHERE NOT EXISTS (SELECT 1 FROM `data_kelompok` g WHERE g.id_kelompok = p.id_kelompok);
DELETE p FROM `data_ternak`       p WHERE NOT EXISTS (SELECT 1 FROM `data_kelompok` g WHERE g.id_kelompok = p.id_kelompok);
DELETE p FROM `data_olahan_hasil` p WHERE NOT EXISTS (SELECT 1 FROM `data_kelompok` g WHERE g.id_kelompok = p.id_kelompok);
DELETE p FROM `data_bibit`        p WHERE NOT EXISTS (SELECT 1 FROM `data_kelompok` g WHERE g.id_kelompok = p.id_kelompok);
DELETE p FROM `data_sampah`       p WHERE NOT EXISTS (SELECT 1 FROM `data_kelompok` g WHERE g.id_kelompok = p.id_kelompok);


-- -----------------------------------------------------------------------------
-- 4. WILAYAH
--    Nama dicocokkan setelah dinormalkan: huruf besar, hanya A–Z (spasi, angka,
--    tanda baca, dan karakter BOM dibuang). "Pasir Kaliki" = "PASIRKALIKI",
--    "Huseinsastranegara 06" = "HUSEINSASTRANEGARA".
-- -----------------------------------------------------------------------------
-- 4a. Salah ketik nama kelurahan yang tidak tertangani normalisasi.
CREATE TEMPORARY TABLE `_alias_kelurahan` (
  `alias` VARCHAR(100) NOT NULL PRIMARY KEY,
  `norm`  VARCHAR(100) NOT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `_alias_kelurahan` VALUES
  ('CIJAWURA',   'CIJAURA'),     -- Buahbatu (kelompok & rekap)
  ('CIMINCRANG', 'CIMENCRANG'),  -- Gedebage (kelompok & rekap)
  ('HEGARNANAH', 'HEGARMANAH'),  -- Cidadap
  ('CIKADUNG',   'CIPADUNG');    -- Cibiru; tidak ada kelurahan "Cikadung" di Bandung

CREATE TEMPORARY TABLE `_kelurahan` (
  `id`   INT NOT NULL PRIMARY KEY,
  `norm` VARCHAR(255) NOT NULL UNIQUE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SELECT id, REGEXP_REPLACE(UPPER(name), '[^A-Z]', '') AS norm FROM `data_kelurahan`;

CREATE TEMPORARY TABLE `_kecamatan` (
  `id`   INT NOT NULL PRIMARY KEY,
  `norm` VARCHAR(255) NOT NULL UNIQUE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SELECT id, REGEXP_REPLACE(UPPER(name), '[^A-Z]', '') AS norm FROM `data_kecamatan`;

-- Pemetaan teks kelurahan (apa adanya) → id kelurahan, dari kelompok & rekap.
CREATE TEMPORARY TABLE `_teks_kelurahan` (
  `teks`         VARCHAR(255) NOT NULL PRIMARY KEY,
  `kelurahan_id` INT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SELECT t.teks, k.id AS kelurahan_id
FROM (SELECT DISTINCT kelurahan AS teks FROM `data_kelompok` WHERE kelurahan IS NOT NULL
      UNION SELECT DISTINCT kelurahan FROM `data_distribusi` WHERE kelurahan IS NOT NULL) t
LEFT JOIN `_alias_kelurahan` a ON a.alias = REGEXP_REPLACE(UPPER(t.teks), '[^A-Z]', '')
LEFT JOIN `_kelurahan` k ON k.norm = COALESCE(a.norm, REGEXP_REPLACE(UPPER(t.teks), '[^A-Z]', ''));

-- 4b. Relasi kelurahan → kecamatan (belum ada di skema lama). Diambil dari
--     rekap distribusi, yang memuat setiap kelurahan beserta kecamatannya;
--     kelurahan yang tidak ada di rekap diambil dari data kelompok. Hanya
--     dipakai bila satu kelurahan selalu tercatat di satu kecamatan yang sama.
ALTER TABLE `data_kelurahan` ADD COLUMN IF NOT EXISTS `id_kecamatan` INT NULL AFTER `id`;

UPDATE `data_kelurahan` kel
JOIN (
  SELECT t.kelurahan_id, MIN(kec.id) AS kecamatan_id
  FROM `data_distribusi` d
  JOIN `_teks_kelurahan` t ON t.teks = d.kelurahan
  JOIN `_kecamatan` kec ON kec.norm = REGEXP_REPLACE(UPPER(d.kecamatan), '[^A-Z]', '')
  GROUP BY t.kelurahan_id
  HAVING COUNT(DISTINCT kec.id) = 1
) m ON m.kelurahan_id = kel.id
SET kel.id_kecamatan = m.kecamatan_id;

UPDATE `data_kelurahan` kel
JOIN (
  SELECT t.kelurahan_id, MIN(kec.id) AS kecamatan_id
  FROM `data_kelompok` g
  JOIN `_teks_kelurahan` t ON t.teks = g.kelurahan
  JOIN `_kecamatan` kec ON kec.norm = REGEXP_REPLACE(UPPER(g.kecamatan), '[^A-Z]', '')
  GROUP BY t.kelurahan_id
  HAVING COUNT(DISTINCT kec.id) = 1
) m ON m.kelurahan_id = kel.id
SET kel.id_kecamatan = m.kecamatan_id
WHERE kel.id_kecamatan IS NULL;

-- Gagal dengan "Data truncated for column 'id_kecamatan'" bila masih ada
-- kelurahan yang kecamatannya tidak diketahui — isi manual, lalu ulangi.
ALTER TABLE `data_kelurahan` MODIFY `id_kecamatan` INT NOT NULL;

-- 4c. Tulis ulang teks kecamatan & kelurahan di kelompok dan rekap memakai nama
--     master. Kecamatan diambil dari kelurahannya, jadi salah ketik kecamatan
--     ("Cidadak", "Pagileukan", "Cibeunying", "Buah Batu") ikut beres.
UPDATE `data_kelompok` g
JOIN `_teks_kelurahan` t ON t.teks = g.kelurahan
JOIN `data_kelurahan` kel ON kel.id = t.kelurahan_id
JOIN `data_kecamatan` kec ON kec.id = kel.id_kecamatan
SET g.kelurahan = kel.name, g.kecamatan = kec.name;

UPDATE `data_distribusi` d
JOIN `_teks_kelurahan` t ON t.teks = d.kelurahan
JOIN `data_kelurahan` kel ON kel.id = t.kelurahan_id
JOIN `data_kecamatan` kec ON kec.id = kel.id_kecamatan
SET d.kelurahan = kel.name, d.kecamatan = kec.name;


-- -----------------------------------------------------------------------------
-- 5. KOLOM KELOMPOK
--    rw: skema baru berupa angka 1–255. "RW 05" → 5; "-", "0", "00" → NULL.
--    luas_lahan: skema baru berupa angka (m²). "-" → NULL.
-- -----------------------------------------------------------------------------
UPDATE `data_kelompok`
SET rw = NULLIF(CAST(NULLIF(REGEXP_REPLACE(rw, '[^0-9]', ''), '') AS UNSIGNED), 0)
WHERE rw IS NOT NULL;

UPDATE `data_kelompok` SET luas_lahan = NULL
WHERE luas_lahan IS NOT NULL AND TRIM(luas_lahan) NOT REGEXP '^[0-9]+(\\.[0-9]+)?$';


-- -----------------------------------------------------------------------------
-- 6. TANGGAL & KATEGORI
-- -----------------------------------------------------------------------------
-- 6a. Tanggal kosong tersimpan sebagai 0000-00-00 → NULL.
UPDATE `data_sayur`        SET tanggal_tanam = NULL         WHERE tanggal_tanam = '0000-00-00';
UPDATE `data_sayur`        SET waktu_prakiraan_panen = NULL WHERE waktu_prakiraan_panen = '0000-00-00';
UPDATE `data_sayur`        SET waktu_panen = NULL           WHERE waktu_panen = '0000-00-00';
UPDATE `data_buah`         SET tanggal_tanam = NULL         WHERE tanggal_tanam = '0000-00-00';
UPDATE `data_buah`         SET waktu_pupuk = NULL           WHERE waktu_pupuk = '0000-00-00';
UPDATE `data_buah`         SET waktu_prakiraan_panen = NULL WHERE waktu_prakiraan_panen = '0000-00-00';
UPDATE `data_buah`         SET waktu_panen = NULL           WHERE waktu_panen = '0000-00-00';
UPDATE `data_tanaman_obat` SET tanggal_tanam = NULL         WHERE tanggal_tanam = '0000-00-00';
UPDATE `data_tanaman_obat` SET waktu_prakiraan_panen = NULL WHERE waktu_prakiraan_panen = '0000-00-00';
UPDATE `data_tanaman_obat` SET waktu_panen = NULL           WHERE waktu_panen = '0000-00-00';
UPDATE `data_ikan`         SET waktu_prakiraan_panen = NULL WHERE waktu_prakiraan_panen = '0000-00-00';
UPDATE `data_ikan`         SET waktu_panen = NULL           WHERE waktu_panen = '0000-00-00';
UPDATE `data_ternak`       SET waktu_pakan = NULL           WHERE waktu_pakan = '0000-00-00';
UPDATE `data_ternak`       SET waktu_prakiraan_panen = NULL WHERE waktu_prakiraan_panen = '0000-00-00';
UPDATE `data_ternak`       SET waktu_panen = NULL           WHERE waktu_panen = '0000-00-00';
UPDATE `data_olahan_hasil` SET waktu_panen = NULL           WHERE waktu_panen = '0000-00-00';
UPDATE `data_bibit`        SET tanggal_tanam = NULL         WHERE tanggal_tanam = '0000-00-00';
UPDATE `data_bibit`        SET waktu_prakiraan_panen = NULL WHERE waktu_prakiraan_panen = '0000-00-00';
UPDATE `data_bibit`        SET waktu_panen = NULL           WHERE waktu_panen = '0000-00-00';
UPDATE `data_sampah`       SET tanggal_masuk = NULL         WHERE tanggal_masuk = '0000-00-00';
UPDATE `data_sampah`       SET waktu_prakiraan_panen = NULL WHERE waktu_prakiraan_panen = '0000-00-00';
UPDATE `data_sampah`       SET waktu_panen = NULL           WHERE waktu_panen = '0000-00-00';

-- 6b. data_ikan.waktu_pakan disimpan sebagai teks ("2024-12-01 00:00:00",
--     satu baris "1/13/2024"). Ubah ke DATE; teks yang tidak bisa dibaca
--     membuat ALTER gagal, bukan diam-diam hilang.
UPDATE `data_ikan`
SET waktu_pakan = DATE_FORMAT(STR_TO_DATE(waktu_pakan, '%c/%e/%Y'), '%Y-%m-%d')
WHERE waktu_pakan REGEXP '^[0-9]{1,2}/[0-9]{1,2}/[0-9]{4}$';
UPDATE `data_ikan` SET waktu_pakan = LEFT(TRIM(waktu_pakan), 10) WHERE waktu_pakan IS NOT NULL;
UPDATE `data_ikan` SET waktu_pakan = NULL WHERE waktu_pakan IN ('', '0000-00-00');
ALTER TABLE `data_ikan` MODIFY `waktu_pakan` DATE NULL;

-- 6c. Salah ketik kategori tanaman.
UPDATE `data_sayur`        SET kategori_tumbuhan = 'Benih' WHERE kategori_tumbuhan = 'BENIIH';
UPDATE `data_buah`         SET kategori_tumbuhan = 'Benih' WHERE kategori_tumbuhan = 'BENIIH';
UPDATE `data_tanaman_obat` SET kategori_tumbuhan = 'Benih' WHERE kategori_tumbuhan = 'BENIIH';
UPDATE `data_sayur`        SET kategori_tumbuhan = 'Bibit' WHERE kategori_tumbuhan = 'BIBT';
UPDATE `data_buah`         SET kategori_tumbuhan = 'Bibit' WHERE kategori_tumbuhan = 'BIBT';
UPDATE `data_tanaman_obat` SET kategori_tumbuhan = 'Bibit' WHERE kategori_tumbuhan = 'BIBT';


-- -----------------------------------------------------------------------------
-- 7. KOMODITAS: nama master diseragamkan huruf besar tanpa spasi berlebih,
--    sama seperti komoditas yang dibuat lewat halaman admin.
-- -----------------------------------------------------------------------------
UPDATE `data_komoditi` SET nama_komoditi = UPPER(TRIM(nama_komoditi));


-- -----------------------------------------------------------------------------
-- 8. KOLOM WAKTU
--    import_data_lama.sql menyalin created_at/updated_at, yang tidak ada di
--    tabel lama. Ditambahkan kosong (NULL).
-- -----------------------------------------------------------------------------
ALTER TABLE `data_kelompok`     ADD COLUMN IF NOT EXISTS `created_at` DATETIME NULL, ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NULL;
ALTER TABLE `data_sayur`        ADD COLUMN IF NOT EXISTS `created_at` DATETIME NULL, ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NULL;
ALTER TABLE `data_buah`         ADD COLUMN IF NOT EXISTS `created_at` DATETIME NULL, ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NULL;
ALTER TABLE `data_tanaman_obat` ADD COLUMN IF NOT EXISTS `created_at` DATETIME NULL, ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NULL;
ALTER TABLE `data_ikan`         ADD COLUMN IF NOT EXISTS `created_at` DATETIME NULL, ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NULL;
ALTER TABLE `data_ternak`       ADD COLUMN IF NOT EXISTS `created_at` DATETIME NULL, ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NULL;
ALTER TABLE `data_olahan_hasil` ADD COLUMN IF NOT EXISTS `created_at` DATETIME NULL, ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NULL;
ALTER TABLE `data_bibit`        ADD COLUMN IF NOT EXISTS `created_at` DATETIME NULL, ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NULL;
ALTER TABLE `data_sampah`       ADD COLUMN IF NOT EXISTS `created_at` DATETIME NULL, ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NULL;


-- -----------------------------------------------------------------------------
-- 9. HASIL
-- -----------------------------------------------------------------------------
-- Harus 0. Kalau tidak, tambahkan salah ketiknya di _alias_kelurahan (4a).
SELECT
  (SELECT COUNT(*) FROM `data_kelompok` g
    WHERE NOT EXISTS (SELECT 1 FROM `data_kelurahan` k JOIN `data_kecamatan` c ON c.id = k.id_kecamatan
                      WHERE k.name = g.kelurahan AND c.name = g.kecamatan)) AS kelompok_tanpa_kelurahan,
  (SELECT COUNT(*) FROM `data_distribusi` d
    WHERE NOT EXISTS (SELECT 1 FROM `data_kelurahan` k JOIN `data_kecamatan` c ON c.id = k.id_kecamatan
                      WHERE k.name = d.kelurahan AND c.name = d.kecamatan)) AS rekap_tanpa_kelurahan;

-- Tanggal yang tahunnya tidak masuk akal (salah ketik). Dibiarkan apa adanya
-- supaya tidak menebak; betulkan lewat menu admin setelah impor.
SELECT tabel, id, kolom, tanggal FROM (
            SELECT 'data_sayur' AS tabel, id_sayur AS id, 'tanggal_tanam' AS kolom, tanggal_tanam AS tanggal FROM `data_sayur`
  UNION ALL SELECT 'data_sayur', id_sayur, 'waktu_prakiraan_panen', waktu_prakiraan_panen FROM `data_sayur`
  UNION ALL SELECT 'data_sayur', id_sayur, 'waktu_panen', waktu_panen FROM `data_sayur`
  UNION ALL SELECT 'data_buah', id_buah, 'tanggal_tanam', tanggal_tanam FROM `data_buah`
  UNION ALL SELECT 'data_buah', id_buah, 'waktu_panen', waktu_panen FROM `data_buah`
  UNION ALL SELECT 'data_tanaman_obat', id_tanaman_obat, 'tanggal_tanam', tanggal_tanam FROM `data_tanaman_obat`
  UNION ALL SELECT 'data_tanaman_obat', id_tanaman_obat, 'waktu_panen', waktu_panen FROM `data_tanaman_obat`
  UNION ALL SELECT 'data_ikan', id_ikan, 'waktu_panen', waktu_panen FROM `data_ikan`
  UNION ALL SELECT 'data_ternak', id_ternak, 'waktu_panen', waktu_panen FROM `data_ternak`
) x
WHERE tanggal IS NOT NULL AND (YEAR(tanggal) < 2015 OR YEAR(tanggal) > YEAR(CURDATE()) + 1)
ORDER BY tabel, id, kolom;

-- Angka lama bertipe FLOAT/DOUBLE, skema baru DECIMAL (2 atau 3 desimal;
-- KK/orang bilangan bulat). Harus KOSONG: tidak ada nilai yang berubah karena
-- dibulatkan (selisih kecil bawaan FLOAT, mis. 1.2 = 1.2000000477, diabaikan).
SELECT tabel, nilai_dibulatkan FROM (
  SELECT 'data_sayur' AS tabel,
         IFNULL(SUM(ABS(`jumlah_tanam` - ROUND(`jumlah_tanam`, 2)) > 0.00001 * GREATEST(1, ABS(`jumlah_tanam`))), 0)
       + IFNULL(SUM(ABS(`prakiraan_jumlah_panen` - ROUND(`prakiraan_jumlah_panen`, 3)) > 0.00001 * GREATEST(1, ABS(`prakiraan_jumlah_panen`))), 0)
       + IFNULL(SUM(ABS(`jumlah_panen` - ROUND(`jumlah_panen`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_panen`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_kp_kg` - ROUND(`jumlah_berat_kp_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_kp_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_stunting_kg` - ROUND(`jumlah_berat_dibagikan_stunting_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_stunting_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_mm_kg` - ROUND(`jumlah_berat_dibagikan_mm_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_mm_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_lansia_kg` - ROUND(`jumlah_berat_dibagikan_lansia_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_lansia_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_posyandu_kg` - ROUND(`jumlah_berat_dibagikan_posyandu_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_posyandu_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dijual_kg` - ROUND(`jumlah_berat_dijual_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dijual_kg`))), 0) AS nilai_dibulatkan
  FROM `data_sayur`
  UNION ALL
  SELECT 'data_buah' AS tabel,
         IFNULL(SUM(ABS(`jumlah_tanam` - ROUND(`jumlah_tanam`, 2)) > 0.00001 * GREATEST(1, ABS(`jumlah_tanam`))), 0)
       + IFNULL(SUM(ABS(`prakiraan_jumlah_panen` - ROUND(`prakiraan_jumlah_panen`, 3)) > 0.00001 * GREATEST(1, ABS(`prakiraan_jumlah_panen`))), 0)
       + IFNULL(SUM(ABS(`jumlah_panen` - ROUND(`jumlah_panen`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_panen`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_kp_kg` - ROUND(`jumlah_berat_kp_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_kp_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_stunting_kg` - ROUND(`jumlah_berat_dibagikan_stunting_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_stunting_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_mm_kg` - ROUND(`jumlah_berat_dibagikan_mm_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_mm_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_lansia_kg` - ROUND(`jumlah_berat_dibagikan_lansia_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_lansia_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_posyandu_kg` - ROUND(`jumlah_berat_dibagikan_posyandu_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_posyandu_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dijual_kg` - ROUND(`jumlah_berat_dijual_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dijual_kg`))), 0) AS nilai_dibulatkan
  FROM `data_buah`
  UNION ALL
  SELECT 'data_tanaman_obat' AS tabel,
         IFNULL(SUM(ABS(`jumlah_tanam` - ROUND(`jumlah_tanam`, 2)) > 0.00001 * GREATEST(1, ABS(`jumlah_tanam`))), 0)
       + IFNULL(SUM(ABS(`prakiraan_jumlah_panen` - ROUND(`prakiraan_jumlah_panen`, 3)) > 0.00001 * GREATEST(1, ABS(`prakiraan_jumlah_panen`))), 0)
       + IFNULL(SUM(ABS(`jumlah_panen` - ROUND(`jumlah_panen`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_panen`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_kp_kg` - ROUND(`jumlah_berat_kp_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_kp_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_stunting_kg` - ROUND(`jumlah_berat_dibagikan_stunting_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_stunting_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_mm_kg` - ROUND(`jumlah_berat_dibagikan_mm_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_mm_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_lansia_kg` - ROUND(`jumlah_berat_dibagikan_lansia_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_lansia_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_posyandu_kg` - ROUND(`jumlah_berat_dibagikan_posyandu_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_posyandu_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dijual_kg` - ROUND(`jumlah_berat_dijual_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dijual_kg`))), 0) AS nilai_dibulatkan
  FROM `data_tanaman_obat`
  UNION ALL
  SELECT 'data_ikan' AS tabel,
         IFNULL(SUM(ABS(`jumlah_ikan` - ROUND(`jumlah_ikan`, 2)) > 0.00001 * GREATEST(1, ABS(`jumlah_ikan`))), 0)
       + IFNULL(SUM(ABS(`prakiraan_jumlah_panen` - ROUND(`prakiraan_jumlah_panen`, 3)) > 0.00001 * GREATEST(1, ABS(`prakiraan_jumlah_panen`))), 0)
       + IFNULL(SUM(ABS(`jumlah_panen_kg` - ROUND(`jumlah_panen_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_panen_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_panen_ekor` - ROUND(`jumlah_panen_ekor`, 2)) > 0.00001 * GREATEST(1, ABS(`jumlah_panen_ekor`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_kp_kg` - ROUND(`jumlah_berat_kp_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_kp_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_stunting_kg` - ROUND(`jumlah_berat_dibagikan_stunting_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_stunting_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_mm_kg` - ROUND(`jumlah_berat_dibagikan_mm_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_mm_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_lansia_kg` - ROUND(`jumlah_berat_dibagikan_lansia_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_lansia_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_posyandu_kg` - ROUND(`jumlah_berat_dibagikan_posyandu_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_posyandu_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dijual_kg` - ROUND(`jumlah_berat_dijual_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dijual_kg`))), 0) AS nilai_dibulatkan
  FROM `data_ikan`
  UNION ALL
  SELECT 'data_ternak' AS tabel,
         IFNULL(SUM(ABS(`jumlah_ternak` - ROUND(`jumlah_ternak`, 2)) > 0.00001 * GREATEST(1, ABS(`jumlah_ternak`))), 0)
       + IFNULL(SUM(ABS(`prakiraan_jumlah_panen` - ROUND(`prakiraan_jumlah_panen`, 3)) > 0.00001 * GREATEST(1, ABS(`prakiraan_jumlah_panen`))), 0)
       + IFNULL(SUM(ABS(`jumlah_panen_kg` - ROUND(`jumlah_panen_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_panen_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_panen_ekor` - ROUND(`jumlah_panen_ekor`, 2)) > 0.00001 * GREATEST(1, ABS(`jumlah_panen_ekor`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_kp_kg` - ROUND(`jumlah_berat_kp_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_kp_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_stunting_kg` - ROUND(`jumlah_berat_dibagikan_stunting_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_stunting_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_mm_kg` - ROUND(`jumlah_berat_dibagikan_mm_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_mm_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_lansia_kg` - ROUND(`jumlah_berat_dibagikan_lansia_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_lansia_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_posyandu_kg` - ROUND(`jumlah_berat_dibagikan_posyandu_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_posyandu_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dijual_kg` - ROUND(`jumlah_berat_dijual_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dijual_kg`))), 0) AS nilai_dibulatkan
  FROM `data_ternak`
  UNION ALL
  SELECT 'data_olahan_hasil' AS tabel,
         IFNULL(SUM(ABS(`jumlah_panen` - ROUND(`jumlah_panen`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_panen`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_kp_kg` - ROUND(`jumlah_berat_kp_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_kp_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_stunting_kg` - ROUND(`jumlah_berat_dibagikan_stunting_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_stunting_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_mm_kg` - ROUND(`jumlah_berat_dibagikan_mm_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_mm_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_lansia_kg` - ROUND(`jumlah_berat_dibagikan_lansia_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_lansia_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dibagikan_posyandu_kg` - ROUND(`jumlah_berat_dibagikan_posyandu_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dibagikan_posyandu_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_berat_dijual_kg` - ROUND(`jumlah_berat_dijual_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_berat_dijual_kg`))), 0) AS nilai_dibulatkan
  FROM `data_olahan_hasil`
  UNION ALL
  SELECT 'data_bibit' AS tabel,
         IFNULL(SUM(ABS(`jumlah_semai` - ROUND(`jumlah_semai`, 2)) > 0.00001 * GREATEST(1, ABS(`jumlah_semai`))), 0)
       + IFNULL(SUM(ABS(`prakiraan_jumlah_panen` - ROUND(`prakiraan_jumlah_panen`, 3)) > 0.00001 * GREATEST(1, ABS(`prakiraan_jumlah_panen`))), 0)
       + IFNULL(SUM(ABS(`jumlah_panen` - ROUND(`jumlah_panen`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_panen`))), 0)
       + IFNULL(SUM(ABS(`jumlah_kp` - ROUND(`jumlah_kp`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_kp`))), 0)
       + IFNULL(SUM(ABS(`jumlah_ms` - ROUND(`jumlah_ms`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_ms`))), 0)
       + IFNULL(SUM(ABS(`jumlah_sekolah` - ROUND(`jumlah_sekolah`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_sekolah`))), 0)
       + IFNULL(SUM(ABS(`jumlah_pkk` - ROUND(`jumlah_pkk`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_pkk`))), 0)
       + IFNULL(SUM(ABS(`jumlah_posyandu` - ROUND(`jumlah_posyandu`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_posyandu`))), 0)
       + IFNULL(SUM(ABS(`jumlah_lainnya` - ROUND(`jumlah_lainnya`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_lainnya`))), 0)
       + IFNULL(SUM(ABS(`jumlah_kk` - ROUND(`jumlah_kk`, 0)) > 0.00001 * GREATEST(1, ABS(`jumlah_kk`))), 0)
       + IFNULL(SUM(ABS(`jumlah_orang` - ROUND(`jumlah_orang`, 0)) > 0.00001 * GREATEST(1, ABS(`jumlah_orang`))), 0)
       + IFNULL(SUM(ABS(`jumlah_dijual_pohon` - ROUND(`jumlah_dijual_pohon`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_dijual_pohon`))), 0)
       + IFNULL(SUM(ABS(`jumlah_dijual_orang` - ROUND(`jumlah_dijual_orang`, 0)) > 0.00001 * GREATEST(1, ABS(`jumlah_dijual_orang`))), 0)
       + IFNULL(SUM(ABS(`jumlah_dijual_kk` - ROUND(`jumlah_dijual_kk`, 0)) > 0.00001 * GREATEST(1, ABS(`jumlah_dijual_kk`))), 0) AS nilai_dibulatkan
  FROM `data_bibit`
  UNION ALL
  SELECT 'data_sampah' AS tabel,
         IFNULL(SUM(ABS(`jumlah_sampah` - ROUND(`jumlah_sampah`, 2)) > 0.00001 * GREATEST(1, ABS(`jumlah_sampah`))), 0)
       + IFNULL(SUM(ABS(`prakiraan_jumlah_panen` - ROUND(`prakiraan_jumlah_panen`, 3)) > 0.00001 * GREATEST(1, ABS(`prakiraan_jumlah_panen`))), 0)
       + IFNULL(SUM(ABS(`jumlah_panen` - ROUND(`jumlah_panen`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_panen`))), 0)
       + IFNULL(SUM(ABS(`jumlah_kp` - ROUND(`jumlah_kp`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_kp`))), 0)
       + IFNULL(SUM(ABS(`jumlah_ms` - ROUND(`jumlah_ms`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_ms`))), 0)
       + IFNULL(SUM(ABS(`jumlah_sekolah` - ROUND(`jumlah_sekolah`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_sekolah`))), 0)
       + IFNULL(SUM(ABS(`jumlah_pkk` - ROUND(`jumlah_pkk`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_pkk`))), 0)
       + IFNULL(SUM(ABS(`jumlah_posyandu` - ROUND(`jumlah_posyandu`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_posyandu`))), 0)
       + IFNULL(SUM(ABS(`jumlah_lainnya` - ROUND(`jumlah_lainnya`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_lainnya`))), 0)
       + IFNULL(SUM(ABS(`jumlah_kk` - ROUND(`jumlah_kk`, 0)) > 0.00001 * GREATEST(1, ABS(`jumlah_kk`))), 0)
       + IFNULL(SUM(ABS(`jumlah_orang` - ROUND(`jumlah_orang`, 0)) > 0.00001 * GREATEST(1, ABS(`jumlah_orang`))), 0)
       + IFNULL(SUM(ABS(`jumlah_dijual_kg` - ROUND(`jumlah_dijual_kg`, 3)) > 0.00001 * GREATEST(1, ABS(`jumlah_dijual_kg`))), 0)
       + IFNULL(SUM(ABS(`jumlah_dijual_orang` - ROUND(`jumlah_dijual_orang`, 0)) > 0.00001 * GREATEST(1, ABS(`jumlah_dijual_orang`))), 0)
       + IFNULL(SUM(ABS(`jumlah_dijual_kk` - ROUND(`jumlah_dijual_kk`, 0)) > 0.00001 * GREATEST(1, ABS(`jumlah_dijual_kk`))), 0) AS nilai_dibulatkan
  FROM `data_sampah`
  UNION ALL
  SELECT 'data_distribusi' AS tabel,
         IFNULL(SUM(ABS(`panen` - ROUND(`panen`, 3)) > 0.00001 * GREATEST(1, ABS(`panen`))), 0)
       + IFNULL(SUM(ABS(`konsumsi_sendiri` - ROUND(`konsumsi_sendiri`, 3)) > 0.00001 * GREATEST(1, ABS(`konsumsi_sendiri`))), 0)
       + IFNULL(SUM(ABS(`dibagikan` - ROUND(`dibagikan`, 3)) > 0.00001 * GREATEST(1, ABS(`dibagikan`))), 0)
       + IFNULL(SUM(ABS(`dijual` - ROUND(`dijual`, 3)) > 0.00001 * GREATEST(1, ABS(`dijual`))), 0) AS nilai_dibulatkan
  FROM `data_distribusi`
) x WHERE nilai_dibulatkan > 0;

SELECT 'data_kelompok' AS tabel, COUNT(*) AS baris FROM `data_kelompok`
UNION ALL SELECT 'data_komoditi', COUNT(*) FROM `data_komoditi`
UNION ALL SELECT 'data_sayur', COUNT(*) FROM `data_sayur`
UNION ALL SELECT 'data_buah', COUNT(*) FROM `data_buah`
UNION ALL SELECT 'data_tanaman_obat', COUNT(*) FROM `data_tanaman_obat`
UNION ALL SELECT 'data_ikan', COUNT(*) FROM `data_ikan`
UNION ALL SELECT 'data_ternak', COUNT(*) FROM `data_ternak`
UNION ALL SELECT 'data_olahan_hasil', COUNT(*) FROM `data_olahan_hasil`
UNION ALL SELECT 'data_bibit', COUNT(*) FROM `data_bibit`
UNION ALL SELECT 'data_sampah', COUNT(*) FROM `data_sampah`
UNION ALL SELECT 'data_distribusi', COUNT(*) FROM `data_distribusi`;

SET SESSION sql_mode = @OLD_SQL_MODE;
