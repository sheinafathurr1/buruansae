<?php

namespace App\Http\Requests;

use App\Enums\SectorType;
use App\Models\Sector;
use App\Support\DashboardFilters;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Rule;

/**
 * Filter dashboard sektor (query string GET).
 *
 * Berbeda dari form POST, filter yang tidak valid TIDAK memicu redirect
 * (redirect "back" pada GET bisa berputar ke URL yang sama). Field yang
 * salah diabaikan, sisanya tetap dipakai, dan pesan galat ditampilkan di form.
 */
class SectorDashboardRequest extends FormRequest
{
    private ?Sector $sectorModel = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'commodity' => [
                'nullable', 'integer',
                Rule::exists('commodities', 'id')->where('sector_id', $this->sector()->id),
            ],
            'district' => ['nullable', 'integer', Rule::exists('districts', 'id')],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'commodity' => 'komoditas',
            'district' => 'kecamatan',
            'start_date' => 'tanggal mulai',
            'end_date' => 'tanggal akhir',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        // Sengaja tidak melempar exception; lihat docblock kelas.
    }

    public function sectorType(): SectorType
    {
        return $this->route('sector');
    }

    public function sector(): Sector
    {
        return $this->sectorModel ??= Sector::query()
            ->where('code', $this->sectorType()->code())
            ->firstOrFail();
    }

    public function filters(): DashboardFilters
    {
        return DashboardFilters::fromArray($this->getValidatorInstance()->valid());
    }

    public function filterErrors(): MessageBag
    {
        return $this->getValidatorInstance()->errors();
    }
}
