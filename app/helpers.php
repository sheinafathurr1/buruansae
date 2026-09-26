<?php

use App\Support\Format;

if (! function_exists('format_number')) {
    function format_number(float|int|string|null $value, int $maxDecimals = 2): string
    {
        return Format::number($value, $maxDecimals);
    }
}

if (! function_exists('format_quantity')) {
    function format_quantity(float|int|string|null $value, ?string $unit, int $maxDecimals = 2): string
    {
        return Format::quantity($value, $unit, $maxDecimals);
    }
}

if (! function_exists('format_rupiah')) {
    function format_rupiah(float|int|string|null $value): string
    {
        return Format::rupiah($value);
    }
}

if (! function_exists('format_date')) {
    function format_date(DateTimeInterface|string|null $date, string $format = 'j M Y'): string
    {
        return Format::date($date, $format);
    }
}

if (! function_exists('display_name')) {
    /** Nama wilayah/komoditas yang tersimpan HURUF BESAR → "Babakan Ciparay". */
    function display_name(?string $value): string
    {
        return Format::name($value);
    }
}
