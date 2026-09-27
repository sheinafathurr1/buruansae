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
<section class="card flex min-w-0 flex-col" aria-labelledby="{{ $id }}-title" x-data="{ view: 'chart' }">
    <header class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 p-5">
        <div class="min-w-0">
            <h2 id="{{ $id }}-title" class="text-base font-bold text-slate-900">{{ $title }}</h2>
            <p class="mt-0.5 text-sm text-slate-500">
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
            <div class="flex rounded-xl bg-slate-100 p-1 text-xs font-semibold" role="tablist" aria-label="Tampilan data">
                <button type="button" role="tab" id="{{ $id }}-tab-chart" aria-controls="{{ $id }}-chart" :aria-selected="(view === 'chart').toString()" aria-selected="true"
                        @click="view = 'chart'" :class="view === 'chart' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-800'"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-slate-900 shadow-sm">
                    <x-heroicon-m-chart-bar class="size-4" aria-hidden="true" /> Grafik
                </button>
                <button type="button" role="tab" id="{{ $id }}-tab-table" aria-controls="{{ $id }}-table" :aria-selected="(view === 'table').toString()" aria-selected="false"
                        @click="view = 'table'" :class="view === 'table' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-800'"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-slate-600">
                    <x-heroicon-m-table-cells class="size-4" aria-hidden="true" /> Tabel
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
        <div id="{{ $id }}-table" role="tabpanel" aria-labelledby="{{ $id }}-tab-table" x-show="view === 'table'" x-cloak class="max-h-[32rem] overflow-auto" tabindex="0">
            <table class="table-base">
                <thead class="sticky top-0">
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
                        <tr class="hover:bg-slate-50">
                            <td class="num text-slate-400">{{ $loop->iteration }}</td>
                            <td class="font-medium text-slate-900">{{ display_name($row->name) }}</td>
                            <td class="num text-right font-semibold">{{ format_number($row->total) }}</td>
                            <td class="num text-right">{{ format_number($row->cycles, 0) }}</td>
                            <td class="text-right whitespace-nowrap">
                                @if ($drill === 'district')
                                    <a href="{{ $rowUrl($row) }}" class="text-sm font-semibold text-brand-700 hover:underline">
                                        Per kelurahan<span class="sr-only"> di Kecamatan {{ display_name($row->name) }}</span>
                                    </a>
                                @else
                                    <a href="{{ $rowUrl($row) }}" class="text-sm font-semibold text-brand-700 hover:underline"
                                       @click.prevent="$dispatch('open-detail', { url: @js($rowUrl($row)), title: @js($detailTitle.' — Kel. '.display_name($row->name)) })">
                                        Rincian<span class="sr-only"> Kelurahan {{ display_name($row->name) }}</span>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-semibold text-slate-900">
                        <td></td>
                        <td class="px-4 py-3">Total</td>
                        <td class="num px-4 py-3 text-right">{{ format_number($total) }}</td>
                        <td class="num px-4 py-3 text-right">{{ format_number($rows->sum('cycles'), 0) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</section>
