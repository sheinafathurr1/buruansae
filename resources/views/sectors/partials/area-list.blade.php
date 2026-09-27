{{-- Tabel ringkas per wilayah (terlambat panen / akan panen). --}}
@php
    $rowUrl = fn ($row) => $areaLevel === 'district'
        ? route('sectors.show', ['sector' => $sector] + $filters->toQuery(['district' => $row->id]))
        : route('sectors.villages.pending', ['sector' => $sector, 'village' => $row->id] + $filters->toQuery());
    $iconTone = $tone === 'critical' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-600';
    $slug = \Illuminate\Support\Str::slug($title);
@endphp
<section class="card flex min-w-0 flex-col" aria-labelledby="{{ $slug }}-title">
    <header class="flex items-start gap-3 border-b border-slate-100 p-5">
        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl {{ $iconTone }}">
            <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-5" aria-hidden="true" />
        </span>
        <div class="min-w-0">
            <h2 id="{{ $slug }}-title" class="text-base font-bold text-slate-900">{{ $title }}</h2>
            <p class="mt-0.5 text-sm text-slate-500">{{ $subtitle }}</p>
        </div>
        @if ($rows->isNotEmpty())
            <span @class(['num ml-auto shrink-0 rounded-full px-2.5 py-1 text-xs font-bold', 'bg-red-50 text-red-700' => $tone === 'critical', 'bg-slate-100 text-slate-700' => $tone !== 'critical'])>
                {{ format_number($rows->sum('cycles'), 0) }} siklus
            </span>
        @endif
    </header>

    @if ($rows->isEmpty())
        <x-empty-state :title="$emptyTitle" icon="heroicon-o-check-circle" compact class="flex-1" />
    @else
        <div class="max-h-96 overflow-auto" tabindex="0" role="region" aria-label="Tabel {{ strtolower($title) }}">
            <table class="table-base">
                <thead class="sticky top-0">
                    <tr>
                        <th scope="col">{{ $areaLevel === 'district' ? 'Kecamatan' : 'Kelurahan' }}</th>
                        <th scope="col" class="text-right">Perkiraan ({{ $unit }})</th>
                        <th scope="col" class="text-right">Siklus</th>
                        <th scope="col" class="text-right">{{ $dateLabel }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="font-medium">
                                @if ($areaLevel === 'district')
                                    <a href="{{ $rowUrl($row) }}" class="text-slate-900 hover:text-brand-700 hover:underline">{{ display_name($row->name) }}</a>
                                @else
                                    <a href="{{ $rowUrl($row) }}" class="text-slate-900 hover:text-brand-700 hover:underline"
                                       @click.prevent="$dispatch('open-detail', { url: @js($rowUrl($row)), title: @js('Rincian belum panen — Kel. '.display_name($row->name)) })">{{ display_name($row->name) }}</a>
                                @endif
                            </td>
                            <td class="num text-right font-semibold">{{ format_number($row->total) }}</td>
                            <td class="num text-right">{{ format_number($row->cycles, 0) }}</td>
                            <td class="text-right whitespace-nowrap">{{ format_date($row->date) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
