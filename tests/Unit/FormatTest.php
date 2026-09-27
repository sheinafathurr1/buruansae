<?php

namespace Tests\Unit;

use App\Support\Format;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FormatTest extends TestCase
{
    #[DataProvider('numbers')]
    public function test_number_uses_indonesian_separators(mixed $value, int $decimals, string $expected): void
    {
        $this->assertSame($expected, Format::number($value, $decimals));
    }

    public static function numbers(): array
    {
        return [
            'thousands' => [1234567, 2, '1.234.567'],
            'decimal trimmed' => [1234.5, 2, '1.234,5'],
            'rounded' => [0.125, 2, '0,13'],
            'decimal string from db' => ['22236.349', 2, '22.236,35'],
            'null' => [null, 2, '0'],
            'no decimals' => [99.9, 0, '100'],
            'negative zero' => [-0.001, 2, '0'],
        ];
    }

    public function test_quantity_and_rupiah(): void
    {
        $this->assertSame('12,5 kg', Format::quantity(12.5, 'kg'));
        $this->assertSame('3', Format::quantity(3, null));
        $this->assertSame('Rp15.000', Format::rupiah(15000));
    }

    public function test_name_is_title_cased(): void
    {
        $this->assertSame('Babakan Ciparay', Format::name('BABAKAN CIPARAY'));
        $this->assertSame('', Format::name(null));
    }
}
