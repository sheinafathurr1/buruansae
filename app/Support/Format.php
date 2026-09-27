<?php

namespace App\Support;

use DateTimeInterface;
use Illuminate\Support\Carbon;

/**
 * Format angka & tanggal gaya Indonesia (1.234,5 · Rp15.000 · 26 Sep 2026).
 * Sengaja tanpa ekstensi intl supaya aman di shared hosting.
 */
final class Format
{
    public static function number(float|int|string|null $value, int $maxDecimals = 2): string
    {
        $value = (float) ($value ?? 0);
        $formatted = number_format($value, $maxDecimals, ',', '.');

        if ($maxDecimals > 0) {
            $formatted = rtrim(rtrim($formatted, '0'), ',');
        }

        return $formatted === '-0' ? '0' : $formatted;
    }

    public static function quantity(float|int|string|null $value, ?string $unit, int $maxDecimals = 2): string
    {
        return trim(self::number($value, $maxDecimals).' '.($unit ?? ''));
    }

    public static function rupiah(float|int|string|null $value): string
    {
        return 'Rp'.number_format((float) ($value ?? 0), 0, ',', '.');
    }

    public static function name(?string $value): string
    {
        $value = trim((string) $value);

        return $value === '' ? '' : mb_convert_case(mb_strtolower($value), MB_CASE_TITLE, 'UTF-8');
    }

    public static function date(DateTimeInterface|string|null $date, string $format = 'j M Y'): string
    {
        if ($date === null || $date === '') {
            return '–';
        }

        return Carbon::parse($date)->locale('id')->translatedFormat($format);
    }
}
