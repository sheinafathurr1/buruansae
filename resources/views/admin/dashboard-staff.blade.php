{{-- Ringkasan untuk staf: hanya kelompok binaannya. --}}
<x-layouts.admin title="Ringkasan">
    <div class="mb-8">
        <p class="text-sm text-slate-600">{{ format_date($today, 'l, j F Y') }}</p>
        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Selamat datang, {{ auth()->user()->name }}</h1>
        <p class="mt-1 text-sm text-slate-600">
            @if ($stats['groups'] > 0)
                Anda mengurus {{ format_number($stats['groups'], 0) }} kelompok binaan. Data yang Anda isi langsung tampil di portal publik.
            @else
                Catat data tanam dan panen kelompok binaan Anda di sini.
            @endif
        </p>
    </div>

    @if ($stats['groups'] === 0)
        <div class="card">
            <x-empty-state title="Belum ada kelompok binaan" icon="heroicon-o-user-group">
                Admin DKPP belum menugaskan kelompok kepada Anda. Hubungi admin agar Anda bisa mulai mengisi data.
            </x-empty-state>
        </div>
    @else
        <section aria-labelledby="ringkasan-title" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <h2 id="ringkasan-title" class="sr-only">Ringkasan kelompok binaan</h2>
            <x-stat-tile label="Kelompok binaan" :value="format_number($stats['groups'], 0)" icon="user-group" tone="success">
                {{ format_number($stats['active_groups'], 0) }} berstatus aktif
            </x-stat-tile>
            <x-stat-tile label="Siklus berjalan" :value="format_number($stats['running'], 0)" icon="clock" tone="info">
                belum dicatat panennya
            </x-stat-tile>
            <x-stat-tile label="Terlambat panen" :value="format_number($stats['late'], 0)" icon="exclamation-triangle" :tone="$stats['late'] > 0 ? 'critical' : 'neutral'" :badge="$stats['late'] > 0 ? 'Perlu dicatat' : null">
                melewati perkiraan tanggal panen
            </x-stat-tile>
            <x-stat-tile :label="'Panen '.format_date($today, 'F Y')" :value="format_number($stats['kg_this_month'])" unit="kg" icon="scale" tone="neutral">
                dari kelompok binaan Anda
            </x-stat-tile>
        </section>

        @include('admin.partials.dashboard-late')

        <section aria-labelledby="binaan-title" class="mt-10">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="binaan-title" class="text-lg font-bold text-slate-900">Kelompok binaan saya</h2>
                    <p class="mt-0.5 text-sm text-slate-600">Yang punya siklus terlambat panen ditampilkan paling atas.</p>
                </div>
                <a href="{{ route('admin.kelompok.index') }}" class="btn btn-secondary px-3 py-2">Lihat semua</a>
            </div>
            <ul class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($myGroups as $group)
                    <li class="card flex flex-col p-4">
                        <h3 class="font-bold text-slate-900">{{ $group->name }}</h3>
                        <p class="text-xs text-slate-500">
                            Kel. {{ display_name($group->village?->name) }}, Kec. {{ display_name($group->village?->district?->name) }}@if ($group->rw) · RW {{ str_pad((string) $group->rw, 2, '0', STR_PAD_LEFT) }}@endif
                        </p>
                        <p class="mt-3 flex flex-wrap gap-1.5 text-xs font-semibold">
                            <span class="num rounded-full bg-blue-50 px-2 py-0.5 text-blue-700 ring-1 ring-blue-200">{{ format_number($group->running_count, 0) }} berjalan</span>
                            @if ($group->late_count > 0)
                                <span class="num inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-red-700 ring-1 ring-red-200">
                                    <x-heroicon-m-exclamation-triangle class="size-3.5" aria-hidden="true" /> {{ format_number($group->late_count, 0) }} terlambat panen
                                </span>
                            @endif
                            @if ((int) $group->updated_this_month === 0)
                                <span class="rounded-full bg-amber-50 px-2 py-0.5 text-amber-800 ring-1 ring-amber-200">Belum ada data bulan ini</span>
                            @endif
                        </p>
                        <p class="mt-3 text-xs text-slate-500">
                            {{ $group->productions_max_updated_at ? 'Terakhir diperbarui '.format_date($group->productions_max_updated_at) : 'Belum ada data produksi' }}
                        </p>
                        <div class="mt-auto pt-4">
                            <a href="{{ route('admin.kelompok.edit', $group) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:underline">
                                <x-heroicon-o-pencil-square class="size-4" aria-hidden="true" /> Ubah profil kelompok<span class="sr-only"> {{ $group->name }}</span>
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>

        @include('admin.partials.dashboard-sectors')
    @endif
</x-layouts.admin>
