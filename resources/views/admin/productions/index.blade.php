@php
    $term = strtolower($sector->harvestTerm());
    $tabs = [
        'all' => 'Semua',
        'pending' => 'Belum '.$term,
        'late' => 'Terlambat',
        'harvested' => 'Sudah '.$term,
    ];
    $query = array_filter(['q' => $search, 'commodity' => $commodityId, 'district' => $districtId]);
    $today = today();
    $highlight = session('highlight');
@endphp
<x-layouts.admin :title="'Data '.$sector->label()">
    <x-admin.page-header :title="'Data '.$sector->label()" :image="$sector->image()"
                         description="Catat data tanam, lalu isi data {{ $term }} setelah hasilnya didapat.">
        <x-slot:actions>
            <a href="{{ route('sectors.show', $sector) }}" class="btn btn-secondary" target="_blank" rel="noopener">
                <x-heroicon-o-chart-bar class="size-4" aria-hidden="true" /> Lihat di portal
            </a>
            <a href="{{ route('admin.productions.create', $sector) }}" class="btn btn-primary">
                <x-heroicon-m-plus class="size-4" aria-hidden="true" /> Tambah data
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <nav aria-label="Status data" class="-mx-4 mb-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
        <ul class="flex w-max gap-2">
            @foreach ($tabs as $key => $label)
                <li>
                    <a href="{{ route('admin.productions.index', ['sector' => $sector, 'status' => $key === 'all' ? null : $key] + $query) }}"
                       @if($status === $key) aria-current="page" @endif
                       @class([
                           'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold ring-1 transition',
                           'bg-brand-700 text-white ring-brand-700' => $status === $key,
                           'bg-white text-slate-700 ring-slate-200 hover:bg-slate-50' => $status !== $key,
                       ])>
                        @if ($key === 'late')<x-heroicon-m-exclamation-triangle :class="$status !== $key ? 'size-4 text-red-600' : 'size-4'" aria-hidden="true" />@endif
                        {{ $label }}
                        <span @class(['num rounded-full px-2 py-0.5 text-xs', 'bg-brand-950 text-white' => $status === $key, 'bg-slate-100 text-slate-600' => $status !== $key])>{{ format_number($counts[$key], 0) }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <form method="GET" class="card mb-6 grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-[2fr_1.2fr_1.2fr_auto] lg:items-end" role="search" aria-label="Cari data">
        @if ($status !== 'all')<input type="hidden" name="status" value="{{ $status }}">@endif
        <div>
            <label for="q" class="form-label">Cari kelompok</label>
            <input type="search" id="q" name="q" value="{{ $search }}" class="form-control" placeholder="Nama kelompok">
        </div>
        <div>
            <label for="commodity" class="form-label">{{ $sector->commodityLabel() }}</label>
            <select id="commodity" name="commodity" class="form-control" aria-label="{{ $sector->commodityLabel() }}" data-searchable>
                <option value="">Semua</option>
                @foreach ($commodities as $commodity)
                    <option value="{{ $commodity->id }}" @selected($commodityId === $commodity->id)>{{ display_name($commodity->name) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="district" class="form-label">Kecamatan</label>
            <select id="district" name="district" class="form-control" aria-label="Kecamatan" data-searchable>
                <option value="">Semua kecamatan</option>
                @foreach ($districts as $district)
                    <option value="{{ $district->id }}" @selected($districtId === $district->id)>{{ display_name($district->name) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2 sm:col-span-2 lg:col-span-1">
            <button type="submit" class="btn btn-primary flex-1"><x-heroicon-o-magnifying-glass class="size-4" aria-hidden="true" /> Cari</button>
            @if ($query)
                <a href="{{ route('admin.productions.index', ['sector' => $sector, 'status' => $status === 'all' ? null : $status]) }}" class="btn btn-secondary">Atur ulang</a>
            @endif
        </div>
    </form>

    <div class="card">
        @if ($productions->isEmpty())
            <x-empty-state title="Belum ada data" icon="heroicon-o-inbox">
                @if ($query || $status !== 'all')
                    Tidak ada data yang cocok dengan filter ini.
                @else
                    Mulai dengan menambahkan data {{ strtolower($sector->label()) }} pertama.
                @endif
            </x-empty-state>
        @else
            <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel data {{ strtolower($sector->label()) }}">
                <table class="table-base table-stack">
                    <thead>
                        <tr>
                            <th scope="col">{{ $sector->commodityLabel() }}</th>
                            <th scope="col">Kelompok</th>
                            <th scope="col">{{ $sector->startDateLabel() }}</th>
                            @if ($sector->initialQuantityLabel())
                                <th scope="col" class="text-right">{{ $sector->initialQuantityLabel() }}</th>
                            @endif
                            <th scope="col">{{ $sector->harvestTerm() }}</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productions as $production)
                            @php($state = $production->status($today))
                            <tr @class(['hover:bg-slate-50', 'bg-brand-50/60' => $highlight === $production->id])>
                                <td data-label="{{ $sector->commodityLabel() }}">
                                    <span class="font-semibold text-slate-900">{{ display_name($production->commodity->name) }}</span>
                                    @if ($production->planting_category)
                                        <span class="block text-xs text-slate-500">{{ $production->planting_category->label() }}</span>
                                    @endif
                                </td>
                                <td class="min-w-48" data-label="Kelompok">
                                    {{ $production->farmerGroup->name }}
                                    <span class="block text-xs text-slate-500">
                                        Kel. {{ display_name($production->farmerGroup->village?->name) }}, Kec. {{ display_name($production->farmerGroup->village?->district?->name) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap" data-label="{{ $sector->startDateLabel() }}">{{ format_date($production->start_date) }}</td>
                                @if ($sector->initialQuantityLabel())
                                    <td class="num text-right whitespace-nowrap" data-label="{{ $sector->initialQuantityLabel() }}">{{ $production->initial_quantity !== null ? format_quantity($production->initial_quantity, $sector->initialQuantityUnit(), 2) : '–' }}</td>
                                @endif
                                <td class="whitespace-nowrap" data-label="{{ $sector->harvestTerm() }}">
                                    @if ($state === 'harvested')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand-800 ring-1 ring-brand-200">
                                            <x-heroicon-m-check-circle class="size-3.5" aria-hidden="true" /> Sudah {{ $term }}
                                        </span>
                                        <span class="mt-1 block text-xs text-slate-600">{{ format_date($production->harvest_date) }} · {{ format_quantity($production->harvest_quantity, $unit) }}</span>
                                    @elseif ($state === 'late')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-red-200">
                                            <x-heroicon-m-exclamation-triangle class="size-3.5" aria-hidden="true" /> Terlambat
                                        </span>
                                        <span class="mt-1 block text-xs text-slate-600">Perkiraan {{ format_date($production->estimated_harvest_date) }}</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 ring-1 ring-blue-200">
                                            <x-heroicon-m-clock class="size-3.5" aria-hidden="true" /> Belum {{ $term }}
                                        </span>
                                        @if ($production->estimated_harvest_date)
                                            <span class="mt-1 block text-xs text-slate-600">
                                                Perkiraan {{ format_date($production->estimated_harvest_date) }}@if ($production->estimated_harvest_quantity !== null) · {{ format_quantity($production->estimated_harvest_quantity, $unit) }}@endif
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="table-actions text-right whitespace-nowrap">
                                    <a href="{{ route('admin.productions.harvest.edit', [$sector, $production]) }}"
                                       @class(['inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-sm font-semibold', 'bg-brand-700 text-white hover:bg-brand-800' => $state !== 'harvested', 'text-brand-700 hover:bg-brand-50' => $state === 'harvested'])>
                                        <x-heroicon-o-archive-box-arrow-down class="size-4" aria-hidden="true" />
                                        {{ $state === 'harvested' ? 'Ubah '.$term : 'Catat '.$term }}
                                    </a>
                                    <a href="{{ route('admin.productions.edit', [$sector, $production]) }}" class="inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                                        <x-heroicon-o-pencil-square class="size-4" aria-hidden="true" /> Ubah<span class="sr-only"> data tanam</span>
                                    </a>
                                    <x-admin.delete-button :action="route('admin.productions.destroy', [$sector, $production])"
                                        :confirm="'Hapus data '.display_name($production->commodity->name).' milik '.$production->farmerGroup->name.'? Data panen & penyalurannya ikut terhapus.'" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 p-4">{{ $productions->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
