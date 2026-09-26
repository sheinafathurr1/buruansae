<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Membuat akun pengelola atau menyetel ulang kata sandinya.
 *
 *     php artisan buruansae:user admin --email=admin@bandung.go.id --name="Admin DKPP"
 *     php artisan buruansae:user admin            # akun sudah ada → setel ulang kata sandi
 *     php artisan buruansae:user admin --deactivate
 */
class ManageUser extends Command
{
    protected $signature = 'buruansae:user
        {username : Username untuk login}
        {--email= : Email (wajib untuk akun baru)}
        {--name= : Nama tampilan}
        {--password= : Kata sandi (jika kosong akan ditanyakan)}
        {--deactivate : Nonaktifkan akun}
        {--activate : Aktifkan kembali akun}';

    protected $description = 'Buat akun pengelola dashboard atau setel ulang kata sandinya';

    public function handle(): int
    {
        $username = $this->argument('username');
        $user = User::query()->where('username', $username)->first();

        if ($user && ($this->option('deactivate') || $this->option('activate'))) {
            $user->update(['is_active' => (bool) $this->option('activate')]);
            $this->info("Akun {$username} ".($user->is_active ? 'diaktifkan.' : 'dinonaktifkan.'));

            return self::SUCCESS;
        }

        $email = $this->option('email') ?? $user?->email;
        $password = $this->option('password') ?? $this->secret('Kata sandi baru (min. 8 karakter, huruf & angka)');

        $validator = Validator::make(
            ['username' => $username, 'email' => $email, 'password' => $password],
            [
                'username' => ['required', 'alpha_dash', 'max:30'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'.($user ? ','.$user->id : '')],
                'password' => ['required', Password::min(8)->letters()->numbers()],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        if ($user) {
            $user->update(array_filter([
                'password' => $password,
                'email' => $this->option('email'),
                'name' => $this->option('name'),
            ]));
            $this->info("Kata sandi akun {$username} berhasil diperbarui.");
        } else {
            User::create([
                'name' => $this->option('name') ?? $username,
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'is_active' => true,
            ]);
            $this->info("Akun {$username} berhasil dibuat.");
        }

        return self::SUCCESS;
    }
}
