<?php

namespace App\Http\Requests\Admin;

use App\Models\Sector;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommodityRequest extends FormRequest
{
    public const MAX_IMAGE_KB = 4096;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Nama komoditas di database memakai huruf besar (KANGKUNG, LELE).
        if ($this->filled('name')) {
            $this->merge(['name' => mb_strtoupper(preg_replace('/\s+/', ' ', trim((string) $this->input('name'))))]);
        }
    }

    public function rules(): array
    {
        $commodity = $this->route('commodity');
        $sectorCode = Sector::query()->whereKey($this->integer('sector_id'))->value('code');

        return [
            'sector_id' => ['required', 'integer', Rule::exists('sectors', 'id')],
            'name' => [
                'required', 'string', 'max:150',
                Rule::unique('commodities', 'name')->where('sector_id', $this->integer('sector_id'))->ignore($commodity?->id),
            ],
            'growing_days' => [Rule::requiredIf($sectorCode !== null && $sectorCode !== 'OLAHAN_HASIL'), 'nullable', 'integer', 'min:1', 'max:3650'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.self::MAX_IMAGE_KB],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'sector_id' => 'sektor',
            'name' => 'nama komoditas',
            'growing_days' => 'durasi tanam',
            'image' => 'gambar',
        ];
    }

    public function messages(): array
    {
        return ['name.unique' => 'Komoditas dengan nama ini sudah ada di sektor tersebut.'];
    }
}
