{{-- Rincian siklus yang belum dipanen di satu kelurahan. --}}
@php($today = today())
<div class="space-y-4 p-4 sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-ink-soft">
        <p>{{ format_number($productions->total(), 0) }} siklus belum dipanen di Kel. {{ display_name($village->name) }}</p>
        @if ($averageGrowingDays)
            <p class="text-sm text-ink-soft">
                Perkiraan lama masa tanam ± {{ format_number(abs($averageGrowingDays), 0) }} hari
            </p>
        @endif
    </div>

    @if ($productions->isEmpty())
        <x-empty-state title="Tidak ada data" icon="heroicon-o-inbox">Semua siklus di kelurahan ini sudah dipanen.</x-empty-state>
    @else
        <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel siklus belum dipanen">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Kelompok</th>
                        <th scope="col">Komoditas</th>
                        <th scope="col">{{ $sector->startDateLabel() }}</th>
                        @if ($sector->initialQuantityUnit())
                            <th scope="col" class="text-right">Jumlah awal</th>
                        @endif
                        <th scope="col" class="text-right">Perkiraan ({{ $unit }})</th>
                        <th scope="col">Perkiraan panen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productions as $production)
                        @php($estimated = $production->estimated_harvest_date)
                        <tr>
                            <td class="font-medium text-ink">
                                {{ $production->farmerGroup->name }}
                                @if ($production->farmerGroup->rw)<span class="block text-xs font-normal text-ink-muted">RW {{ str_pad((string) $production->farmerGroup->rw, 2, '0', STR_PAD_LEFT) }}</span>@endif
                            </td>
                            <td>{{ display_name($production->commodity->name) }}</td>
                            <td class="whitespace-nowrap">{{ format_date($production->start_date) }}</td>
                            @if ($sector->initialQuantityUnit())
                                <td class="num text-right whitespace-nowrap">{{ $production->initial_quantity !== null ? format_quantity($production->initial_quantity, $sector->initialQuantityUnit(), 0) : '–' }}</td>
                            @endif
                            <td class="num text-right">{{ $production->estimated_harvest_quantity !== null ? format_number($production->estimated_harvest_quantity) : '–' }}</td>
                            <td class="whitespace-nowrap">
                                {{ format_date($estimated) }}
                                @if ($estimated && $estimated->lt($today))
                                    <span class="ml-1 text-xs font-semibold text-soil">Terlambat</span>
                                @elseif ($estimated && $estimated->lte($today->copy()->addDays(7)))
                                    <span class="ml-1 text-xs font-semibold text-water-deep">Segera</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{ $productions->links('partials.pagination') }}
</div>
