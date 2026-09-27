<?php

namespace App\Http\Requests\Admin;

use App\Enums\SectorType;
use App\Models\Commodity;
use App\Models\Sector;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** Data tanam / produksi (form "Tambah data" & "Ubah data" per sektor). */
class ProductionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function sectorType(): SectorType
    {
        return $this->route('sector');
    }

    public function rules(): array
    {
        $sector = $this->sectorType();
        $sectorId = Sector::query()->where('code', $sector->code())->value('id');
        $categories = array_map(fn ($c) => $c->value, $sector->plantingCategories());

        return [
            'farmer_group_id' => ['required', 'integer', Rule::exists('farmer_groups', 'id')->whereNull('deleted_at')],
            'commodity_id' => ['required', 'integer', Rule::exists('commodities', 'id')->where('sector_id', $sectorId)],
            'planting_category' => [Rule::requiredIf($categories !== []), 'nullable', Rule::in($categories)],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'initial_quantity' => [Rule::requiredIf($sector->initialQuantityLabel() !== null), 'nullable', 'numeric', 'min:0', 'max:9999999999'],
            'estimated_harvest_quantity' => [Rule::requiredIf($sector->tracksHarvestEstimate()), 'nullable', 'numeric', 'min:0', 'max:999999999'],
            'estimated_harvest_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string', 'max:255'],

            // Olahan hasil
            'base_ingredient' => [Rule::requiredIf($sector === SectorType::ProcessedProduct), 'nullable', 'string', 'max:255'],
            'brand' => [Rule::requiredIf($sector === SectorType::ProcessedProduct), 'nullable', 'string', 'max:150'],
            'recipe' => ['nullable', 'string', 'max:5000'],
            'pirt_permit' => ['nullable', 'string', 'max:255'],
            'halal_permit' => ['nullable', 'string', 'max:255'],
            'lab_test' => ['nullable', 'string', 'max:255'],

            // Pembibitan
            'origin' => ['nullable', 'string', 'max:100'],

            // Pakan (ikan, ternak)
            'feed' => ['nullable', 'array'],
            'feed.name' => ['nullable', 'string', 'max:150'],
            'feed.quantity' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'feed.applied_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function attributes(): array
    {
        $sector = $this->sectorType();

        return [
            'farmer_group_id' => 'kelompok',
            'commodity_id' => strtolower($sector->commodityLabel()),
            'planting_category' => 'kategori tanam',
            'start_date' => strtolower($sector->startDateLabel()),
            'initial_quantity' => strtolower((string) $sector->initialQuantityLabel()),
            'estimated_harvest_quantity' => 'perkiraan jumlah panen',
            'estimated_harvest_date' => 'perkiraan tanggal panen',
            'notes' => 'keterangan',
            'base_ingredient' => 'bahan dasar',
            'brand' => 'merek',
            'recipe' => 'resep',
            'pirt_permit' => 'izin PIRT',
            'halal_permit' => 'sertifikat halal',
            'lab_test' => 'hasil uji lab',
            'origin' => 'asal bibit',
            'feed.name' => 'jenis pakan',
            'feed.quantity' => 'jumlah pakan',
            'feed.applied_date' => 'tanggal pakan',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $this->sectorType()->tracksHarvestEstimate() || $this->filled('estimated_harvest_date') || $validator->errors()->has('commodity_id')) {
                    return;
                }

                $growingDays = Commodity::query()->whereKey($this->integer('commodity_id'))->value('growing_days');

                if (! $growingDays) {
                    $validator->errors()->add('estimated_harvest_date', 'Komoditas ini belum punya durasi tanam, jadi perkiraan tanggal panen wajib diisi.');
                }
            },
        ];
    }
}
