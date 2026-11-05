<?php

namespace Tests\Feature;

use App\Models\FarmerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** php artisan buruansae:bersihkan-kelompok */
class CleanGroupPlaceholdersTest extends TestCase
{
    use RefreshDatabase;

    private function legacyGroup(): FarmerGroup
    {
        return FarmerGroup::factory()->create([
            'name' => 'Jimbaran',
            'leader_name' => '0',
            'phone' => ' 00 ',
            'land_status' => '-',
            'description_url' => '0',
            'land_area_m2' => 0,
        ]);
    }

    public function test_placeholders_are_emptied_and_real_values_kept(): void
    {
        $legacy = $this->legacyGroup();
        $legacy->deleteQuietly();
        $spaced = FarmerGroup::factory()->create(['description_url' => ' https://buruansae.bandung.go.id/kelompok ']);
        $real = FarmerGroup::factory()->create([
            'leader_name' => 'Ibu Euis',
            'phone' => '0812-3456-7890',
            'land_status' => 'Pribadi',
            'description_url' => 'https://buruansae.bandung.go.id/walagri',
            'land_area_m2' => 120.5,
        ]);
        $updatedAt = FarmerGroup::withTrashed()->find($legacy->id)->updated_at;

        $this->travel(1)->day();
        $this->artisan('buruansae:bersihkan-kelompok', ['--force' => true])
            ->expectsOutput('Selesai: 2 kelompok dibersihkan.')
            ->assertSuccessful();

        $legacy = FarmerGroup::withTrashed()->find($legacy->id);
        foreach (['leader_name', 'phone', 'land_status', 'description_url', 'land_area_m2'] as $column) {
            $this->assertNull($legacy->{$column}, "{$column} seharusnya kosong.");
        }
        $this->assertSame('Jimbaran', $legacy->name);
        $this->assertEquals($updatedAt, $legacy->updated_at, 'Pembersihan bukan isian baru.');

        $this->assertSame('https://buruansae.bandung.go.id/kelompok', $spaced->fresh()->description_url);

        $real->refresh();
        $this->assertSame(
            ['Ibu Euis', '0812-3456-7890', 'Pribadi', 'https://buruansae.bandung.go.id/walagri', 120.5],
            [$real->leader_name, $real->phone, $real->land_status, $real->description_url, (float) $real->land_area_m2],
        );

        $this->artisan('buruansae:bersihkan-kelompok', ['--force' => true])
            ->expectsOutput('Tidak ada isian yang perlu dibersihkan.')
            ->assertSuccessful();
    }

    public function test_dry_run_and_declined_confirmation_change_nothing(): void
    {
        $group = $this->legacyGroup();

        $this->artisan('buruansae:bersihkan-kelompok', ['--dry-run' => true])
            ->expectsOutput('1 kelompok akan diperbarui.')
            ->expectsOutput('Mode --dry-run: tidak ada data yang diubah.')
            ->assertSuccessful();

        $this->artisan('buruansae:bersihkan-kelompok')
            ->expectsConfirmation('Lanjutkan membersihkan?', 'no')
            ->assertFailed();

        $this->assertSame('0', $group->fresh()->leader_name);
    }

    public function test_cleaned_legacy_group_can_be_saved_from_the_dashboard(): void
    {
        $group = $this->legacyGroup();
        $this->actingAs(User::factory()->admin()->create());

        $form = fn () => [
            'name' => $group->name,
            'village_id' => $group->village_id,
            'leader_name' => $group->fresh()->leader_name,
            'phone' => $group->fresh()->phone,
            'land_status' => $group->fresh()->land_status,
            'description_url' => $group->fresh()->description_url,
        ];

        $this->put("/admin/kelompok/{$group->id}", $form())->assertSessionHasErrors('description_url');

        $this->artisan('buruansae:bersihkan-kelompok', ['--force' => true])->assertSuccessful();

        $this->put("/admin/kelompok/{$group->id}", $form())->assertSessionHasNoErrors();
    }
}
