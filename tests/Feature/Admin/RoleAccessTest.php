<?php

namespace Tests\Feature\Admin;

use App\Models\Commodity;
use App\Models\FarmerGroup;
use App\Models\Production;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Staf hanya melihat & mengubah data kelompok binaannya; admin mengelola semuanya. */
class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    private FarmerGroup $ownGroup;

    private FarmerGroup $otherGroup;

    private Commodity $kangkung;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-06-15 09:00:00');
        Storage::fake('public');

        $this->admin = User::factory()->admin()->create(['name' => 'Admin DKPP']);
        $this->staff = User::factory()->create(['name' => 'Staf Sukajadi']);
        $this->ownGroup = FarmerGroup::factory()->create(['name' => 'Buruan SAE Binaan', 'facilitator' => 'Bu Neneng']);
        $this->otherGroup = FarmerGroup::factory()->create(['name' => 'Buruan SAE Tetangga']);
        $this->kangkung = Commodity::factory()->inSector('SAYUR')->create(['name' => 'KANGKUNG', 'growing_days' => 25]);

        $this->staff->farmerGroups()->attach($this->ownGroup);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function production(FarmerGroup $group, ?User $creator = null): Production
    {
        return Production::factory()->for($group)->for($this->kangkung)->pending('2026-06-10')
            ->create(['created_by' => $creator?->id]);
    }

    private function plantingData(FarmerGroup $group): array
    {
        return [
            'farmer_group_id' => $group->id,
            'commodity_id' => $this->kangkung->id,
            'planting_category' => 'seed',
            'start_date' => '2026-06-01',
            'initial_quantity' => '120',
            'estimated_harvest_quantity' => '15.5',
        ];
    }

    public function test_staff_only_sees_assigned_groups_and_their_data(): void
    {
        $this->production($this->ownGroup);
        $this->production($this->otherGroup);

        $this->actingAs($this->staff);

        $this->get('/admin/produksi/vegetable')->assertOk()
            ->assertSee('Buruan SAE Binaan')->assertDontSee('Buruan SAE Tetangga');
        $this->get('/admin/kelompok')->assertOk()
            ->assertSee('Kelompok binaan')->assertSee('Buruan SAE Binaan')->assertDontSee('Buruan SAE Tetangga')
            ->assertDontSee('Tambah kelompok');
        $this->get('/admin/produksi/vegetable/tambah')->assertOk()
            ->assertSee('Buruan SAE Binaan')->assertDontSee('Buruan SAE Tetangga');

        $this->get('/admin')->assertOk()
            ->assertSee('Kelompok binaan saya')->assertSee('Buruan SAE Binaan')->assertDontSee('Buruan SAE Tetangga')
            ->assertDontSee(route('admin.komoditas.index'))->assertDontSee(route('admin.pengguna.index'));
    }

    public function test_staff_cannot_open_or_change_data_of_other_groups(): void
    {
        $other = $this->production($this->otherGroup);

        $this->actingAs($this->staff);

        $this->get("/admin/kelompok/{$this->otherGroup->id}/ubah")->assertForbidden();
        $this->put("/admin/kelompok/{$this->otherGroup->id}", ['phone' => '0811'])->assertForbidden();
        $this->get("/admin/produksi/vegetable/{$other->id}/ubah")->assertForbidden();
        $this->put("/admin/produksi/vegetable/{$other->id}", $this->plantingData($this->otherGroup))->assertForbidden();
        $this->get("/admin/produksi/vegetable/{$other->id}/panen")->assertForbidden();
        $this->put("/admin/produksi/vegetable/{$other->id}/panen", ['harvest_date' => '2026-06-14', 'distributions' => ['KP' => ['quantity' => '4']]])
            ->assertForbidden();
        $this->delete("/admin/produksi/vegetable/{$other->id}")->assertForbidden();

        $this->assertNull($other->fresh()->harvest_date);
    }

    public function test_staff_cannot_record_data_for_another_group(): void
    {
        $this->actingAs($this->staff);

        $this->post('/admin/produksi/vegetable', $this->plantingData($this->otherGroup))
            ->assertSessionHasErrors(['farmer_group_id' => 'Anda hanya dapat mengisi data untuk kelompok binaan Anda.']);
        $this->assertSame(0, Production::count());

        // Data milik kelompok binaan tidak bisa dipindah ke kelompok lain.
        $own = $this->production($this->ownGroup);
        $this->put("/admin/produksi/vegetable/{$own->id}", $this->plantingData($this->otherGroup))->assertSessionHasErrors('farmer_group_id');
        $this->assertSame($this->ownGroup->id, $own->fresh()->farmer_group_id);

        $this->post('/admin/produksi/vegetable', $this->plantingData($this->ownGroup))->assertSessionHasNoErrors();
        $created = Production::latest('id')->first();
        $this->assertSame($this->staff->id, $created->created_by);
        $this->assertSame($this->staff->id, $created->updated_by);
    }

    public function test_staff_deletes_only_entries_they_recorded(): void
    {
        $byAdmin = $this->production($this->ownGroup, $this->admin);
        $byStaff = $this->production($this->ownGroup, $this->staff);

        $this->actingAs($this->staff);

        $this->delete("/admin/produksi/vegetable/{$byAdmin->id}")->assertForbidden();
        $this->delete("/admin/produksi/vegetable/{$byStaff->id}")->assertRedirect();

        $this->assertModelExists($byAdmin);
        $this->assertModelMissing($byStaff);
    }

    public function test_harvest_and_edits_record_who_changed_the_data(): void
    {
        $production = $this->production($this->ownGroup, $this->admin);

        $this->actingAs($this->staff)
            ->put("/admin/produksi/vegetable/{$production->id}/panen", [
                'harvest_date' => '2026-06-14',
                'photo' => UploadedFile::fake()->image('panen.jpg'),
                'distributions' => ['KP' => ['quantity' => '4']],
            ])
            ->assertSessionHasNoErrors();

        $production->refresh();
        $this->assertSame($this->admin->id, $production->created_by);
        $this->assertSame($this->staff->id, $production->updated_by);

        $this->get("/admin/produksi/vegetable/{$production->id}/ubah")->assertOk()
            ->assertSee('Dicatat oleh')->assertSee('Admin DKPP')->assertSee('terakhir diubah oleh')->assertSee('Staf Sukajadi');
    }

    public function test_access_follows_the_current_assignment(): void
    {
        $production = $this->production($this->ownGroup, $this->staff);
        $successor = User::factory()->create();
        $successor->farmerGroups()->attach($this->ownGroup);

        // Petugas baru bisa melanjutkan data yang dicatat petugas lama.
        $this->actingAs($successor)->get("/admin/produksi/vegetable/{$production->id}/panen")->assertOk();

        $this->staff->farmerGroups()->detach($this->ownGroup);
        $this->actingAs($this->staff)->get("/admin/produksi/vegetable/{$production->id}/panen")->assertForbidden();
    }

    public function test_staff_edits_group_profile_but_not_its_name_or_area(): void
    {
        $this->actingAs($this->staff);

        $this->get("/admin/kelompok/{$this->ownGroup->id}/ubah")->assertOk()
            ->assertSee('Nama dan wilayah kelompok hanya bisa diubah oleh admin.')
            ->assertDontSee('name="name"', false)->assertDontSee('name="staff_ids[]"', false);

        $this->put("/admin/kelompok/{$this->ownGroup->id}", [
            'name' => 'Nama Diganti',
            'village_id' => $this->otherGroup->village_id,
            'phone' => '0812 3456',
            'extension_officer' => 'Pak Ujang',
            'facilitator' => 'Bu Neneng',
            'is_active' => '1',
            'staff_ids' => [$this->staff->id],
        ])->assertRedirect('/admin/kelompok')->assertSessionHasNoErrors();

        $group = $this->ownGroup->fresh();
        $this->assertSame('Buruan SAE Binaan', $group->name);
        $this->assertNotSame($this->otherGroup->village_id, $group->village_id);
        $this->assertSame('0812 3456', $group->phone);

        $this->get('/admin/kelompok/tambah')->assertForbidden();
        $this->post('/admin/kelompok', ['name' => 'Baru'])->assertForbidden();
        $this->delete("/admin/kelompok/{$this->ownGroup->id}")->assertForbidden();
        $this->assertNotSoftDeleted($this->ownGroup);
    }

    public function test_admin_menus_are_closed_to_staff(): void
    {
        $this->actingAs($this->staff);

        $this->get('/admin/komoditas')->assertForbidden();
        $this->get('/admin/komoditas/tambah')->assertForbidden();
        $this->get('/admin/pengguna')->assertForbidden();
        $this->get('/admin/pengguna/tambah')->assertForbidden();
        $this->post('/admin/pengguna', ['username' => 'baru'])->assertForbidden();
    }

    public function test_staff_without_groups_is_told_to_contact_the_admin(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin')->assertOk()->assertSee('Belum ada kelompok binaan');
        $this->get('/admin/produksi/vegetable')->assertOk()->assertSee('Hubungi admin DKPP')->assertDontSee('Tambah data');
        $this->get('/admin/produksi/vegetable/tambah')->assertForbidden();
    }

    public function test_admin_sees_everything_and_staff_monitoring(): void
    {
        $this->production($this->ownGroup, $this->staff);
        $this->production($this->otherGroup);

        $this->actingAs($this->admin);

        $this->get('/admin/produksi/vegetable')->assertOk()->assertSee('Buruan SAE Binaan')->assertSee('Buruan SAE Tetangga');
        $this->get('/admin')->assertOk()
            ->assertSee('Petugas')->assertSee('Staf Sukajadi')->assertSee('1 kelompok')->assertSee('belum punya petugas');
        $this->get('/admin/kelompok?petugas=none')->assertOk()
            ->assertSee('Buruan SAE Tetangga')->assertDontSee('Buruan SAE Binaan');
        $this->get("/admin/kelompok?petugas={$this->staff->id}")->assertOk()
            ->assertSee('Buruan SAE Binaan')->assertDontSee('Buruan SAE Tetangga');
    }

    public function test_admin_assigns_staff_from_the_group_form(): void
    {
        $other = User::factory()->create(['name' => 'Staf Cidadap']);

        $this->actingAs($this->admin);

        $this->get("/admin/kelompok/{$this->otherGroup->id}/ubah")->assertOk()->assertSee('Staf Cidadap');

        $this->put("/admin/kelompok/{$this->otherGroup->id}", [
            'name' => $this->otherGroup->name,
            'village_id' => $this->otherGroup->village_id,
            'extension_officer' => 'Pak Ujang',
            'facilitator' => 'Bu Neneng',
            'staff_ids' => [$this->staff->id, $other->id],
        ])->assertSessionHasNoErrors();

        $this->assertEqualsCanonicalizing([$this->staff->id, $other->id], $this->otherGroup->staff()->pluck('users.id')->all());

        // Admin tidak bisa "ditugaskan": ia sudah mengelola semua kelompok.
        $this->put("/admin/kelompok/{$this->otherGroup->id}", [
            'name' => $this->otherGroup->name,
            'village_id' => $this->otherGroup->village_id,
            'extension_officer' => 'Pak Ujang',
            'facilitator' => 'Bu Neneng',
            'staff_ids' => [$this->admin->id],
        ])->assertSessionHasErrors('staff_ids.0');
    }
}
