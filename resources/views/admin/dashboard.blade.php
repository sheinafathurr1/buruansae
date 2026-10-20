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

    <section aria-labelledby="petugas-title" class="card mt-10">
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">
            <div>
                <h2 id="petugas-title" class="text-base font-bold text-slate-900">Petugas</h2>
                <p class="mt-0.5 text-sm text-slate-600">Staf aktif, jumlah kelompok binaannya, dan pencatatan mereka bulan {{ format_date($today, 'F') }}.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.aktivitas.index') }}" class="btn btn-secondary px-3 py-2">Rincian kinerja</a>
                <a href="{{ route('admin.pengguna.index') }}" class="btn btn-secondary px-3 py-2">Kelola pengguna</a>
            </div>
        </header>
        @if ($groupsWithoutStaff > 0)
            <p class="flex flex-wrap items-center gap-x-3 gap-y-1 border-b border-slate-100 bg-amber-50/70 px-5 py-3 text-sm text-amber-900">
                <x-heroicon-o-exclamation-triangle class="size-5 shrink-0" aria-hidden="true" />
                <span class="flex-1"><span class="font-semibold">{{ format_number($groupsWithoutStaff, 0) }} kelompok</span> belum punya petugas.</span>
                <a href="{{ route('admin.kelompok.index', ['petugas' => 'none']) }}" class="font-semibold underline">Lihat &amp; tugaskan</a>
            </p>
        @endif
        @if ($staffActivity->isEmpty())
            <x-empty-state title="Belum ada akun staf" icon="heroicon-o-identification" compact>
                <a href="{{ route('admin.pengguna.create') }}" class="font-semibold text-brand-700 underline">Buat akun staf</a> lalu pilih kelompok binaannya.
            </x-empty-state>
        @else
            <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel aktivitas petugas">
                <table class="table-base table-stack">
                    <thead>
                        <tr>
                            <th scope="col">Petugas</th>
                            <th scope="col" class="text-right">Kelompok binaan</th>
                            <th scope="col" class="text-right">Tanam dicatat</th>
                            <th scope="col" class="text-right">Panen dicatat</th>
                            <th scope="col" class="text-right">Terlambat panen</th>
                            <th scope="col">Terakhir masuk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($staffActivity as $staff)
                            <tr>
                                <td data-label="Petugas">
                                    <a href="{{ route('admin.pengguna.edit', $staff) }}" class="font-semibold text-slate-900 hover:underline">{{ $staff->name }}</a>
                                    <span class="block text-xs text-slate-500">{{ $staff->username }}</span>
                                </td>
                                <td class="num text-right" data-label="Kelompok binaan">
                                    @if ($staff->farmer_groups_count > 0)
                                        <a href="{{ route('admin.kelompok.index', ['petugas' => $staff->id]) }}" class="font-semibold text-brand-700 hover:underline">{{ format_number($staff->farmer_groups_count, 0) }}</a>
                                    @else
                                        <span class="inline-block rounded-full whitespace-nowrap bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800 ring-1 ring-amber-200">Belum ada</span>
                                    @endif
                                </td>
                                <td @class(['num text-right', 'text-slate-400' => (int) $staff->planted_this_month === 0]) data-label="Tanam dicatat bulan ini">
                                    {{ format_number($staff->planted_this_month, 0) }}
                                </td>
                                <td @class(['num text-right', 'text-slate-400' => (int) $staff->harvested_this_month === 0]) data-label="Panen dicatat bulan ini">
                                    {{ format_number($staff->harvested_this_month, 0) }}
                                </td>
                                <td @class(['num text-right', 'font-semibold text-red-700' => $staff->late_count > 0]) data-label="Terlambat panen">
                                    {{ format_number($staff->late_count, 0) }}
                                </td>
                                <td class="whitespace-nowrap" data-label="Terakhir masuk">
                                    {{ $staff->last_login_at ? format_date($staff->last_login_at, 'j M Y, H.i') : 'Belum pernah' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section aria-labelledby="aktivitas-title" class="card mt-10">
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">
            <div>
                <h2 id="aktivitas-title" class="text-base font-bold text-slate-900">Aktivitas terbaru</h2>
                <p class="mt-0.5 text-sm text-slate-600">Pencatatan data tanam dan panen oleh admin maupun staf.</p>
            </div>
            <a href="{{ route('admin.aktivitas.index') }}" class="btn btn-secondary px-3 py-2">Lihat semua</a>
        </header>
        @if ($recentActivities->isEmpty())
            <x-empty-state title="Belum ada aktivitas" icon="heroicon-o-clipboard-document-list" compact>
                Pencatatan data tanam dan panen dari dashboard akan muncul di sini.
            </x-empty-state>
        @else
            @include('admin.partials.activity-list', ['activities' => $recentActivities])
        @endif
    </section>

    @include('admin.partials.dashboard-sectors')
    @include('admin.partials.dashboard-late')
</x-layouts.admin>
