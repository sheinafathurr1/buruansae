<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/masuk');
        $this->get('/admin/produksi/vegetable')->assertRedirect('/admin/masuk');
        $this->get('/admin/masuk')->assertOk()->assertSee('Masuk ke dashboard');
    }

    public function test_users_can_log_in_with_username_or_email(): void
    {
        $user = User::factory()->create(['username' => 'admin', 'email' => 'admin@bandung.go.id', 'password' => 'Rahasia123']);

        $this->post('/admin/masuk', ['login' => 'admin', 'password' => 'Rahasia123'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);

        $this->post('/admin/keluar')->assertRedirect('/admin/masuk');
        $this->assertGuest();

        $this->post('/admin/masuk', ['login' => 'admin@bandung.go.id', 'password' => 'Rahasia123'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_and_inactive_accounts_are_rejected(): void
    {
        User::factory()->create(['username' => 'admin', 'password' => 'Rahasia123']);
        User::factory()->inactive()->create(['username' => 'lama', 'password' => 'Rahasia123']);

        $this->from('/admin/masuk')->post('/admin/masuk', ['login' => 'admin', 'password' => 'salah'])
            ->assertRedirect('/admin/masuk')
            ->assertSessionHasErrors('login');
        $this->post('/admin/masuk', ['login' => 'lama', 'password' => 'Rahasia123'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        $this->freezeTime();
        User::factory()->create(['username' => 'admin', 'password' => 'Rahasia123']);

        foreach (range(1, 5) as $_) {
            $this->post('/admin/masuk', ['login' => 'admin', 'password' => 'salah']);
        }

        $this->post('/admin/masuk', ['login' => 'admin', 'password' => 'Rahasia123'])
            ->assertSessionHasErrors(['login' => 'Terlalu banyak percobaan masuk. Coba lagi dalam 60 detik.']);
        $this->assertGuest();
    }

    public function test_deactivated_users_are_logged_out_on_their_next_request(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertOk();

        $user->update(['is_active' => false]);

        $this->actingAs($user)->get('/admin')->assertRedirect('/admin/masuk');
        $this->assertGuest();
    }

    public function test_logged_in_users_skip_the_login_page(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/masuk')->assertRedirect('/admin');
    }

    public function test_profile_and_password_can_be_updated(): void
    {
        $user = User::factory()->create(['password' => 'Rahasia123']);

        $this->actingAs($user)->get('/admin/profil')->assertOk();

        $this->actingAs($user)->put('/admin/profil', ['name' => 'Penyuluh Baru', 'email' => 'baru@bandung.go.id'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Penyuluh Baru', $user->fresh()->name);

        $this->actingAs($user)->put('/admin/profil/kata-sandi', [
            'current_password' => 'salah', 'password' => 'SandiBaru123', 'password_confirmation' => 'SandiBaru123',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($user)->put('/admin/profil/kata-sandi', [
            'current_password' => 'Rahasia123', 'password' => 'SandiBaru123', 'password_confirmation' => 'SandiBaru123',
        ])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('SandiBaru123', $user->fresh()->password));
    }

    public function test_user_command_creates_resets_and_deactivates_accounts(): void
    {
        $this->artisan('buruansae:user', ['username' => 'admin', '--email' => 'admin@bandung.go.id', '--password' => 'Rahasia123'])
            ->expectsOutput('Akun admin berhasil dibuat.')
            ->assertSuccessful();

        $this->artisan('buruansae:user', ['username' => 'admin', '--password' => 'SandiBaru123'])->assertSuccessful();
        $this->assertTrue(Hash::check('SandiBaru123', User::firstWhere('username', 'admin')->password));

        $this->artisan('buruansae:user', ['username' => 'admin', '--password' => 'pendek'])->assertFailed();

        $this->artisan('buruansae:user', ['username' => 'admin', '--deactivate' => true])->assertSuccessful();
        $this->assertFalse(User::firstWhere('username', 'admin')->is_active);
    }
}
