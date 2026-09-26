<?php

namespace App\Enums;

/**
 * Sektor yang punya halaman dashboard publik.
 *
 * Nilai enum = slug URL (dipertahankan dari aplikasi lama, mis. /vegetable),
 * sedangkan code() = sectors.code di database.
 */
enum SectorType: string
{
    case Vegetable = 'vegetable';
    case MedicinalPlant = 'medicalplant';
    case Fruit = 'fruit';
    case Livestock = 'livestock';
    case Fish = 'fish';
    case ProcessedProduct = 'processed-products';
    case WasteProcessing = 'waste-processing';
    case Nursery = 'nursery';

    public static function fromCode(string $code): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->code() === $code) {
                return $case;
            }
        }

        return null;
    }

    public function code(): string
    {
        return match ($this) {
            self::Vegetable => 'SAYUR',
            self::MedicinalPlant => 'TANAMAN_OBAT',
            self::Fruit => 'BUAH',
            self::Livestock => 'TERNAK',
            self::Fish => 'IKAN',
            self::ProcessedProduct => 'OLAHAN_HASIL',
            self::WasteProcessing => 'OLAHAN_SAMPAH',
            self::Nursery => 'BIBIT',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Vegetable => 'Sayur-sayuran',
            self::MedicinalPlant => 'Tanaman Obat',
            self::Fruit => 'Buah',
            self::Livestock => 'Ternak',
            self::Fish => 'Ikan',
            self::ProcessedProduct => 'Olahan Hasil',
            self::WasteProcessing => 'Pengolahan Sampah',
            self::Nursery => 'Pembibitan',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Vegetable => 'Sayuran daun dan buah seperti kangkung, bayam, sawi, dan cabai dari pekarangan warga.',
            self::MedicinalPlant => 'Tanaman obat keluarga (TOGA) seperti jahe, kunyit, dan sereh.',
            self::Fruit => 'Buah-buahan yang ditanam di pekarangan dan lahan kelompok.',
            self::Livestock => 'Ternak kecil seperti ayam dan domba yang dipelihara kelompok.',
            self::Fish => 'Budidaya ikan seperti lele dan nila di kolam maupun ember.',
            self::ProcessedProduct => 'Produk olahan pangan buatan kelompok beserta informasi perizinannya.',
            self::WasteProcessing => 'Pengolahan sampah organik menjadi kompos, maggot, dan eco-enzyme.',
            self::Nursery => 'Penyemaian bibit tanaman untuk dibagikan dan dijual kepada warga.',
        };
    }

    public function image(): string
    {
        return 'images/sectors/'.$this->value.'.png';
    }

    /** Istilah untuk hasil akhir siklus: "Panen" atau "Produksi". */
    public function harvestTerm(): string
    {
        return $this === self::ProcessedProduct ? 'Produksi' : 'Panen';
    }

    public function startDateLabel(): string
    {
        return match ($this) {
            self::Fish => 'Tanggal tebar',
            self::Livestock => 'Tanggal mulai',
            self::ProcessedProduct => 'Tanggal produksi',
            self::WasteProcessing => 'Tanggal masuk',
            self::Nursery => 'Tanggal semai',
            default => 'Tanggal tanam',
        };
    }

    /** Satuan productions.initial_quantity (jumlah tanam / ikan / ternak / semai / sampah). */
    public function initialQuantityUnit(): ?string
    {
        return match ($this) {
            self::Vegetable, self::MedicinalPlant, self::Fruit => 'tanaman',
            self::Livestock, self::Fish => 'ekor',
            self::Nursery => 'benih',
            self::WasteProcessing => 'kg sampah',
            self::ProcessedProduct => null,
        };
    }

    /** Sektor yang mencatat perkiraan tanggal & jumlah panen. */
    public function tracksHarvestEstimate(): bool
    {
        return $this !== self::ProcessedProduct;
    }

    /** Sektor yang mencatat jumlah ekor saat panen. */
    public function tracksHeadCount(): bool
    {
        return in_array($this, [self::Fish, self::Livestock], true);
    }
}
