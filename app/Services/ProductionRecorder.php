<?php

namespace App\Services;

use App\Enums\InputType;
use App\Enums\SectorType;
use App\Models\Commodity;
use App\Models\Production;
use App\Models\RecipientCategory;
use App\Support\ImageStore;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Pencatatan siklus produksi dari dashboard pengelola (tanam → panen).
 *
 * Alur sama dengan aplikasi CodeIgniter lama: data tanam dicatat dulu, lalu
 * "data panen" diisi terpisah. Jumlah panen = total seluruh penyaluran
 * (konsumsi pribadi + dibagikan + dijual), sesuai perubahan terakhir di
 * aplikasi lama.
 */
class ProductionRecorder
{
    /** @param  array<string, mixed>  $data  hasil ProductionRequest::validated() */
    public function create(SectorType $sector, array $data): Production
    {
        return DB::transaction(function () use ($sector, $data) {
            $production = Production::create($this->productionAttributes($sector, $data));
            $this->syncSectorDetails($sector, $production, $data);

            return $production;
        });
    }

    /** @param  array<string, mixed>  $data  hasil ProductionRequest::validated() */
    public function update(SectorType $sector, Production $production, array $data): Production
    {
        return DB::transaction(function () use ($sector, $production, $data) {
            $production->update($this->productionAttributes($sector, $data));
            $this->syncSectorDetails($sector, $production, $data);

            return $production;
        });
    }

    /** @param  array<string, mixed>  $data  hasil HarvestRequest::validated() */
    public function recordHarvest(SectorType $sector, Production $production, array $data, ?UploadedFile $photo): Production
    {
        $newImage = $photo ? ImageStore::store($photo, Production::IMAGE_DIRECTORY) : null;
        $oldImage = $production->image;

        try {
            DB::transaction(function () use ($sector, $production, $data, $newImage) {
                $this->syncDistributions($sector, $production, $data['distributions'] ?? []);

                if ($sector->tracksFertilizer()) {
                    $this->syncInput($production, InputType::Fertilizer, $data['fertilizer'] ?? []);
                }

                $production->fill([
                    'harvest_date' => $data['harvest_date'],
                    'harvest_quantity' => round((float) $production->distributions()->sum('quantity'), 3),
                    'harvest_head_count' => $sector->tracksHeadCount() ? $data['harvest_head_count'] : $production->harvest_head_count,
                    'selling_price' => $data['selling_price'] ?? null,
                ]);

                if ($newImage) {
                    $production->image = $newImage;
                }

                $production->save();
            });
        } catch (\Throwable $e) {
            ImageStore::delete($newImage, Production::IMAGE_DIRECTORY);

            throw $e;
        }

        if ($newImage && $oldImage && $oldImage !== $newImage) {
            ImageStore::delete($oldImage, Production::IMAGE_DIRECTORY);
        }

        return $production;
    }

    public function delete(Production $production): void
    {
        $image = $production->image;

        // distributions, production_inputs, dan detail ikut terhapus (cascadeOnDelete).
        $production->delete();

        ImageStore::delete($image, Production::IMAGE_DIRECTORY);
    }

    /** Perkiraan tanggal panen = tanggal tanam + durasi tanam komoditas. */
    public static function estimateHarvestDate(?string $startDate, ?int $growingDays): ?string
    {
        if (! $startDate || ! $growingDays) {
            return null;
        }

        return Carbon::parse($startDate)->addDays($growingDays)->toDateString();
    }

    private function productionAttributes(SectorType $sector, array $data): array
    {
        $attributes = [
            'farmer_group_id' => $data['farmer_group_id'],
            'commodity_id' => $data['commodity_id'],
            'planting_category' => $sector->plantingCategories() ? $data['planting_category'] : null,
            'start_date' => $data['start_date'],
            'initial_quantity' => $sector->initialQuantityLabel() ? $data['initial_quantity'] : null,
            'notes' => $data['notes'] ?? null,
        ];

        if ($sector->tracksHarvestEstimate()) {
            $growingDays = Commodity::query()->whereKey($data['commodity_id'])->value('growing_days');

            $attributes['estimated_harvest_quantity'] = $data['estimated_harvest_quantity'];
            $attributes['estimated_harvest_date'] = $data['estimated_harvest_date']
                ?? self::estimateHarvestDate($data['start_date'], $growingDays);
        }

        return $attributes;
    }

    private function syncSectorDetails(SectorType $sector, Production $production, array $data): void
    {
        if ($sector === SectorType::ProcessedProduct) {
            $production->processedProductDetail()->updateOrCreate([], [
                'base_ingredient' => $data['base_ingredient'] ?? null,
                'brand' => $data['brand'] ?? null,
                'recipe' => $data['recipe'] ?? null,
                'pirt_permit' => $data['pirt_permit'] ?? null,
                'halal_permit' => $data['halal_permit'] ?? null,
                'lab_test' => $data['lab_test'] ?? null,
            ]);
        }

        if ($sector === SectorType::Nursery) {
            $production->seedlingDetail()->updateOrCreate([], ['origin' => $data['origin'] ?? null]);
        }

        if ($sector->tracksFeed()) {
            $this->syncInput($production, InputType::Feed, $data['feed'] ?? []);
        }
    }

    /**
     * Satu catatan pakan/pupuk per siklus (seperti form lama). Dihapus bila
     * semua isian dikosongkan.
     *
     * @param  array{name?: ?string, quantity?: ?string, applied_date?: ?string}  $values
     */
    private function syncInput(Production $production, InputType $type, array $values): void
    {
        $values = array_intersect_key($values, array_flip(['name', 'quantity', 'applied_date']));
        $existing = $production->inputs()->where('type', $type)->orderBy('id')->first();

        if (array_filter($values, fn ($v) => $v !== null && $v !== '') === []) {
            $existing?->delete();

            return;
        }

        $existing
            ? $existing->update($values)
            : $production->inputs()->create($values + ['type' => $type]);
    }

    /**
     * Simpan penyaluran per kategori. Kategori yang tidak ada di form sektor ini
     * (mis. rekap data lama) dibiarkan apa adanya; kategori form yang
     * dikosongkan dihapus.
     *
     * @param  array<string, array{quantity?: ?string, household_count?: ?string, person_count?: ?string}>  $rows
     */
    private function syncDistributions(SectorType $sector, Production $production, array $rows): void
    {
        $categories = RecipientCategory::query()
            ->whereIn('code', $sector->recipientCategoryCodes())
            ->pluck('id', 'code');

        foreach ($categories as $code => $categoryId) {
            $row = $rows[$code] ?? [];
            $values = [
                'quantity' => self::nullableNumber($row['quantity'] ?? null),
                'household_count' => self::nullableNumber($row['household_count'] ?? null),
                'person_count' => self::nullableNumber($row['person_count'] ?? null),
            ];

            if (array_filter($values, fn ($v) => $v !== null && (float) $v > 0) === []) {
                $production->distributions()->where('recipient_category_id', $categoryId)->delete();

                continue;
            }

            $production->distributions()->updateOrCreate(['recipient_category_id' => $categoryId], $values);
        }
    }

    private static function nullableNumber(mixed $value): ?string
    {
        return $value === null || $value === '' ? null : (string) $value;
    }
}
