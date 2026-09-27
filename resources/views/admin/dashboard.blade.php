<x-layouts.admin title="Ringkasan">
    <div class="mb-8">
        <p class="text-sm text-slate-600">{{ format_date($today, 'l, j F Y') }}</p>
        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Selamat datang, {{ auth()->user()->name }}</h1>
        <p class="mt-1 text-sm text-slate-600">Catat data tanam dan panen kelompok Buruan SAE. Perubahan langsung tampil di portal publik.</p>
    </div>

    <section aria-labelledby="ringkasan-title" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <h2 id="ringkasan-title" class="sr-only">Ringkasan</h2>
        <x-stat-tile label="Kelompok aktif" :value="format_number($stats['active_groups'], 0)" icon="user-group" tone="success">
            dari {{ format_number($stats['groups'], 0) }} kelompok terdaftar
        </x-stat-tile>
        <x-stat-tile label="Siklus berjalan" :value="format_number($stats['running'], 0)" icon="clock" tone="info">
            belum dicatat panennya
        </x-stat-tile>
        <x-stat-tile label="Terlambat panen" :value="format_number($stats['late'], 0)" icon="exclamation-triangle" :tone="$stats['late'] > 0 ? 'critical' : 'neutral'" :badge="$stats['late'] > 0 ? 'Perlu dicek' : null">
            melewati perkiraan tanggal panen
        </x-stat-tile>
        <x-stat-tile :label="'Panen '.format_date($today, 'F Y')" :value="format_number($stats['kg_this_month'])" unit="kg" icon="scale" tone="neutral">
            total hasil bersatuan kilogram
        </x-stat-tile>
    </section>

    <section aria-labelledby="sektor-title" class="mt-10">
        <h2 id="sektor-title" class="text-lg font-bold text-slate-900">Input data per sektor</h2>
        <ul class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($sectors as $item)
                <li class="card flex flex-col p-4">
                    <div class="flex items-center gap-3">
                        <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 p-1.5 ring-1 ring-brand-100">
                            <img src="{{ asset($item->type->image()) }}" alt="" class="max-h-full max-w-full object-contain">
                        </span>
                        <h3 class="font-bold text-slate-900">{{ $item->type->label() }}</h3>
                    </div>
                    <dl class="mt-4 grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-xl bg-slate-50 px-1 py-2">
                            <dt class="text-[11px] font-medium text-slate-500">Berjalan</dt>
                            <dd class="num text-base font-bold text-slate-900">{{ format_number($item->running, 0) }}</dd>
                        </div>
                        <div @class(['rounded-xl px-1 py-2', 'bg-red-50' => $item->late > 0, 'bg-slate-50' => $item->late === 0])>
                            <dt class="text-[11px] font-medium text-slate-600">Terlambat</dt>
                            <dd @class(['num text-base font-bold', 'text-red-700' => $item->late > 0, 'text-slate-900' => $item->late === 0])>{{ format_number($item->late, 0) }}</dd>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-1 py-2">
                            <dt class="text-[11px] font-medium text-slate-500">Panen bln ini</dt>
                            <dd class="num text-base font-bold text-slate-900">{{ format_number($item->harvested_this_month, 0) }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('admin.productions.index', $item->type) }}" class="btn btn-secondary flex-1 px-3 py-2">Lihat data</a>
                        <a href="{{ route('admin.productions.create', $item->type) }}" class="btn btn-primary px-3 py-2" title="Tambah data {{ strtolower($item->type->label()) }}">
                            <x-heroicon-m-plus class="size-4" aria-hidden="true" /><span class="sr-only">Tambah data {{ strtolower($item->type->label()) }}</span>
                        </a>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    <section aria-labelledby="terlambat-title" class="card mt-10">
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">
            <div>
                <h2 id="terlambat-title" class="text-base font-bold text-slate-900">Perlu dicatat panennya</h2>
                <p class="mt-0.5 text-sm text-slate-500">Siklus yang perkiraan tanggal panennya sudah lewat, dari yang paling lama.</p>
            </div>
        </header>
        @if ($lateProductions->isEmpty())
            <x-empty-state title="Tidak ada siklus yang terlambat panen" icon="heroicon-o-check-circle" compact />
        @else
            <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel siklus terlambat panen">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th scope="col">Komoditas</th>
                            <th scope="col">Kelompok</th>
                            <th scope="col">Perkiraan panen</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lateProductions as $production)
                            @php($type = \App\Enums\SectorType::fromCode($production->commodity->sector->code))
                            <tr>
                                <td>
                                    <span class="font-semibold text-slate-900">{{ display_name($production->commodity->name) }}</span>
                                    <span class="block text-xs text-slate-500">{{ $type?->label() }}</span>
                                </td>
                                <td>
                                    {{ $production->farmerGroup->name }}
                                    <span class="block text-xs text-slate-500">Kel. {{ display_name($production->farmerGroup->village?->name) }}</span>
                                </td>
                                <td class="whitespace-nowrap">
                                    {{ format_date($production->estimated_harvest_date) }}
                                    <span class="block text-xs text-red-700">{{ (int) $production->estimated_harvest_date->diffInDays($today) }} hari lalu</span>
                                </td>
                                <td class="text-right">
                                    @if ($type)
                                        <a href="{{ route('admin.productions.harvest.edit', [$type, $production]) }}" class="btn btn-primary px-3 py-1.5 whitespace-nowrap">
                                            Catat {{ strtolower($type->harvestTerm()) }}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.admin>
