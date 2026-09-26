<?php

namespace Tests\Feature;

use App\Enums\SectorType;
use App\Models\FarmerGroup;
use App\Models\Village;
use App\Services\HomeStatistics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_lists_all_sectors_and_statistics(): void
    {
        FarmerGroup::factory()->count(2)->create();
        FarmerGroup::factory()->inactive()->create();

        $response = $this->get('/')->assertOk()->assertSee('Buruan');

        foreach (SectorType::cases() as $sector) {
            $response->assertSee($sector->label())->assertSee(route('sectors.show', $sector), false);
        }

        $stats = app(HomeStatistics::class)->get();
        $this->assertSame(3, $stats['groups']);
        $this->assertSame(2, $stats['active_groups']);
    }

    public static function sectorSlugs(): array
    {
        return collect(SectorType::cases())->mapWithKeys(fn ($s) => [$s->value => [$s->value]])->all();
    }

    #[DataProvider('sectorSlugs')]
    public function test_each_sector_page_renders_with_an_empty_database(string $slug): void
    {
        $this->get('/'.$slug)->assertOk()->assertSee('Belum ada data');
    }

    public function test_legacy_sector_query_string_still_works(): void
    {
        $this->get('/vegetable?sector=sayur')->assertOk()->assertSee('Sayur-sayuran');
    }

    public function test_unknown_paths_return_404(): void
    {
        $this->get('/tidak-ada')->assertNotFound()->assertSee('Halaman tidak ditemukan');
        $this->get('/vegetable/kelurahan/999999/panen')->assertNotFound();
    }

    public function test_news_pages(): void
    {
        $this->get('/news')->assertOk()->assertSee('Buruan SAE Sajuta Saratus');
        $this->get('/news/sekemala-integrated-farming')
            ->assertOk()
            ->assertSee('Sekemala Integrated Farming (Seinfarm)')
            ->assertSee('Berita lainnya');
        $this->get('/news/tidak-ada')->assertNotFound();
    }

    public function test_map_page(): void
    {
        $this->get('/map')->assertOk()->assertSee('Peta sebaran kelompok')->assertSee(route('api.locations'), false);
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_locations_api_counts_groups_per_village(): void
    {
        $village = Village::factory()->create(['name' => 'CIPAGANTI', 'latitude' => -6.89, 'longitude' => 107.60]);
        FarmerGroup::factory()->for($village)->create();
        FarmerGroup::factory()->for($village)->inactive()->create();
        FarmerGroup::factory()->for($village)->create()->delete(); // soft delete: tidak dihitung

        Village::factory()->create(['latitude' => -6.20, 'longitude' => 106.80]); // Jakarta: di luar batas
        Village::factory()->withoutCoordinates()->create();

        $this->getJson('/api/locations')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'CIPAGANTI')
            ->assertJsonPath('0.district', $village->district->name)
            ->assertJsonPath('0.total_kelompok', 2)
            ->assertJsonPath('0.active_kelompok', 1)
            ->assertJsonStructure([['id', 'name', 'district_id', 'district', 'latitude', 'longitude', 'total_kelompok', 'active_kelompok']]);
    }
}
