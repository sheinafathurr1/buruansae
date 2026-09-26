@use('App\Enums\DistributionGroup')
@use('App\Enums\SectorType')
{{-- Rincian hasil panen/produksi satu kelurahan: daftar siklus + penyalurannya. --}}
<div class="space-y-3 p-4 sm:p-6">
    <p class="text-sm text-slate-600">
        {{ format_number($productions->total(), 0) }} catatan {{ strtolower($sector->harvestTerm()) }}
        di Kel. {{ display_name($village->name) }}
        @if ($filters->hasDateRange()) pada periode yang dipilih @endif
    </p>

    @forelse ($productions as $production)
        @php
            $byGroup = $production->distributions
                ->sortBy(fn ($d) => $d->recipientCategory->sort_order)
                ->groupBy(fn ($d) => $d->recipientCategory->group()->value);
            $detail = $production->processedProductDetail;
            $hasMore = $production->distributions->isNotEmpty() || $detail || $production->seedlingDetail || $production->selling_price;
        @endphp
        <article class="rounded-2xl border border-slate-200 p-4" x-data="{ expanded: false }">
            <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-2">
                <div class="min-w-0">
                    <h3 class="font-semibold text-slate-900">{{ $production->farmerGroup->name }}</h3>
                    <p class="mt-0.5 text-xs text-slate-500">
                        @if ($production->farmerGroup->rw) RW {{ str_pad((string) $production->farmerGroup->rw, 2, '0', STR_PAD_LEFT) }} · @endif
                        {{ display_name($production->commodity->name) }}
                        @if ($production->planting_category) · {{ $production->planting_category->label() }} @endif
                    </p>
                </div>
                <div class="text-right">
                    <p class="num font-bold text-slate-900">{{ format_quantity($production->harvest_quantity, $unit) }}</p>
                    @if ($production->harvest_head_count)
                        <p class="num text-xs text-slate-500">{{ format_number($production->harvest_head_count, 0) }} ekor</p>
                    @endif
                    <p class="text-xs text-slate-500">{{ $sector->harvestTerm() }} {{ format_date($production->harvest_date) }}</p>
                </div>
            </div>

            @if ($hasMore)
                <button type="button" @click="expanded = !expanded" :aria-expanded="expanded.toString()" aria-expanded="false"
                        class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:text-brand-800">
                    <x-heroicon-m-chevron-down class="size-4 transition" ::class="expanded && 'rotate-180'" aria-hidden="true" />
                    <span x-text="expanded ? 'Sembunyikan rincian' : 'Lihat penyaluran & rincian'">Lihat penyaluran &amp; rincian</span>
                </button>
                <div x-show="expanded" x-collapse x-cloak>
                    <div class="mt-3 space-y-3">
                        @if ($production->distributions->isNotEmpty())
                            <div class="overflow-x-auto rounded-xl border border-slate-200" tabindex="0" role="region" aria-label="Tabel penyaluran {{ $production->farmerGroup->name }}">
                                <table class="table-base">
                                    <thead>
                                        <tr>
                                            <th scope="col">Penyaluran</th>
                                            <th scope="col" class="text-right">Jumlah ({{ $unit }})</th>
                                            <th scope="col" class="text-right">KK</th>
                                            <th scope="col" class="text-right">Orang</th>
                                        </tr>
                                    </thead>
                                    @foreach (DistributionGroup::cases() as $group)
                                        @continue(! $byGroup->has($group->value))
                                        <tbody>
                                            <tr>
                                                <th scope="rowgroup" colspan="4" class="border-t border-slate-100 bg-slate-50/60 px-4 py-1.5 text-left text-[11px] font-bold tracking-wide text-slate-500 uppercase">{{ $group->label() }}</th>
                                            </tr>
                                            @foreach ($byGroup[$group->value] as $distribution)
                                                <tr>
                                                    <th scope="row" class="border-t border-slate-100 px-4 py-2 text-left font-medium text-slate-700">{{ $distribution->recipientCategory->name }}</th>
                                                    <td class="num py-2 text-right">{{ $distribution->quantity !== null ? format_number($distribution->quantity) : '–' }}</td>
                                                    <td class="num py-2 text-right">{{ $distribution->household_count ?? '–' }}</td>
                                                    <td class="num py-2 text-right">{{ $distribution->person_count ?? '–' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    @endforeach
                                </table>
                            </div>
                        @endif

                        <dl class="grid gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                            @if ($production->selling_price)
                                <div class="flex justify-between gap-3 sm:block"><dt class="text-slate-500">Harga jual</dt><dd class="font-semibold text-slate-800">{{ format_rupiah($production->selling_price) }}</dd></div>
                            @endif
                            @if ($production->start_date)
                                <div class="flex justify-between gap-3 sm:block"><dt class="text-slate-500">{{ $sector->startDateLabel() }}</dt><dd class="font-semibold text-slate-800">{{ format_date($production->start_date) }}</dd></div>
                            @endif
                            @if ($production->seedlingDetail?->origin)
                                <div class="flex justify-between gap-3 sm:block"><dt class="text-slate-500">Asal bibit</dt><dd class="font-semibold text-slate-800">{{ $production->seedlingDetail->origin }}</dd></div>
                            @endif
                            @if ($detail)
                                @foreach ([
                                    'Merek' => $detail->brand,
                                    'Bahan dasar' => $detail->base_ingredient,
                                    'Izin PIRT' => $detail->pirt_permit,
                                    'Sertifikat halal' => $detail->halal_permit,
                                    'Uji laboratorium' => $detail->lab_test,
                                ] as $label => $value)
                                    <div class="flex justify-between gap-3 sm:block"><dt class="text-slate-500">{{ $label }}</dt><dd class="font-semibold text-slate-800">{{ $value ?: 'Belum ada' }}</dd></div>
                                @endforeach
                            @endif
                        </dl>
                    </div>
                </div>
            @endif
        </article>
    @empty
        <x-empty-state title="Tidak ada data" icon="heroicon-o-inbox">Tidak ada hasil {{ strtolower($sector->harvestTerm()) }} di kelurahan ini untuk filter yang dipilih.</x-empty-state>
    @endforelse

    {{ $productions->links('partials.pagination') }}
</div>
