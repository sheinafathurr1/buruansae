<?php

namespace Tests\Feature\Admin;

use App\Models\FarmerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Token CSRF kedaluwarsa (419): kembali ke form dengan pesan, bukan halaman error. */
class ExpiredFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Laravel melewati pemeriksaan token CSRF saat pengujian; nyalakan lagi.
        $this->app['env'] = 'local';
    }

    public function test_expired_login_form_returns_to_the_login_page_with_the_username_kept(): void
    {
        User::factory()->admin()->create(['username' => 'admindkpp', 'password' => 'Rahasia123']);

        $this->get('/admin/masuk')->assertOk();
        $this->post('/admin/masuk', ['_token' => 'token-lama', 'login' => 'admindkpp', 'password' => 'Rahasia123', 'remember' => '1'])
            ->assertRedirect('/admin/masuk')
            ->assertSessionHas('warning')
            ->assertSessionHasInput('login', 'admindkpp')
            ->assertSessionMissing('_old_input.password');
        $this->assertGuest();

        $this->get('/admin/masuk')->assertOk()
            ->assertSee('Halaman masuk sudah kedaluwarsa')
            ->assertSee('value="admindkpp"', false);

        // Dengan token yang berlaku, masuk berhasil seperti biasa.
        $this->post('/admin/masuk', ['_token' => session()->token(), 'login' => 'admindkpp', 'password' => 'Rahasia123'])
            ->assertRedirect('/admin');
        $this->assertAuthenticated();
    }

    public function test_expired_form_while_logged_in_goes_back_with_the_input(): void
    {
        $group = FarmerGroup::factory()->create(['name' => 'Nama Lama']);
        $this->actingAs(User::factory()->admin()->create());

        $this->from("/admin/kelompok/{$group->id}/ubah")
            ->put("/admin/kelompok/{$group->id}", ['_token' => 'token-lama', 'name' => 'Nama Baru', 'village_id' => $group->village_id])
            ->assertRedirect("/admin/kelompok/{$group->id}/ubah")
            ->assertSessionHas('warning')
            ->assertSessionHasInput('name', 'Nama Baru');

        $this->assertSame('Nama Lama', $group->fresh()->name, 'Permintaan dengan token kedaluwarsa tetap ditolak.');
    }

    public function test_expired_form_after_the_session_ended_asks_to_log_in_again(): void
    {
        $group = FarmerGroup::factory()->create();

        $this->get('/admin/masuk');
        $this->from("http://localhost/admin/kelompok/{$group->id}/ubah")
            ->put("/admin/kelompok/{$group->id}", ['_token' => 'token-lama', 'name' => 'Nama Baru'])
            ->assertRedirect('/admin/masuk')
            ->assertSessionHas('warning')
            ->assertSessionHas('url.intended', "http://localhost/admin/kelompok/{$group->id}/ubah");

        // Halaman asal dari situs lain tidak dipakai sebagai tujuan setelah masuk.
        $this->withSession(['url.intended' => null])
            ->from('https://situs-lain.example/form')
            ->put("/admin/kelompok/{$group->id}", ['_token' => 'token-lama'])
            ->assertRedirect('/admin/masuk')
            ->assertSessionMissing('url.intended');
    }

    public function test_expired_logout(): void
    {
        $this->get('/admin/masuk');
        $this->post('/admin/keluar', ['_token' => 'token-lama'])
            ->assertRedirect('/admin/masuk')->assertSessionHas('status', 'Anda sudah keluar.');

        $this->actingAs(User::factory()->admin()->create());
        $this->post('/admin/keluar', ['_token' => 'token-lama'])
            ->assertRedirect('/admin')->assertSessionHas('warning');
        $this->assertAuthenticated();
    }

    public function test_json_requests_keep_the_419_status(): void
    {
        $this->get('/admin/masuk');
        $this->postJson('/admin/masuk', ['_token' => 'token-lama', 'login' => 'x', 'password' => 'y'])->assertStatus(419);
    }

    public function test_dashboard_and_login_pages_are_not_cached_by_the_browser(): void
    {
        $this->assertStringContainsString('no-store', $this->get('/admin/masuk')->headers->get('Cache-Control'));

        $this->actingAs(User::factory()->admin()->create());
        $this->assertStringContainsString('no-store', $this->get('/admin')->headers->get('Cache-Control'));
        $this->assertStringNotContainsString('no-store', (string) $this->get('/')->headers->get('Cache-Control'));
    }
}
