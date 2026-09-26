<?php

namespace Tests\Feature\Admin;

use App\Enums\InputType;
use App\Enums\SectorType;
use App\Models\Commodity;
use App\Models\Distribution;
use App\Models\FarmerGroup;
use App\Models\Production;
use App\Models\RecipientCategory;
use App\Models\User;
use App\Services\HomeStatistics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductionTest extends TestCase
{
    use RefreshDatabase;

    private FarmerGroup $group;

    private Commodity $kangkung;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-06-15 09:00:00');
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $this->group = FarmerGroup::factory()->create(['name' => 'Buruan SAE Sauyunan']);
        $this->kangkung = Commodity::factory()->inSector('SAYUR')->create(['name' => 'KANGKUNG', 'growing_days' => 25]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function plantingData(array $overrides = []): array
    {
        return [
            'farmer_group_id' => $this->group->id,
            'commodity_id' => $this->kangkung->id,
            'planting_category' => 'seed',
            'start_date' => '2026-06-01',
            'initial_quantity' => '120',
            'estimated_harvest_quantity' => '15.5',
            ...$overrides,
        ];
    }

    public function test_all_admin_pages_render(): void
    {
        $production = Production::factory()->for($this->group)->for($this->kangkung)->create();

        $this->get('/admin')->assertOk()->assertSee('Selamat datang');
        foreach (SectorType::cases() as $sector) {
            $this->get("/admin/produksi/{$sector->value}")->assertOk()->assertSee($sector->label());
            $this->get("/admin/produksi/{$sector->value}/tambah")->assertOk();
        }
        $this->get("/admin/produksi/vegetable/{$production->id}/ubah")->assertOk();
        $this->get("/admin/produksi/vegetable/{$production->id}/panen")->assertOk()->assertSee('Konsumsi Pribadi');
    }

    public function test_planting_data_is_created_with_an_estimated_harvest_date(): void
    {
        $this->post('/admin/produksi/vegetable', $this->plantingData())
            ->assertRedirect('/admin/produksi/vegetable')
            ->assertSessionHas('success');

        $production = Production::sole();
        $this->assertSame('2026-06-26', $production->estimated_harvest_date->toDateString());
        $this->assertEqualsWithDelta(15.5, (float) $production->estimated_harvest_quantity, 0.001);
        $this->assertNull($production->harvest_date);
        $this->assertSame('seed', $production->planting_category->value);
    }

    public function test_estimated_date_is_required_when_commodity_has_no_growing_days(): void
    {
        $this->kangkung->update(['growing_days' => null]);

        $this->post('/admin/produksi/vegetable', $this->plantingData())->assertSessionHasErrors('estimated_harvest_date');
        $this->post('/admin/produksi/vegetable', $this->plantingData(['estimated_harvest_date' => '2026-07-01']))->assertSessionHasNoErrors();
    }

    public function test_commodity_from_another_sector_and_deleted_group_are_rejected(): void
    {
        $lele = Commodity::factory()->inSector('IKAN')->create();
        $this->post('/admin/produksi/vegetable', $this->plantingData(['commodity_id' => $lele->id]))->assertSessionHasErrors('commodity_id');

        $this->group->delete();
        $this->post('/admin/produksi/vegetable', $this->plantingData())->assertSessionHasErrors('farmer_group_id');
    }

    public function test_sector_specific_details_are_saved(): void
    {
        $lele = Commodity::factory()->inSector('IKAN')->create(['growing_days' => 90]);
        $this->post('/admin/produksi/fish', [
            'farmer_group_id' => $this->group->id, 'commodity_id' => $lele->id, 'start_date' => '2026-06-01',
            'initial_quantity' => 500, 'estimated_harvest_quantity' => 60,
            'feed' => ['applied_date' => '2026-06-05', 'name' => 'Pelet', 'quantity' => '12'],
        ])->assertSessionHasNoErrors();
        $fish = Production::where('commodity_id', $lele->id)->sole();
        $this->assertNull($fish->planting_category);
        $this->assertSame('Pelet', $fish->inputs()->where('type', InputType::Feed)->value('name'));

        $keripik = Commodity::factory()->inSector('OLAHAN_HASIL')->create(['growing_days' => null]);
        $this->post('/admin/produksi/processed-products', [
            'farmer_group_id' => $this->group->id, 'commodity_id' => $keripik->id, 'start_date' => '2026-06-01',
            'base_ingredient' => 'Singkong', 'brand' => 'SAE Crispy', 'pirt_permit' => 'P-IRT 123',
        ])->assertSessionHasNoErrors();
        $processed = Production::where('commodity_id', $keripik->id)->sole();
        $this->assertSame('SAE Crispy', $processed->processedProductDetail->brand);
        $this->assertNull($processed->estimated_harvest_date);

        $cabai = Commodity::factory()->inSector('BIBIT')->create(['growing_days' => 30]);
        $this->post('/admin/produksi/nursery', [
            'farmer_group_id' => $this->group->id, 'commodity_id' => $cabai->id, 'start_date' => '2026-06-01',
            'initial_quantity' => 300, 'estimated_harvest_quantity' => 250, 'origin' => 'DKPP',
        ])->assertSessionHasNoErrors();
        $this->assertSame('DKPP', Production::where('commodity_id', $cabai->id)->sole()->seedlingDetail->origin);
    }

    public function test_harvest_total_is_the_sum_of_distributions(): void
    {
        $production = Production::factory()->for($this->group)->for($this->kangkung)->pending('2026-06-20')->create();
        Cache::put(HomeStatistics::CACHE_KEY, ['stale' => true]);

        $this->put("/admin/produksi/vegetable/{$production->id}/panen", [
            'harvest_date' => '2026-06-14',
            'photo' => UploadedFile::fake()->image('panen.jpg', 2400, 1600),
            'selling_price' => 50000,
            'distributions' => [
                'KP' => ['quantity' => '4', 'household_count' => '2', 'person_count' => '5'],
                'STUNTING' => ['quantity' => '3.5', 'household_count' => '', 'person_count' => '6'],
                'MM' => ['quantity' => '', 'household_count' => '', 'person_count' => ''],
                'DIJUAL' => ['quantity' => '8', 'person_count' => '3'],
            ],
        ])->assertRedirect('/admin/produksi/vegetable?status=harvested');

        $production->refresh();
        $this->assertSame('2026-06-14', $production->harvest_date->toDateString());
        $this->assertEqualsWithDelta(15.5, (float) $production->harvest_quantity, 0.001);
        $this->assertSame(50000, $production->selling_price);
        $this->assertSame(3, $production->distributions()->count());
        Storage::disk('public')->assertExists(Production::IMAGE_DIRECTORY.'/'.$production->image);
        $this->assertFalse(Cache::has(HomeStatistics::CACHE_KEY), 'Cache portal publik harus dikosongkan.');

        // Ubah lagi tanpa foto baru: foto lama tetap, kategori yang dikosongkan terhapus.
        $image = $production->image;
        $this->put("/admin/produksi/vegetable/{$production->id}/panen", [
            'harvest_date' => '2026-06-14',
            'distributions' => ['KP' => ['quantity' => '10'], 'STUNTING' => ['quantity' => '']],
        ])->assertSessionHasNoErrors();

        $production->refresh();
        $this->assertSame($image, $production->image);
        $this->assertEqualsWithDelta(10, (float) $production->harvest_quantity, 0.001);
        $this->assertSame(['KP'], $production->distributions()->with('recipientCategory')->get()->pluck('recipientCategory.code')->all());
    }

    public function test_harvest_validation(): void
    {
        $production = Production::factory()->for($this->group)->for($this->kangkung)->pending('2026-06-20')->create(['start_date' => '2026-06-01']);
        $url = "/admin/produksi/vegetable/{$production->id}/panen";

        $this->put($url, ['harvest_date' => '2026-05-01', 'distributions' => ['KP' => ['quantity' => '']]])
            ->assertSessionHasErrors(['harvest_date', 'photo', 'distributions']);

        $this->put($url, [
            'harvest_date' => '2026-06-16', 'photo' => UploadedFile::fake()->image('a.jpg'),
            'distributions' => ['DIJUAL' => ['quantity' => '5']],
        ])->assertSessionHasErrors([
            'harvest_date' => 'Tanggal panen tidak boleh melewati hari ini.',
            'selling_price' => 'Total harga jual wajib diisi karena ada hasil yang dijual.',
        ]);

        $this->put($url, [
            'harvest_date' => '2026-06-10', 'photo' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'),
            'distributions' => ['KP' => ['quantity' => '1'], 'SEKOLAH' => ['quantity' => '1']],
        ])->assertSessionHasErrors(['photo', 'distributions' => 'Kategori penyaluran tidak dikenal.']);
    }

    public function test_fish_harvest_requires_head_count_and_fruit_saves_fertilizer(): void
    {
        $lele = Commodity::factory()->inSector('IKAN')->create();
        $fish = Production::factory()->for($this->group)->for($lele)->pending('2026-06-20')->create();
        $this->put("/admin/produksi/fish/{$fish->id}/panen", [
            'harvest_date' => '2026-06-14', 'photo' => UploadedFile::fake()->image('a.jpg'),
            'distributions' => ['KP' => ['quantity' => '2']],
        ])->assertSessionHasErrors('harvest_head_count');

        $jeruk = Commodity::factory()->inSector('BUAH')->create();
        $fruit = Production::factory()->for($this->group)->for($jeruk)->pending('2026-06-20')->create();
        $this->put("/admin/produksi/fruit/{$fruit->id}/panen", [
            'harvest_date' => '2026-06-14', 'photo' => UploadedFile::fake()->image('a.jpg'),
            'distributions' => ['KP' => ['quantity' => '2']],
            'fertilizer' => ['applied_date' => '2026-06-02', 'name' => 'Kompos', 'quantity' => '5'],
        ])->assertSessionHasNoErrors();
        $this->assertSame('Kompos', $fruit->inputs()->where('type', InputType::Fertilizer)->value('name'));
    }

    public function test_legacy_distribution_categories_are_kept_when_editing_a_harvest(): void
    {
        $bibit = Commodity::factory()->inSector('BIBIT')->create();
        $production = Production::factory()->for($this->group)->for($bibit)->create(['image' => 'lama.jpg']);
        $legacy = RecipientCategory::create(['code' => 'DIBAGIKAN_REKAP', 'name' => 'Rekap', 'sort_order' => 19]);
        Distribution::factory()->for($production)->create(['recipient_category_id' => $legacy->id, 'quantity' => null, 'person_count' => 40]);

        $this->put("/admin/produksi/nursery/{$production->id}/panen", [
            'harvest_date' => '2026-06-10',
            'distributions' => ['MS' => ['quantity' => '100'], 'SEKOLAH' => ['quantity' => '50']],
        ])->assertSessionHasNoErrors();

        $this->assertSame(3, $production->distributions()->count());
        $this->assertEqualsWithDelta(150, (float) $production->fresh()->harvest_quantity, 0.001);
    }

    public function test_index_filters_by_status_and_hides_deleted_groups(): void
    {
        Production::factory()->for($this->group)->for($this->kangkung)->create();                              // sudah panen
        Production::factory()->for($this->group)->for($this->kangkung)->pending('2026-06-01')->create();       // terlambat
        Production::factory()->for($this->group)->for($this->kangkung)->pending('2026-07-01')->create();       // belum panen
        $deleted = FarmerGroup::factory()->create(['name' => 'Kelompok Terhapus']);
        Production::factory()->for($deleted)->for($this->kangkung)->create();
        $deleted->delete();
        Production::factory()->for($this->group)->for(Commodity::factory()->inSector('IKAN'))->create();       // sektor lain

        $this->get('/admin/produksi/vegetable')
            ->assertOk()
            ->assertViewHas('counts', ['all' => 3, 'pending' => 2, 'late' => 1, 'harvested' => 1])
            ->assertDontSee('Kelompok Terhapus');

        $this->get('/admin/produksi/vegetable?status=late')->assertViewHas('productions', fn ($p) => $p->total() === 1);
    }

    public function test_production_of_another_sector_returns_404_and_delete_removes_everything(): void
    {
        Storage::disk('public')->put(Production::IMAGE_DIRECTORY.'/foto.jpg', 'x');
        $production = Production::factory()->for($this->group)->for($this->kangkung)->create(['image' => 'foto.jpg']);
        Distribution::factory()->for($production)->create();

        $this->get("/admin/produksi/fish/{$production->id}/ubah")->assertNotFound();
        $this->delete("/admin/produksi/fish/{$production->id}")->assertNotFound();

        $this->delete("/admin/produksi/vegetable/{$production->id}")->assertSessionHas('success');
        $this->assertModelMissing($production);
        $this->assertSame(0, Distribution::count());
        Storage::disk('public')->assertMissing(Production::IMAGE_DIRECTORY.'/foto.jpg');
    }

    public function test_image_url_is_null_when_the_file_is_missing(): void
    {
        Storage::disk('public')->put(Production::IMAGE_DIRECTORY.'/ada.jpg', 'x');
        $withFile = Production::factory()->for($this->group)->for($this->kangkung)->create(['image' => 'ada.jpg']);
        $missing = Production::factory()->for($this->group)->for($this->kangkung)->create(['image' => 'hilang.jpg']);

        $this->assertStringEndsWith('/storage/images/panen/ada.jpg', $withFile->image_url);
        $this->assertNull($missing->image_url);
    }

    public function test_shared_legacy_photo_is_kept_until_no_production_uses_it(): void
    {
        // Setelah duplikat dirapikan, satu foto lama dipakai banyak data panen.
        $path = Production::IMAGE_DIRECTORY.'/1782462719_9f5b9698f714581ea13b.jpg';
        Storage::disk('public')->put($path, 'x');
        [$first, $second, $third] = Production::factory()->count(3)->for($this->group)->for($this->kangkung)
            ->create(['image' => '1782462719_9f5b9698f714581ea13b.jpg', 'start_date' => '2026-06-01'])->all();

        $this->delete("/admin/produksi/vegetable/{$first->id}")->assertSessionHas('success');
        Storage::disk('public')->assertExists($path);

        // Mengganti foto satu data panen tidak menghapus foto yang masih dipakai data lain.
        $this->put("/admin/produksi/vegetable/{$second->id}/panen", [
            'harvest_date' => '2026-06-14',
            'photo' => UploadedFile::fake()->image('baru.jpg'),
            'distributions' => ['KP' => ['quantity' => '2']],
        ])->assertSessionHasNoErrors();
        $this->assertNotSame('1782462719_9f5b9698f714581ea13b.jpg', $second->fresh()->image);
        Storage::disk('public')->assertExists($path);

        $this->delete("/admin/produksi/vegetable/{$third->id}")->assertSessionHas('success');
        Storage::disk('public')->assertMissing($path);
    }
}
