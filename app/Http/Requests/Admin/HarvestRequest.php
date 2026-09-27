<?php

namespace App\Http\Requests\Admin;

use App\Enums\SectorType;
use App\Models\Production;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** Form "Data panen" / "Data produksi": tanggal, penyaluran hasil, foto. */
class HarvestRequest extends FormRequest
{
    public const MAX_PHOTO_KB = 8192;

    public function authorize(): bool
    {
        return true;
    }

    public function sectorType(): SectorType
    {
        return $this->route('sector');
    }

    public function production(): Production
    {
        return $this->route('production');
    }

    public function rules(): array
    {
        $sector = $this->sectorType();
        $production = $this->production();
        $rules = [
            'harvest_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'harvest_head_count' => [Rule::requiredIf($sector->tracksHeadCount()), 'nullable', 'numeric', 'min:0', 'max:9999999999'],
            'selling_price' => ['nullable', 'integer', 'min:0', 'max:999999999999'],
            'photo' => [Rule::requiredIf($production->image === null), 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.self::MAX_PHOTO_KB],
            'distributions' => ['required', 'array'],
            'fertilizer' => ['nullable', 'array'],
            'fertilizer.name' => ['nullable', 'string', 'max:150'],
            'fertilizer.quantity' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'fertilizer.applied_date' => ['nullable', 'date_format:Y-m-d'],
        ];

        if ($production->start_date) {
            $rules['harvest_date'][] = 'after_or_equal:'.$production->start_date->toDateString();
        }

        foreach ($sector->recipientCategoryCodes() as $code) {
            $rules["distributions.{$code}"] = ['nullable', 'array'];
            $rules["distributions.{$code}.quantity"] = ['nullable', 'numeric', 'min:0', 'max:999999999'];
            $rules["distributions.{$code}.household_count"] = ['nullable', 'integer', 'min:0', 'max:4294967295'];
            $rules["distributions.{$code}.person_count"] = ['nullable', 'integer', 'min:0', 'max:4294967295'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'harvest_date' => 'tanggal '.strtolower($this->sectorType()->harvestTerm()),
            'harvest_head_count' => 'jumlah ekor',
            'selling_price' => 'total harga jual',
            'photo' => 'foto hasil',
            'fertilizer.name' => 'jenis pupuk',
            'fertilizer.quantity' => 'jumlah pupuk',
            'fertilizer.applied_date' => 'tanggal pemupukan',
        ];

        foreach ($this->sectorType()->recipientCategoryCodes() as $code) {
            $attributes["distributions.{$code}.quantity"] = 'jumlah';
            $attributes["distributions.{$code}.household_count"] = 'jumlah KK';
            $attributes["distributions.{$code}.person_count"] = 'jumlah orang';
        }

        return $attributes;
    }

    public function messages(): array
    {
        return [
            'harvest_date.after_or_equal' => 'Tanggal '.strtolower($this->sectorType()->harvestTerm()).' tidak boleh sebelum '.strtolower($this->sectorType()->startDateLabel()).' ('.format_date($this->production()->start_date).').',
            'harvest_date.before_or_equal' => 'Tanggal '.strtolower($this->sectorType()->harvestTerm()).' tidak boleh melewati hari ini.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $rows = (array) $this->input('distributions', []);
                $unknown = array_diff(array_keys($rows), $this->sectorType()->recipientCategoryCodes());

                if ($unknown !== []) {
                    $validator->errors()->add('distributions', 'Kategori penyaluran tidak dikenal.');

                    return;
                }

                $total = array_sum(array_map(fn ($row) => (float) ($row['quantity'] ?? 0), $rows));
                if ($total <= 0) {
                    $validator->errors()->add('distributions', 'Isi minimal satu jumlah penyaluran (konsumsi pribadi, dibagikan, atau dijual). Jumlah panen dihitung dari totalnya.');
                }

                if ((float) ($rows['DIJUAL']['quantity'] ?? 0) > 0 && ! $this->filled('selling_price')) {
                    $validator->errors()->add('selling_price', 'Total harga jual wajib diisi karena ada hasil yang dijual.');
                }
            },
        ];
    }
}
