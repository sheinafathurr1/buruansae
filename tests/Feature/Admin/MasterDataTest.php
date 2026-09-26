<?php

namespace Tests\Feature\Admin;

use App\Models\Commodity;
use App\Models\FarmerGroup;
use App\Models\Production;
use App\Models\Sector;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
    }

    public function test_group_list_and_search(): void
    {
        FarmerGroup::factory()->create(['name' => 'Buruan SAE Sauyunan']);
        FarmerGroup::factory()->create(['name' => 'KWT Mekar Sari']);

        $this->get('/admin/kelompok')->assertOk()->assertSee('Buruan SAE Sauyunan')->assertSee('KWT Mekar Sari');
        $this->get('/admin/kelompok?q=Sauyunan')->assertOk()->assertSee('Buruan SAE Sauyunan')->assertDontSee('KWT Mekar Sari');
    }

    public function test_group_can_be_created_with_photos(): void
    {
        $village = Village::factory()->create();

        $this->get('/admin/kelompok/tambah')->assertOk();

        $this->post('/admin/kelompok', [
            'name' => 'Buruan SAE Walagri',
            'village_id' => $village->id,
            'rw' => 5,
            'leader_name' => 'Ibu Euis',
            'phone' => '0812-3456-7890',
            'extension_officer' => 'Pak Ujang',
            'facilitator' => 'Bu Neneng',
            'land_area_m2' => '120.5',
            'is_active' => '1',
            'land_photo' => UploadedFile::fake()->image('lahan.jpg', 2400, 1600),
        ])->assertRedirect('/admin/kelompok')->assertSessionHas('success');

        $group = FarmerGroup::firstWhere('name', 'Buruan SAE Walagri');
        $this->assertTrue($group->is_active);
        $this->assertSame(5, $group->rw);
        Storage::disk('public')->assertExists(FarmerGroup::PHOTO_DIRECTORY.'/'.$group->land_photo);
    }

    public function test_group_validation_messages_are_in_indonesian(): void
    {
        $this->from('/admin/kelompok/tambah')->post('/admin/kelompok', ['is_active' => ''])
            ->assertRedirect('/admin/kelompok/tambah')
            ->assertSessionHasErrors([
                'name' => 'Nama kelompok wajib diisi.',
                'village_id' => 'Kelurahan wajib diisi.',
                'extension_officer' => 'Penyuluh wajib diisi.',
            ]);
    }

    public function test_group_update_can_remove_photo_and_delete_is_soft(): void
    {
        Storage::disk('public')->put(FarmerGroup::PHOTO_DIRECTORY.'/lama.jpg', 'x');
        $group = FarmerGroup::factory()->create(['land_photo' => 'lama.jpg']);
        $production = Production::factory()->for($group)->for(Commodity::factory()->inSector('SAYUR'))->create();

        $this->get("/admin/kelompok/{$group->id}/ubah")->assertOk()->assertSee($group->name);

        $this->put("/admin/kelompok/{$group->id}", [
            'name' => 'Nama Baru', 'village_id' => $group->village_id, 'extension_officer' => 'A', 'facilitator' => 'B',
            'is_active' => '', 'remove_land_photo' => '1',
        ])->assertSessionHasNoErrors();

        $group->refresh();
        $this->assertSame('Nama Baru', $group->name);
        $this->assertNull($group->is_active);
        $this->assertNull($group->land_photo);
        Storage::disk('public')->assertMissing(FarmerGroup::PHOTO_DIRECTORY.'/lama.jpg');

        $this->delete("/admin/kelompok/{$group->id}")->assertRedirect('/admin/kelompok');
        $this->assertSoftDeleted($group);
        $this->assertModelExists($production);
    }

    public function test_commodity_crud(): void
    {
        $sayur = Sector::firstWhere('code', 'SAYUR');

        $this->get('/admin/komoditas')->assertOk();
        $this->get('/admin/komoditas/tambah')->assertOk();

        $this->post('/admin/komoditas', ['sector_id' => $sayur->id, 'name' => '  kangkung   darat ', 'growing_days' => 25])
            ->assertRedirect('/admin/komoditas');
        $commodity = Commodity::firstWhere('name', 'KANGKUNG DARAT');
        $this->assertNotNull($commodity);

        // Nama sama di sektor yang sama ditolak (tidak peka huruf besar/kecil).
        $this->post('/admin/komoditas', ['sector_id' => $sayur->id, 'name' => 'Kangkung Darat', 'growing_days' => 25])
            ->assertSessionHasErrors(['name' => 'Komoditas dengan nama ini sudah ada di sektor tersebut.']);

        // Durasi tanam wajib, kecuali olahan hasil.
        $this->post('/admin/komoditas', ['sector_id' => $sayur->id, 'name' => 'BAYAM'])->assertSessionHasErrors('growing_days');
        $olahan = Sector::firstWhere('code', 'OLAHAN_HASIL');
        $this->post('/admin/komoditas', ['sector_id' => $olahan->id, 'name' => 'KERIPIK'])->assertSessionHasNoErrors();

        $this->put("/admin/komoditas/{$commodity->id}", [
            'sector_id' => $sayur->id, 'name' => 'kangkung', 'growing_days' => 30,
            'image' => UploadedFile::fake()->image('kangkung.png', 300, 300),
        ])->assertSessionHasNoErrors();
        $commodity->refresh();
        $this->assertSame(30, $commodity->growing_days);
        Storage::disk('public')->assertExists(Commodity::IMAGE_DIRECTORY.'/'.$commodity->image);

        $this->delete("/admin/komoditas/{$commodity->id}")->assertRedirect('/admin/komoditas');
        $this->assertModelMissing($commodity);
    }

    public function test_used_commodity_cannot_be_deleted_or_moved_to_another_sector(): void
    {
        $commodity = Commodity::factory()->inSector('SAYUR')->create();
        Production::factory()->for($commodity)->create();

        $this->delete("/admin/komoditas/{$commodity->id}")->assertSessionHas('error');
        $this->assertModelExists($commodity);

        $this->put("/admin/komoditas/{$commodity->id}", [
            'sector_id' => Sector::firstWhere('code', 'BUAH')->id, 'name' => $commodity->name, 'growing_days' => 30,
        ])->assertSessionHasErrors('sector_id');
    }
}
