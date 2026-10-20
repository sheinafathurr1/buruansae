<?php

namespace App\Enums;

/** Jenis aktivitas pencatatan di dashboard (tabel activity_logs). */
enum ActivityAction: string
{
    case PlantingCreated = 'planting_created';
    case PlantingUpdated = 'planting_updated';
    case HarvestRecorded = 'harvest_recorded';
    case HarvestUpdated = 'harvest_updated';
    case ProductionDeleted = 'production_deleted';

    /** Olahan hasil memakai istilah "produksi", sektor lain "panen". */
    public function label(?SectorType $sector = null): string
    {
        $harvest = strtolower(($sector ?? SectorType::Vegetable)->harvestTerm());

        return match ($this) {
            self::PlantingCreated => 'Catat data tanam',
            self::PlantingUpdated => 'Ubah data tanam',
            self::HarvestRecorded => 'Catat '.$harvest,
            self::HarvestUpdated => 'Ubah '.$harvest,
            self::ProductionDeleted => 'Hapus data',
        };
    }

    /** Label filter (tanpa sektor). */
    public function filterLabel(): string
    {
        return match ($this) {
            self::HarvestRecorded => 'Catat panen / produksi',
            self::HarvestUpdated => 'Ubah panen / produksi',
            default => $this->label(),
        };
    }

    /** neutral | info | success | critical (warna label di tampilan). */
    public function tone(): string
    {
        return match ($this) {
            self::PlantingCreated => 'info',
            self::HarvestRecorded => 'success',
            self::ProductionDeleted => 'critical',
            default => 'neutral',
        };
    }
}
