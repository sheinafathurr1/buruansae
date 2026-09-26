<?php

namespace Tests\Feature;

use App\Enums\DistributionGroup;
use App\Enums\SectorType;
use App\Models\Commodity;
use App\Models\Distribution;
use App\Models\District;
use App\Models\FarmerGroup;
use App\Models\Production;
use App\Models\RecipientCategory;
use App\Models\Sector;
use App\Models\Village;
use App\Services\SectorDashboard;
use App\Support\DashboardFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Angka dashboard dihitung dari data kecil yang totalnya bisa dihitung manual.
 * "Hari ini" = 15 Jun 2026.
 */
class SectorDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private District $districtA;

    private District $districtB;

    private Village $villageA1;

    private Village $villageA2;

    private Commodity $kangkung;

    private Production $harvestedA1;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-06-15 10:00:00');

        $this->districtA = District::factory()->create(['name' => 'COBLONG']);
        $this->districtB = District::factory()->create(['name' => 'GEDEBAGE']);
        $this->villageA1 = Village::factory()->for($this->districtA)->create(['name' => 'CIPAGANTI']);
        $this->villageA2 = Village::factory()->for($this->districtA)->create(['name' => 'DAGO']);
        $villageB1 = Village::factory()->for($this->districtB)->create(['name' => 'RANCABOLANG']);

        $groupA1 = FarmerGroup::factory()->for($this->villageA1)->create();
        $groupA2 = FarmerGroup::factory()->for($this->villageA2)->create();
        $groupB1 = FarmerGroup::factory()->for($villageB1)->create();
        $deletedGroup = FarmerGroup::factory()->for($this->villageA1)->create();

        $this->kangkung = Commodity::factory()->inSector('SAYUR')->create(['name' => 'KANGKUNG']);
        $bayam = Commodity::factory()->inSector('SAYUR')->create(['name' => 'BAYAM']);
        $lele = Commodity::factory()->inSector('IKAN')->create(['name' => 'LELE']);

        $make = fn (FarmerGroup $g, Commodity $c) => Production::factory()->for($g)->for($c);

        // Sudah panen: 10 + 5 + 7 = 22
        $this->harvestedA1 = $make($groupA1, $this->kangkung)->harvestedOn('2026-06-01', 10)->create();
        $make($groupA2, $bayam)->harvestedOn('2026-03-01', 5)->create();
        $make($groupB1, $this->kangkung)->harvestedOn('2026-06-10', 7)->create();

        // Belum panen: terlambat (4), 7 hari ke depan (3), masih lama (6)
        $make($groupA1, $this->kangkung)->pending('2026-06-10', 4, 20)->create();
        $make($groupB1, $this->kangkung)->pending('2026-06-18', 3, 15)->create();
        $make($groupA2, $bayam)->pending('2026-08-01', 6, 30)->create();

        // Tidak boleh ikut dihitung: kelompok terhapus & sektor lain.
        $make($deletedGroup, $this->kangkung)->harvestedOn('2026-06-02', 100)->create();
        $deletedGroup->delete();
        $make($groupA1, $lele)->harvestedOn('2026-06-02', 50)->create();

        $category = fn (string $code) => RecipientCategory::query()->where('code', $code)->value('id');
        Distribution::factory()->for($this->harvestedA1)->create(['recipient_category_id' => $category('KP'), 'quantity' => 4, 'household_count' => 2, 'person_count' => 5]);
        Distribution::factory()->for($this->harvestedA1)->create(['recipient_category_id' => $category('STUNTING'), 'quantity' => 3, 'household_count' => 1, 'person_count' => 4]);
        Distribution::factory()->for($this->harvestedA1)->create(['recipient_category_id' => $category('DIJUAL'), 'quantity' => 3, 'household_count' => null, 'person_count' => 2]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function dashboard(array $filters = []): SectorDashboard
    {
        return new SectorDashboard(
            SectorType::Vegetable,
            Sector::query()->where('code', 'SAYUR')->firstOrFail(),
            DashboardFilters::fromArray($filters),
        );
    }

    public function test_summary_without_filters(): void
    {
        $summary = $this->dashboard()->summary();

        $this->assertEqualsWithDelta(22, $summary['harvest']['quantity'], 0.001);
        $this->assertSame(3, $summary['harvest']['cycles']);
        $this->assertSame(3, $summary['harvest']['groups']);
        $this->assertEqualsWithDelta(13, $summary['pending']['estimate'], 0.001);
        $this->assertEqualsWithDelta(65, $summary['pending']['initial'], 0.001);
        $this->assertSame(3, $summary['pending']['cycles']);
        $this->assertEqualsWithDelta(4, $summary['late']['estimate'], 0.001);
        $this->assertSame(1, $summary['late']['cycles']);
        $this->assertEqualsWithDelta(3, $summary['upcoming']['estimate'], 0.001);
        $this->assertSame(1, $summary['upcoming']['cycles']);
    }

    public function test_breakdown_is_per_district_then_per_village(): void
    {
        $city = $this->dashboard();
        $this->assertSame('district', $city->areaLevel());
        $this->assertSame(
            [['COBLONG', 15.0, 2], ['GEDEBAGE', 7.0, 1]],
            $city->harvestByArea()->map(fn ($r) => [$r->name, $r->total, $r->cycles])->all(),
        );

        $district = $this->dashboard(['district' => $this->districtA->id]);
        $this->assertSame('village', $district->areaLevel());
        $this->assertSame(
            [['CIPAGANTI', 10.0], ['DAGO', 5.0]],
            $district->harvestByArea()->map(fn ($r) => [$r->name, $r->total])->all(),
        );
        $this->assertEqualsWithDelta(15, $district->summary()['harvest']['quantity'], 0.001);
        $this->assertSame(['CIPAGANTI'], $district->lateByArea()->pluck('name')->all());
        $this->assertSame('2026-06-10', $district->lateByArea()->first()->date);
    }

    public function test_commodity_and_date_filters(): void
    {
        $this->assertEqualsWithDelta(17, $this->dashboard(['commodity' => $this->kangkung->id])->summary()['harvest']['quantity'], 0.001);

        $ranged = $this->dashboard(['start_date' => '2026-05-01', 'end_date' => '2026-06-30']);
        $summary = $ranged->summary();
        $this->assertEqualsWithDelta(17, $summary['harvest']['quantity'], 0.001);
        // Belum panen memakai tanggal perkiraan panen: 10 Jun (4) + 18 Jun (3).
        $this->assertEqualsWithDelta(7, $summary['pending']['estimate'], 0.001);

        $this->assertEqualsWithDelta(5, $this->dashboard(['end_date' => '2026-03-31'])->summary()['harvest']['quantity'], 0.001);
    }

    public function test_upcoming_is_sorted_by_nearest_date(): void
    {
        $rows = $this->dashboard()->upcomingByArea();

        $this->assertSame(['GEDEBAGE'], $rows->pluck('name')->all());
        $this->assertSame('2026-06-18', $rows->first()->date);
    }

    public function test_distribution_summary_groups_categories(): void
    {
        $rows = $this->dashboard()->distributionSummary();

        $this->assertSame(['KP', 'STUNTING', 'DIJUAL'], $rows->pluck('code')->all());
        $this->assertSame(DistributionGroup::SelfConsumption, $rows[0]->group);
        $this->assertSame(DistributionGroup::Shared, $rows[1]->group);
        $this->assertSame(DistributionGroup::Sold, $rows[2]->group);
        $this->assertSame(4, $rows[1]->persons);
        $this->assertEqualsWithDelta(10, $rows->sum('quantity'), 0.001);
    }

    public function test_dashboard_page_renders_totals_and_filters(): void
    {
        $this->get('/vegetable')
            ->assertOk()
            ->assertSee('Sayur-sayuran')
            ->assertSee('22')
            ->assertSee('Coblong')
            ->assertSee('Terlambat panen');

        $this->get('/vegetable?district='.$this->districtA->id)
            ->assertOk()
            ->assertSee('Hasil panen per kelurahan')
            ->assertSee('Cipaganti')
            ->assertSee('Kec. Coblong');
    }

    public function test_invalid_filters_are_ignored_and_reported_without_redirect(): void
    {
        $this->get('/vegetable?commodity=999999&start_date=bukan-tanggal&district='.$this->districtA->id)
            ->assertOk()
            ->assertSee('Sebagian filter tidak dipakai')
            ->assertSee('Komoditas yang dipilih tidak tersedia.')
            ->assertSee('Tanggal mulai harus berformat Y-m-d.')
            // Filter kecamatan yang valid tetap dipakai.
            ->assertSee('Hasil panen per kelurahan');
    }

    public function test_commodity_from_another_sector_is_rejected(): void
    {
        $lele = Commodity::query()->where('name', 'LELE')->firstOrFail();

        $this->get('/vegetable?commodity='.$lele->id)->assertOk()->assertSee('Komoditas yang dipilih tidak tersedia.');
    }

    public function test_end_date_before_start_date_is_rejected(): void
    {
        $this->get('/vegetable?start_date=2026-06-01&end_date=2026-05-01')
            ->assertOk()
            ->assertSee('Tanggal akhir harus berupa tanggal yang sama dengan atau sesudah tanggal mulai.');
    }

    public function test_village_detail_is_a_fragment_for_ajax_and_a_page_otherwise(): void
    {
        $url = route('sectors.villages.harvested', ['sector' => SectorType::Vegetable, 'village' => $this->villageA1]);

        $this->get($url, ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertDontSee('<html', false)
            ->assertSee('1 catatan panen')
            ->assertSee('Konsumsi Pribadi')
            ->assertSee('Stunting');

        $this->get($url)->assertOk()->assertSee('<html', false)->assertSee('Kembali ke dashboard');
    }

    public function test_village_pending_detail_marks_late_cycles(): void
    {
        $this->get(route('sectors.villages.pending', ['sector' => SectorType::Vegetable, 'village' => $this->villageA1]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertSee('1 siklus belum dipanen')
            ->assertSee('Terlambat');
    }

    public function test_pending_detail_is_not_available_for_processed_products(): void
    {
        $this->get(route('sectors.villages.pending', ['sector' => SectorType::ProcessedProduct, 'village' => $this->villageA1]))
            ->assertNotFound();
    }
}
