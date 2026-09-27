{{--
    Kartu rincian per wilayah: grafik batang + tampilan tabel (aksesibel).
    Per kecamatan: klik → filter kecamatan tsb. Per kelurahan: klik → modal rincian kelompok.
--}}
@php
    $drill = $areaLevel;
    $rowUrl = fn ($row) => $drill === 'district'
        ? route('sectors.show', ['sector' => $sector] + $filters->toQuery(['district' => $row->id]))
        : route('sectors.villages.'.$kind, ['sector' => $sector, 'village' => $row->id] + $filters->toQuery());
    $detailTitle = $kind === 'harvested' ? 'Rincian hasil '.strtolower($sector->harvestTerm()) : 'Rincian belum panen';
    $chart = [
        'label' => $datasetLabel,
        'labels' => $rows->map(fn ($row) => display_name($row->name))->values(),
        'values' => $rows->pluck('total')->values(),
        'urls' => $rows->map($rowUrl)->values(),
        'color' => $color,
        'hoverColor' => $hoverColor,
        'unit' => $unit,
        'drill' => $drill,
        'detailTitle' => $detailTitle,
    ];
    $top = $rows->first();
    $total = $rows->sum('total');
    $id = 'breakdown-'.$kind;
@endphp
<section class="panel flex min-w-0 flex-col" aria-labelledby="{{ $id }}-title" x-data="{ view: 'chart' }">
    <header class="flex flex-wrap items-end justify-between gap-x-4 gap-y-3 border-b border-rule px-5 pt-5">
        <div class="min-w-0 pb-4">
            <h2 id="{{ $id }}-title" class="display text-xl leading-snug">{{ $title }}</h2>
            <p class="mt-0.5 text-sm text-ink-muted">
                @if ($rows->isEmpty())
                    Tidak ada data.
                @elseif ($drill === 'district')
                    Pilih kecamatan untuk melihat rincian per kelurahan.
                @else
                    Pilih kelurahan untuk melihat rincian per kelompok.
                @endif
            </p>
        </div>
        @if ($rows->isNotEmpty())
            <div class="-mb-px flex gap-4 text-sm" role="tablist" aria-label="Tampilan data">
                <button type="button" role="tab" id="{{ $id }}-tab-chart" aria-controls="{{ $id }}-chart" :aria-selected="(view === 'chart').toString()" aria-selected="true"
                        @click="view = 'chart'" :class="view === 'chart' ? 'border-brand-800 font-semibold text-ink' : 'border-transparent text-ink-muted hover:text-ink'"
                        class="border-b-[3px] border-brand-800 pb-3 font-semibold text-ink">
                    Grafik
                </button>
                <button type="button" role="tab" id="{{ $id }}-tab-table" aria-controls="{{ $id }}-table" :aria-selected="(view === 'table').toString()" aria-selected="false"
                        @click="view = 'table'" :class="view === 'table' ? 'border-brand-800 font-semibold text-ink' : 'border-transparent text-ink-muted hover:text-ink'"
                        class="border-b-[3px] border-transparent pb-3 text-ink-muted">
                    Tabel
                </button>
            </div>
        @endif
    </header>

    @if ($rows->isEmpty())
        <x-empty-state :title="$emptyTitle" icon="heroicon-o-chart-bar" class="flex-1">{{ $emptyText }}</x-empty-state>
    @else
        <div id="{{ $id }}-chart" role="tabpanel" aria-labelledby="{{ $id }}-tab-chart" x-show="view === 'chart'" class="p-4 sm:p-5">
            <div class="relative" style="height: {{ max(180, $rows->count() * 30 + 56) }}px">
                <canvas data-area-chart="{{ json_encode($chart, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}" role="img"
                        aria-label="Grafik batang {{ strtolower($title) }}. Tertinggi {{ display_name($top->name) }}: {{ format_quantity($top->total, $unit) }}. Total {{ format_quantity($total, $unit) }}. Lihat tab Tabel untuk semua angka."></canvas>
            </div>
        </div>
        <div id="{{ $id }}-table" role="tabpanel" aria-labelledby="{{ $id }}-tab-table" x-show="view === 'table'" x-cloak class="max-h-[32rem] overflow-auto px-5" tabindex="0">
            <table class="data-table">
                <thead class="sticky top-0 bg-white">
                    <tr>
                        <th scope="col" class="w-10">#</th>
                        <th scope="col">{{ $drill === 'district' ? 'Kecamatan' : 'Kelurahan' }}</th>
                        <th scope="col" class="text-right">Jumlah ({{ $unit }})</th>
                        <th scope="col" class="text-right">Siklus</th>
                        <th scope="col"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr class="hover:bg-paper">
                            <td class="num text-ink-muted">{{ $loop->iteration }}</td>
                            <td class="font-medium">{{ display_name($row->name) }}</td>
                            <td class="num text-right font-semibold">{{ format_number($row->total) }}</td>
                            <td class="num text-right">{{ format_number($row->cycles, 0) }}</td>
                            <td class="text-right whitespace-nowrap">
                                @if ($drill === 'district')
                                    <a href="{{ $rowUrl($row) }}" class="link text-sm">
                                        Per kelurahan<span class="sr-only"> di Kecamatan {{ display_name($row->name) }}</span>
                                    </a>
                                @else
                                    <a href="{{ $rowUrl($row) }}" class="link text-sm"
                                       @click.prevent="$dispatch('open-detail', { url: @js($rowUrl($row)), title: @js($detailTitle.' — Kel. '.display_name($row->name)) })">
                                        Rincian<span class="sr-only"> Kelurahan {{ display_name($row->name) }}</span>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-ink/70 font-semibold text-ink">
                        <td></td>
                        <td class="px-3 py-3">Total</td>
                        <td class="num px-3 py-3 text-right">{{ format_number($total) }}</td>
                        <td class="num px-3 py-3 text-right">{{ format_number($rows->sum('cycles'), 0) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</section>
