{{--
    Hasil panen tercatat per bulan (kg): satu seri, kolom dengan ujung atas membulat
    4px, celah 2px, garis bantu tipis. Dibuat dengan HTML (bukan SVG) supaya label
    tetap terbaca di layar kecil. Arahkan kursor/ketuk kolom untuk melihat angkanya;
    tabel lengkap ada di bawah grafik.
--}}
@php
    $series = collect($months)->values();
    $max = max(1, (float) $series->max('total'));
    $step = collect([100, 200, 250, 500, 1000, 2000, 2500, 5000, 10000, 20000])->first(fn ($s) => $max / $s <= 4) ?? 50000;
    $yMax = ceil($max / $step) * $step;
    $label = fn (string $month) => \Illuminate\Support\Carbon::parse($month.'-01')->translatedFormat('F Y');
    $peakIndex = (int) $series->search(fn ($m) => $m['total'] === $series->max('total'));
    $readouts = $series->map(fn ($m) => ['label' => $label($m['month']), 'value' => format_number($m['total'], 0).' kg']);
@endphp
<figure x-data="{ i: {{ $peakIndex }}, peak: {{ $peakIndex }}, r: @js($readouts) }" class="min-w-0">
    <p class="flex flex-wrap items-baseline gap-x-2 text-sm text-ink-soft" aria-live="polite">
        <span x-text="i === peak ? 'Puncak:' : 'Bulan:'">Puncak:</span>
        <span class="font-semibold text-ink" x-text="r[i].label">{{ $readouts[$peakIndex]['label'] }}</span>
        <span class="figure-number text-lg text-ink" x-text="r[i].value">{{ $readouts[$peakIndex]['value'] }}</span>
    </p>

    <div class="mt-3" role="img" @mouseleave="i = peak"
         aria-label="Grafik kolom hasil panen tercatat per bulan, {{ $label($series->first()['month']) }} sampai {{ $label($series->last()['month']) }}. Tertinggi {{ $readouts[$peakIndex]['label'] }}: {{ $readouts[$peakIndex]['value'] }}. Angka lengkap ada di tabel di bawah grafik.">
        <div class="relative h-52 pl-11 sm:h-60">
            @for ($t = 0; $t <= $yMax; $t += $step)
                <div @class(['absolute right-0 left-11 border-t', 'border-[#b8b0a0]' => $t === 0, 'border-[#ece6d8]' => $t > 0]) style="bottom: {{ $t / $yMax * 100 }}%" aria-hidden="true">
                    <span class="absolute -left-11 w-9 -translate-y-1/2 text-right text-[11px] text-ink-muted">{{ format_number($t, 0) }}</span>
                </div>
            @endfor
            <div class="absolute inset-y-0 right-0 left-11 flex items-end gap-[2px]" aria-hidden="true">
                @foreach ($series as $n => $m)
                    <div class="flex h-full min-w-0 flex-1 cursor-default items-end justify-center" @mouseenter="i = {{ $n }}" @click="i = {{ $n }}">
                        <div class="w-full max-w-6 rounded-t-[4px] transition-colors" style="height: {{ $m['total'] / $yMax * 100 }}%"
                             :class="i === {{ $n }} ? 'bg-leaf-700' : 'bg-leaf-600'" class="bg-leaf-600"></div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="ml-11 flex gap-[2px] pt-1.5 text-[11px] leading-4" aria-hidden="true">
            @foreach ($series as $n => $m)
                @php($monthNo = (int) substr($m['month'], 5, 2))
                <div class="relative flex-1">
                    @if (in_array($monthNo, [1, 4, 7, 10], true))
                        <span class="absolute left-1/2 -translate-x-1/2 whitespace-nowrap text-ink-muted">{{ \Illuminate\Support\Carbon::parse($m['month'].'-01')->translatedFormat('M') }}</span>
                    @endif
                    @if ($monthNo === 1)
                        <span class="absolute top-4 left-1/2 -translate-x-1/2 font-semibold text-ink">{{ substr($m['month'], 0, 4) }}</span>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="h-9"></div>
    </div>

    <figcaption class="text-xs leading-5 text-ink-muted">
        Hasil panen sektor bersatuan kilogram, menurut bulan panen. Angka mengikuti laporan kelompok yang masuk ke {{ config('buruansae.agency_short') }}.
    </figcaption>
    <details class="mt-3 text-sm">
        <summary class="cursor-pointer font-semibold text-leaf-700">Lihat tabel</summary>
        <div class="mt-2 max-h-64 overflow-y-auto" tabindex="0" role="region" aria-label="Tabel panen per bulan">
            <table class="data-table">
                <thead class="sticky top-0 bg-white"><tr><th scope="col">Bulan</th><th scope="col" class="text-right">Hasil panen (kg)</th></tr></thead>
                <tbody>
                    @foreach ($series->reverse() as $m)
                        <tr><td>{{ $label($m['month']) }}</td><td class="num text-right">{{ format_number($m['total'], 0) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>
</figure>
