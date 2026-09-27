<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FarmerGroupRequest extends FormRequest
{
    public const MAX_PHOTO_KB = 8192;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => match ($this->input('is_active')) {
                '1' => true, '0' => false, default => null,
            },
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'village_id' => ['required', 'integer', Rule::exists('villages', 'id')],
            'rw' => ['nullable', 'integer', 'min:1', 'max:255'],
            'leader_name' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'extension_officer' => ['required', 'string', 'max:150'],
            'facilitator' => ['required', 'string', 'max:150'],
            'land_area_m2' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'land_status' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'status_note' => ['nullable', 'string', 'max:255'],
            'description_url' => ['nullable', 'url', 'max:500'],
            'land_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.self::MAX_PHOTO_KB],
            'leader_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.self::MAX_PHOTO_KB],
            'remove_land_photo' => ['nullable', 'boolean'],
            'remove_leader_photo' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama kelompok',
            'village_id' => 'kelurahan',
            'rw' => 'RW',
            'leader_name' => 'nama ketua',
            'phone' => 'nomor kontak',
            'extension_officer' => 'penyuluh',
            'facilitator' => 'pendamping',
            'land_area_m2' => 'luas lahan',
            'land_status' => 'status lahan',
            'is_active' => 'status keaktifan',
            'status_note' => 'keterangan status',
            'description_url' => 'tautan',
            'land_photo' => 'foto lahan',
            'leader_photo' => 'foto ketua',
        ];
    }

    public function messages(): array
    {
        return ['phone.regex' => 'Nomor kontak hanya boleh berisi angka, spasi, +, -, dan tanda kurung.'];
    }
}
