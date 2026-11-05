<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

/** Form akun pengguna (menu Pengguna, khusus admin). */
class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('admin');
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        $target = $this->route('user');
        $self = $target?->is($this->user());

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'alpha_dash', 'max:30', Rule::unique('users', 'username')->ignore($target?->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($target?->id)],
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_active' => ['boolean'],
            // Kata sandi sementara: wajib untuk akun baru, opsional (setel ulang) saat mengubah.
            // Kata sandi sendiri diganti dari halaman Profil.
            'password' => $self
                ? ['prohibited']
                : [$target ? 'nullable' : 'required', 'string', Password::min(8)->letters()->numbers()],
            'group_ids' => ['nullable', 'array'],
            'group_ids.*' => ['integer', 'distinct', Rule::exists('farmer_groups', 'id')->whereNull('deleted_at')],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $this->route('user')?->is($this->user())) {
                    return;
                }

                // Mencegah admin mengunci dirinya sendiri (dan dashboard kehilangan admin terakhir).
                if ($this->input('role') !== UserRole::Admin->value) {
                    $validator->errors()->add('role', 'Anda tidak bisa mengubah peran akun Anda sendiri.');
                }
                if (! $this->boolean('is_active')) {
                    $validator->errors()->add('is_active', 'Anda tidak bisa menonaktifkan akun Anda sendiri.');
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'username' => 'username',
            'email' => 'email',
            'role' => 'peran',
            'is_active' => 'status akun',
            'password' => 'kata sandi sementara',
            'group_ids' => 'kelompok binaan',
            'group_ids.*' => 'kelompok binaan',
        ];
    }
}
