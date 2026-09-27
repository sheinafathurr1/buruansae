{{-- Tabel ringkas per wilayah (terlambat panen / akan panen). --}}
@php
    $rowUrl = fn ($row) => $areaLevel === 'district'
        ? route('sectors.show', ['sector' => $sector] + $filters->toQuery(['district' => $row->id]))
        : route('sectors.villages.pending', ['sector' => $sector, 'village' => $row->id] + $filters->toQuery());
    $slug = \Illuminate\Support\Str::slug($title);
@endphp
<section class="panel flex min-w-0 flex-col" aria-labelledby="{{ $slug }}-title">
    <header class="flex items-start gap-4 border-b border-rule p-5">
        <div class="min-w-0">
            <h2 id="{{ $slug }}-title" class="display text-xl leading-snug">{{ $title }}</h2>
            <p class="mt-0.5 text-sm text-ink-muted">{{ $subtitle }}</p>
        </div>
        @if ($rows->isNotEmpty())
            <p @class(['num ml-auto shrink-0 text-right text-sm leading-tight', 'text-soil' => $tone === 'critical', 'text-ink-soft' => $tone !== 'critical'])>
                <span class="figure-number block text-2xl">{{ format_number($rows->sum('cycles'), 0) }}</span> siklus
            </p>
        @endif
    </header>

    @if ($rows->isEmpty())
        <x-empty-state :title="$emptyTitle" icon="heroicon-o-check-circle" compact class="flex-1" />
    @else
        <div class="max-h-96 overflow-auto px-5" tabindex="0" role="region" aria-label="Tabel {{ strtolower($title) }}">
            <table class="data-table">
                <thead class="sticky top-0 bg-white">
                    <tr>
                        <th scope="col">{{ $areaLevel === 'district' ? 'Kecamatan' : 'Kelurahan' }}</th>
                        <th scope="col" class="text-right">Perkiraan ({{ $unit }})</th>
                        <th scope="col" class="text-right">Siklus</th>
                        <th scope="col" class="text-right">{{ $dateLabel }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr class="hover:bg-paper">
                            <td class="font-medium">
                                @if ($areaLevel === 'district')
                                    <a href="{{ $rowUrl($row) }}" class="text-ink underline decoration-rule underline-offset-4 hover:decoration-ink">{{ display_name($row->name) }}</a>
                                @else
                                    <a href="{{ $rowUrl($row) }}" class="text-ink underline decoration-rule underline-offset-4 hover:decoration-ink"
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
