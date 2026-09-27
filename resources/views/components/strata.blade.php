@props(['end' => 'var(--color-paper)', 'height' => 'h-14 sm:h-20'])
{{--
    Lapisan gelombang air & tanah dari dasar emblem logo Buruan Saé, dipakai sebagai
    peralih antarbagian. Bagian atas transparan (memperlihatkan latar di atasnya);
    lapisan terakhir diisi warna $end, yaitu latar bagian di bawahnya.
--}}
@php
    $width = 1440;
    $bands = [
        // [garis dasar gelombang, amplitudo, fase, warna]
        [14, 7, 0.0, '#93d8f4'],
        [34, 8, 1.3, '#1b93cf'],
        [52, 7, 2.4, '#d38b2b'],
        [68, 6, 3.3, '#7a5c2e'],
        [80, 5, 4.2, $end],
    ];
    $wave = function (float $base, float $amp, float $phase) use ($width): string {
        $points = [];
        for ($i = 0; $i <= 48; $i++) {
            $x = $width * $i / 48;
            $y = $base + $amp * sin(2 * M_PI * 1.5 * $x / $width + $phase);
            $points[] = round($x, 1).','.round($y, 1);
        }

        return 'M'.implode(' L', $points).' L'.$width.',96 L0,96 Z';
    };
@endphp
<svg {{ $attributes->class(['block w-full', $height]) }} viewBox="0 0 {{ $width }} 96" preserveAspectRatio="none" aria-hidden="true" focusable="false">
    @foreach ($bands as [$base, $amp, $phase, $color])
        {{-- Celah putih tipis di atas setiap lapisan, seperti garis putih pada emblem. --}}
        <path d="{{ $wave($base - 3.5, $amp, $phase) }}" fill="#ffffff" />
        <path d="{{ $wave($base, $amp, $phase) }}" style="fill: {{ $color }}" />
    @endforeach
</svg>
