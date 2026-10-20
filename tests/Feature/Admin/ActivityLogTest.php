<?php

namespace Tests\Feature\Admin;

use App\Enums\ActivityAction;
use App\Enums\SectorType;
use App\Models\ActivityLog;
use App\Models\Commodity;
use App\Models\FarmerGroup;
use App\Models\Production;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Riwayat pencatatan tanam & panen untuk memantau kinerja petugas. */
class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    private FarmerGroup $group;

    private Commodity $kangkung;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-06-15 09:00:00');
        Storage::fake('public');

        $this->admin = User::factory()->admin()->create(['name' => 'Admin DKPP']);
        $this->staff = User::factory()->create(['name' => 'Rina Kartika']);
        $this->group = FarmerGroup::factory()->create(['name' => 'Buruan SAE Binaan']);
        $this->kangkung = Commodity::factory()->inSector('SAYUR')->create(['name' => 'KANGKUNG', 'growing_days' => 25]);
        $this->staff->farmerGroups()->attach($this->group);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function plant(): Production
    {
        $this->post('/admin/produksi/vegetable', [
            'farmer_group_id' => $this->group->id,
            'commodity_id' => $this->kangkung->id,
            'planting_category' => 'seed',
            'start_date' => '2026-06-01',
            'initial_quantity' => '120',
            'estimated_harvest_quantity' => '15.5',
        ])->assertSessionHasNoErrors();

        return Production::latest('id')->first();
    }

    private function harvest(Production $production, string $quantity): void
    {
        $this->put("/admin/produksi/vegetable/{$production->id}/panen", [
            'harvest_date' => '2026-06-14',
            'photo' => UploadedFile::fake()->image('panen.jpg'),
            'distributions' => ['KP' => ['quantity' => $quantity]],
        ])->assertSessionHasNoErrors();
    }

    public function test_each_recording_step_is_logged_with_its_author(): void
    {
        $this->actingAs($this->staff);

        $production = $this->plant();
        $this->put("/admin/produksi/vegetable/{$production->id}", [
            'farmer_group_id' => $this->group->id,
            'commodity_id' => $this->kangkung->id,
            'planting_category' => 'seed',
            'start_date' => '2026-06-01',
            'initial_quantity' => '150',
            'estimated_harvest_quantity' => '15.5',
        ])->assertSessionHasNoErrors();
        $this->harvest($production, '4');
        $this->harvest($production, '6.5');
        $this->delete("/admin/produksi/vegetable/{$production->id}")->assertRedirect();

        $logs = ActivityLog::orderBy('id')->get();
        $this->assertSame(
            [ActivityAction::PlantingCreated, ActivityAction::PlantingUpdated, ActivityAction::HarvestRecorded, ActivityAction::HarvestUpdated, ActivityAction::ProductionDeleted],
            $logs->pluck('action')->all(),
        );
        $this->assertSame(['120 tanaman', '150 tanaman', '4 kg', '6,5 kg', '150 tanaman'], $logs->pluck('detail')->all());
        $this->assertTrue($logs->every(fn (ActivityLog $log) => $log->user_id === $this->staff->id
            && $log->farmer_group_id === $this->group->id
            && $log->sector === SectorType::Vegetable
            && $log->commodity_name === 'KANGKUNG'));
        // Data sudah dihapus, tetapi riwayatnya tetap terbaca.
        $this->assertNull($logs->last()->production_id);
    }

    public function test_processed_products_use_the_production_term(): void
    {
        $this->assertSame('Catat produksi', ActivityAction::HarvestRecorded->label(SectorType::ProcessedProduct));
        $this->assertSame('Catat panen', ActivityAction::HarvestRecorded->label(SectorType::Fruit));
    }

    public function test_admin_dashboard_shows_recent_activity_and_monthly_counts(): void
    {
        $this->actingAs($this->staff);
        $production = $this->plant();
        $this->harvest($production, '4');

        $this->actingAs($this->admin)->get('/admin')->assertOk()
            ->assertSee('Aktivitas terbaru')
            ->assertSeeInOrder(['Rina Kartika', 'Catat panen', 'Kangkung', '4 kg', 'Buruan SAE Binaan'])
            ->assertSee('Catat data tanam')
            ->assertSee(route('admin.productions.harvest.edit', ['vegetable', $production->id]), false);
    }

    public function test_activity_page_summarises_and_filters_per_staff(): void
    {
        $other = User::factory()->create(['name' => 'Budi Santoso']);

        $this->actingAs($this->staff);
        $this->harvest($this->plant(), '4');
        $this->plant();

        // Aktivitas bulan lalu tidak ikut periode bawaan (bulan berjalan).
        ActivityLog::forceCreate([
            'user_id' => $this->staff->id, 'action' => ActivityAction::PlantingCreated, 'sector' => SectorType::Vegetable,
            'commodity_name' => 'BAYAM', 'created_at' => '2026-05-20 10:00:00',
        ]);

        $this->actingAs($this->admin);

        $page = $this->get('/admin/aktivitas')->assertOk()
            ->assertSee('Ringkasan per petugas')->assertSee('Budi Santoso')->assertSee('3 aktivitas')->assertDontSee('Bayam');
        $summary = $page->viewData('summary')->keyBy('id');
        $this->assertSame([2, 1, 3], [
            (int) $summary[$this->staff->id]->planting_count,
            (int) $summary[$this->staff->id]->harvest_count,
            (int) $summary[$this->staff->id]->total_count,
        ]);
        $this->assertSame(0, (int) $summary[$other->id]->total_count, 'Staf aktif tanpa aktivitas tetap tampil.');

        $this->get('/admin/aktivitas?jenis=harvest_recorded')->assertOk()->assertSee('1 aktivitas');
        $this->get("/admin/aktivitas?petugas={$other->id}")->assertOk()->assertSee('0 aktivitas');
        $this->get('/admin/aktivitas?dari=2026-05-01&sampai=2026-05-31')->assertOk()->assertSee('1 aktivitas')->assertSee('Bayam');
        // Tanggal tidak valid diabaikan, bukan error.
        $this->get('/admin/aktivitas?dari=2026-02-31&sampai=kemarin')->assertOk()->assertSee('3 aktivitas');
    }

    public function test_activity_page_is_admin_only_and_recorders_cannot_be_deleted(): void
    {
        $this->actingAs($this->staff);
        $this->get('/admin/aktivitas')->assertForbidden();
        $production = $this->plant();
        $this->delete("/admin/produksi/vegetable/{$production->id}");

        // Datanya sudah dihapus, tetapi riwayatnya membuat akun tidak bisa dihapus.
        $this->actingAs($this->admin)->delete("/admin/pengguna/{$this->staff->id}")->assertSessionHas('error');
        $this->assertModelExists($this->staff);
    }

    public function test_nothing_is_logged_without_a_signed_in_user(): void
    {
        Production::factory()->for($this->group)->for($this->kangkung)->create();

        $this->assertSame(0, ActivityLog::count());
    }
}
