{{--
    Penyaluran hasil: konsumsi pribadi / dibagikan / dijual.
    $compact = true → versi ringkas di baris ringkasan (sektor tanpa perkiraan panen).
--}}
@use('App\Enums\DistributionGroup')
@php
    $colors = [
        DistributionGroup::SelfConsumption->value => '#2a78d6',
        DistributionGroup::Shared->value => '#eb6834',
        DistributionGroup::Sold->value => '#1baf7a',
    ];
    $groups = collect(DistributionGroup::cases())->map(function (DistributionGroup $group) use ($colors, $distribution) {
        $rows = $distribution->filter(fn ($row) => $row->group === $group)->values();

        return (object) [
            'group' => $group,
            'color' => $colors[$group->value],
            'rows' => $rows,
            'quantity' => $rows->sum('quantity'),
            'households' => $rows->sum('households'),
            'persons' => $rows->sum('persons'),
        ];
    });
    $grandTotal = $groups->sum('quantity');
    $shared = $groups->firstWhere('group', DistributionGroup::Shared);
    $percent = fn ($value) => $grandTotal > 0 ? $value / $grandTotal * 100 : 0;
@endphp
<section @class(['card min-w-0', 'p-6' => $compact, 'grid gap-8 p-5 sm:p-6 lg:grid-cols-5' => ! $compact]) aria-labelledby="penyaluran-title">
    <div @class(['lg:col-span-2' => ! $compact])>
        <h2 id="penyaluran-title" class="text-base font-bold text-slate-900">Penyaluran hasil</h2>
        <p class="mt-0.5 text-sm text-slate-500">Ke mana hasil {{ strtolower($sector->harvestTerm()) }} disalurkan oleh kelompok.</p>

        @if ($grandTotal <= 0)
            <x-empty-state title="Belum ada data penyaluran" icon="heroicon-o-arrows-right-left" compact>
                Data penyaluran tercatat setelah hasil {{ strtolower($sector->harvestTerm()) }} dilaporkan.
            </x-empty-state>
        @else
            {{-- Batang bertumpuk: celah 2px memisahkan segmen, label ada di legenda. --}}
            <div class="mt-5 flex h-3 w-full gap-0.5 overflow-hidden rounded-full" role="img"
                 aria-label="{{ $groups->filter(fn ($g) => $g->quantity > 0)->map(fn ($g) => $g->group->label().' '.format_number($percent($g->quantity), 0).' persen')->implode(', ') }}">
                @foreach ($groups as $group)
                    @if ($group->quantity > 0)
                        <span class="h-full first:rounded-l-full last:rounded-r-full" style="width: {{ $percent($group->quantity) }}%; background: {{ $group->color }}"></span>
                    @endif
                @endforeach
            </div>

            <ul class="mt-5 space-y-3">
                @foreach ($groups as $group)
                    <li class="flex items-center gap-3 text-sm">
                        <span class="size-3 shrink-0 rounded-sm" style="background: {{ $group->color }}" aria-hidden="true"></span>
                        <span class="flex-1 text-slate-700">{{ $group->group->label() }}</span>
                        <span class="num font-semibold text-slate-900">{{ format_quantity($group->quantity, $unit) }}</span>
                        <span class="num w-12 text-right text-slate-500">{{ format_number($percent($group->quantity), 0) }}%</span>
                    </li>
                @endforeach
            </ul>

            @if ($shared->persons > 0 || $shared->households > 0)
                <p class="mt-5 flex items-start gap-2 rounded-xl bg-orange-50 p-3 text-sm text-orange-900">
                    <x-heroicon-o-heart class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                    <span>Hasil yang dibagikan menjangkau <strong>{{ format_number($shared->persons, 0) }} orang</strong>{{ $shared->households > 0 ? ' dari '.format_number($shared->households, 0).' kepala keluarga' : '' }}.</span>
                </p>
            @endif
        @endif
    </div>

    @if (! $compact && $grandTotal > 0)
        <div class="min-w-0 lg:col-span-3">
            <div class="overflow-x-auto rounded-xl border border-slate-200" tabindex="0" role="region" aria-label="Tabel rincian penyaluran">
                <table class="table-base">
                    <caption class="sr-only">Rincian penyaluran per kategori penerima</caption>
                    <thead>
                        <tr>
                            <th scope="col">Kategori penerima</th>
                            <th scope="col" class="text-right">Jumlah ({{ $unit }})</th>
                            <th scope="col" class="text-right">KK</th>
                            <th scope="col" class="text-right">Orang</th>
                        </tr>
                    </thead>
                    @foreach ($groups->filter(fn ($g) => $g->rows->isNotEmpty()) as $group)
                        <tbody>
                            <tr>
                                <th scope="rowgroup" colspan="4" class="border-t border-slate-100 bg-slate-50/60 px-4 py-2 text-left text-xs font-bold tracking-wide text-slate-600 uppercase">
                                    <span class="mr-1.5 inline-block size-2.5 rounded-sm align-middle" style="background: {{ $group->color }}" aria-hidden="true"></span>{{ $group->group->label() }}
                                </th>
                            </tr>
                            @foreach ($group->rows as $row)
                                <tr>
                                    <th scope="row" class="border-t border-slate-100 px-4 py-2.5 text-left font-medium text-slate-700">{{ $row->name }}</th>
                                    <td class="num py-2.5 text-right">{{ format_number($row->quantity) }}</td>
                                    <td class="num py-2.5 text-right">{{ $row->households ? format_number($row->households, 0) : '–' }}</td>
                                    <td class="num py-2.5 text-right">{{ $row->persons ? format_number($row->persons, 0) : '–' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endforeach
                </table>
            </div>
        </div>
    @endif
</section>
