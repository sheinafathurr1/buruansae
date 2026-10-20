<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\FarmerGroup;
use App\Models\Production;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Menu Pengguna (admin) & kata sandi sementara. */
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create(['username' => 'admindkpp']);
        $this->actingAs($this->admin);
    }

    private function userData(array $overrides = []): array
    {
        return [
            'name' => 'Budi Penyuluh',
            'username' => 'budi',
            'email' => 'budi@bandung.go.id',
            'role' => 'staff',
            'is_active' => '1',
            'password' => 'Sementara123',
            ...$overrides,
        ];
    }

    public function test_admin_creates_a_staff_account_with_assigned_groups(): void
    {
        [$a, $b] = FarmerGroup::factory()->count(2)->create();

        $this->get('/admin/pengguna')->assertOk()->assertSee('admindkpp');
        $this->get('/admin/pengguna/tambah')->assertOk()->assertSee($a->name)->assertSee('Buat kata sandi acak');

        $this->post('/admin/pengguna', $this->userData(['group_ids' => [$a->id, $b->id]]))
            ->assertRedirect('/admin/pengguna')->assertSessionHas('success');

        $user = User::firstWhere('username', 'budi');
        $this->assertSame(UserRole::Staff, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue($user->must_change_password);
        $this->assertTrue(Hash::check('Sementara123', $user->password));
        $this->assertEqualsCanonicalizing([$a->id, $b->id], $user->farmerGroups()->pluck('farmer_groups.id')->all());
    }

    public function test_user_validation(): void
    {
        $this->post('/admin/pengguna', $this->userData([
            'username' => 'admindkpp',
            'email' => 'bukan-email',
            'role' => 'superadmin',
            'password' => 'pendek',
            'group_ids' => [999],
        ]))->assertSessionHasErrors(['username', 'email', 'role', 'password', 'group_ids.0']);

        $this->post('/admin/pengguna', $this->userData(['password' => '']))->assertSessionHasErrors('password');
    }

    public function test_temporary_password_must_be_changed_before_anything_else(): void
    {
        $staff = User::factory()->mustChangePassword()->create(['password' => 'Sementara123']);

        $this->actingAs($staff)->get('/admin')->assertRedirect('/admin/profil');
        $this->get('/admin/produksi/vegetable')->assertRedirect('/admin/profil');
        $this->get('/admin/profil')->assertOk()->assertSee('kata sandi sementara');

        $this->put('/admin/profil/kata-sandi', [
            'current_password' => 'Sementara123',
            'password' => 'SandiSendiri45',
            'password_confirmation' => 'SandiSendiri45',
        ])->assertSessionHasNoErrors();

        $this->assertFalse($staff->fresh()->must_change_password);
        $this->get('/admin')->assertOk();
    }

    public function test_admin_resets_password_and_changes_role(): void
    {
        $group = FarmerGroup::factory()->create();
        $staff = User::factory()->create(['username' => 'budi', 'password' => 'Lama12345']);
        $staff->farmerGroups()->attach($group);

        $this->get("/admin/pengguna/{$staff->id}/ubah")->assertOk()->assertSee('Setel ulang kata sandi');

        // Kata sandi kosong = tidak diubah.
        $this->put("/admin/pengguna/{$staff->id}", $this->userData(['password' => '', 'group_ids' => [$group->id]]))
            ->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('Lama12345', $staff->fresh()->password));
        $this->assertFalse($staff->fresh()->must_change_password);

        $this->put("/admin/pengguna/{$staff->id}", $this->userData(['password' => 'Reset12345', 'group_ids' => [$group->id]]))
            ->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('Reset12345', $staff->fresh()->password));
        $this->assertTrue($staff->fresh()->must_change_password);

        // Menjadi admin: penugasan dihapus karena admin mengelola semua kelompok.
        $this->put("/admin/pengguna/{$staff->id}", $this->userData(['role' => 'admin', 'password' => '', 'group_ids' => [$group->id]]))
            ->assertSessionHasNoErrors();
        $this->assertTrue($staff->fresh()->isAdmin());
        $this->assertSame(0, $staff->farmerGroups()->count());
    }

    public function test_admin_cannot_lock_themselves_out(): void
    {
        $this->get("/admin/pengguna/{$this->admin->id}/ubah")->assertOk()
            ->assertSee('Ini akun Anda')->assertDontSee('name="password"', false);

        $this->put("/admin/pengguna/{$this->admin->id}", $this->userData([
            'username' => 'admindkpp', 'role' => 'staff', 'is_active' => '0', 'password' => '',
        ]))->assertSessionHasErrors(['role', 'is_active']);

        $this->put("/admin/pengguna/{$this->admin->id}", $this->userData(['username' => 'admindkpp', 'role' => 'admin', 'password' => 'Baru12345']))
            ->assertSessionHasErrors('password');

        $this->delete("/admin/pengguna/{$this->admin->id}")->assertSessionHas('error');

        $this->assertModelExists($this->admin);
        $this->assertTrue($this->admin->fresh()->isAdmin());
        $this->assertTrue($this->admin->fresh()->is_active);
    }

    public function test_accounts_with_recorded_data_are_deactivated_instead_of_deleted(): void
    {
        $recorder = User::factory()->create();
        Production::factory()->create(['created_by' => $recorder->id]);
        $unused = User::factory()->create();
        $unused->farmerGroups()->attach(FarmerGroup::factory()->create());

        $this->delete("/admin/pengguna/{$recorder->id}")->assertSessionHas('error');
        $this->assertModelExists($recorder);

        $this->delete("/admin/pengguna/{$unused->id}")->assertRedirect('/admin/pengguna');
        $this->assertModelMissing($unused);
        $this->assertDatabaseMissing('farmer_group_user', ['user_id' => $unused->id]);
    }

    public function test_deactivated_staff_is_logged_out(): void
    {
        $staff = User::factory()->create();

        $this->put("/admin/pengguna/{$staff->id}", $this->userData(['is_active' => '0', 'password' => '']))->assertSessionHasNoErrors();

        $this->actingAs($staff->fresh())->get('/admin')->assertRedirect('/admin/masuk');
    }

    public function test_login_records_the_last_login_time(): void
    {
        auth()->logout();
        $user = User::factory()->create(['username' => 'budi', 'password' => 'Rahasia123']);
        $updatedAt = $user->updated_at;
        $this->travel(5)->minutes();

        $this->post('/admin/masuk', ['login' => 'budi', 'password' => 'Rahasia123'])->assertRedirect('/admin');

        $user->refresh();
        $this->assertTrue($user->last_login_at->isAfter($updatedAt));
        $this->assertEquals($updatedAt, $user->updated_at, 'Login tidak dihitung sebagai perubahan akun.');
    }

    public function test_user_command_sets_the_role(): void
    {
        $this->artisan('buruansae:user', ['username' => 'budi', '--email' => 'budi@bandung.go.id', '--password' => 'Rahasia123', '--role' => 'staff'])
            ->assertSuccessful();
        $this->assertSame(UserRole::Staff, User::firstWhere('username', 'budi')->role);

        $this->artisan('buruansae:user', ['username' => 'budi', '--password' => 'Rahasia123', '--role' => 'admin'])->assertSuccessful();
        $this->assertSame(UserRole::Admin, User::firstWhere('username', 'budi')->role);

        $this->artisan('buruansae:user', ['username' => 'baru', '--email' => 'baru@bandung.go.id', '--password' => 'Rahasia123'])->assertSuccessful();
        $this->assertSame(UserRole::Admin, User::firstWhere('username', 'baru')->role, 'Akun pertama dari server menjadi admin.');

        $this->artisan('buruansae:user', ['username' => 'budi', '--password' => 'Rahasia123', '--role' => 'super'])->assertFailed();
    }
}
