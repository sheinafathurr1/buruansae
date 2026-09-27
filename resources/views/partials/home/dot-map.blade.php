{{-- Peta titik: satu titik per kelurahan yang punya kelompok, besar titik = jumlah kelompok. --}}
@php
    $pts = collect($points);
    $minLat = $pts->min('lat'); $maxLat = $pts->max('lat'); $minLng = $pts->min('lng'); $maxLng = $pts->max('lng');
    // Rentang minimum ±0,01° supaya satu titik saja tetap bisa digambar.
    $padLng = max(($maxLng - $minLng) * 0.04, 0.01); $padLat = max(($maxLat - $minLat) * 0.06, 0.01);
    $minLng -= $padLng; $maxLng += $padLng; $minLat -= $padLat; $maxLat += $padLat;
    $W = 600; $H = round($W * ($maxLat - $minLat) / max(0.0001, $maxLng - $minLng));
    $px = fn ($lng) => round(($lng - $minLng) / ($maxLng - $minLng) * $W, 1);
    $py = fn ($lat) => round(($maxLat - $lat) / ($maxLat - $minLat) * $H, 1);
@endphp
<svg viewBox="0 0 {{ $W }} {{ $H }}" class="block h-auto w-full" role="img"
     aria-label="Peta titik sebaran kelompok: {{ $pts->count() }} kelurahan di Kota Bandung punya kelompok Buruan SAE, paling banyak {{ $pts->max('groups') }} kelompok di satu kelurahan.">
    @foreach ($pts->sortByDesc('groups') as $p)
        <circle cx="{{ $px($p['lng']) }}" cy="{{ $py($p['lat']) }}" r="{{ round(3 + 2.2 * sqrt($p['groups']), 1) }}" fill="#2e8b45" fill-opacity="0.85" stroke="#fff" stroke-width="2" />
    @endforeach
</svg>
