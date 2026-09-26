<?php

namespace Tests\Feature;

use App\Enums\SectorType;
use App\Models\Sector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SectorTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_sector_page_maps_to_a_seeded_sector(): void
    {
        foreach (SectorType::cases() as $type) {
            $this->assertTrue(Sector::query()->where('code', $type->code())->exists(), "Sektor {$type->code()} belum ada di SectorSeeder");
            $this->assertSame($type, SectorType::fromCode($type->code()));
            $this->assertFileExists(public_path($type->image()));
        }

        $this->assertCount(Sector::count(), SectorType::cases());
    }

    public function test_legacy_urls_are_kept(): void
    {
        $this->assertSame(
            ['vegetable', 'medicalplant', 'fruit', 'livestock', 'fish', 'processed-products', 'waste-processing', 'nursery'],
            array_column(SectorType::cases(), 'value'),
        );
    }
}
