@use('App\Enums\ActivityAction')
@php
    $period = $from->isSameDay($to) ? format_date($from) : format_date($from).' – '.format_date($to);
    $query = fn (array $extra) => array_filter([
        'dari' => $from->toDateString(), 'sampai' => $to->toDateString(), 'petugas' => $userId, 'jenis' => $action?->value,
    ] + $extra, fn ($value) => $value !== null);
@endphp
<x-layouts.admin title="Aktivitas petugas">
    <x-admin.page-header title="Aktivitas petugas"
                         description="Siapa mencatat data tanam dan panen, kapan, dan untuk kelompok mana. Gunakan untuk memantau kinerja petugas." />

    <form method="GET" class="card mb-6 grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-[1.4fr_1.2fr_1fr_1fr_auto] lg:items-end" aria-label="Filter aktivitas">
        <div>
            <label for="petugas" class="form-label">Petugas</label>
            <select id="petugas" name="petugas" class="form-control" aria-label="Petugas" data-searchable>
                <option value="">Semua akun</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected($userId === $user->id)>{{ $user->name }}{{ $user->is_active ? '' : ' (nonaktif)' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="jenis" class="form-label">Jenis aktivitas</label>
            <select id="jenis" name="jenis" class="form-control">
                <option value="">Semua jenis</option>
                @foreach (ActivityAction::cases() as $case)
                    <option value="{{ $case->value }}" @selected($action === $case)>{{ $case->filterLabel() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="dari" class="form-label">Dari tanggal</label>
            <input type="date" id="dari" name="dari" value="{{ $from->toDateString() }}" class="form-control">
        </div>
        <div>
            <label for="sampai" class="form-label">Sampai tanggal</label>
            <input type="date" id="sampai" name="sampai" value="{{ $to->toDateString() }}" class="form-control">
        </div>
        <div class="flex gap-2 sm:col-span-2 lg:col-span-1">
            <button type="submit" class="btn btn-primary flex-1"><x-heroicon-o-funnel class="size-4" aria-hidden="true" /> Tampilkan</button>
            @if ($filtered)
                <a href="{{ route('admin.aktivitas.index') }}" class="btn btn-secondary">Atur ulang</a>
            @endif
        </div>
    </form>

    <section aria-labelledby="ringkasan-title" class="card mb-6">
        <header class="border-b border-slate-100 p-5">
            <h2 id="ringkasan-title" class="text-base font-bold text-slate-900">Ringkasan per petugas</h2>
            <p class="mt-0.5 text-sm text-slate-600">{{ $period }}. Staf aktif tetap ditampilkan walau belum mencatat apa pun.</p>
        </header>
        @if ($summary->isEmpty())
            <x-empty-state title="Belum ada akun staf maupun aktivitas" icon="heroicon-o-clipboard-document-list" compact />
        @else
            <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel ringkasan aktivitas per petugas">
                <table class="table-base table-stack">
                    <thead>
                        <tr>
                            <th scope="col">Petugas</th>
                            <th scope="col" class="text-right">Kelompok binaan</th>
                            <th scope="col" class="text-right">Tanam dicatat</th>
                            <th scope="col" class="text-right">Panen dicatat</th>
                            <th scope="col" class="text-right">Perubahan</th>
                            <th scope="col" class="text-right">Dihapus</th>
                            <th scope="col" class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($summary as $row)
                            <tr @class(['bg-brand-50/60' => $userId === $row->id])>
                                <td data-label="Petugas">
                                    <a href="{{ route('admin.aktivitas.index', $query(['petugas' => $row->id])) }}" class="font-semibold text-slate-900 hover:underline">{{ $row->name }}</a>
                                    <span class="block text-xs text-slate-600">{{ $row->username }} · {{ $row->role->label() }}</span>
                                </td>
                                <td class="num text-right" data-label="Kelompok binaan">{{ $row->isAdmin() ? 'Semua' : format_number($row->farmer_groups_count, 0) }}</td>
                                <td @class(['num text-right', 'text-slate-400' => (int) $row->planting_count === 0]) data-label="Tanam dicatat">{{ format_number($row->planting_count, 0) }}</td>
                                <td @class(['num text-right', 'text-slate-400' => (int) $row->harvest_count === 0]) data-label="Panen dicatat">{{ format_number($row->harvest_count, 0) }}</td>
                                <td @class(['num text-right', 'text-slate-400' => (int) $row->update_count === 0]) data-label="Perubahan">{{ format_number($row->update_count, 0) }}</td>
                                <td @class(['num text-right', 'text-slate-400' => (int) $row->delete_count === 0]) data-label="Dihapus">{{ format_number($row->delete_count, 0) }}</td>
                                <td class="num text-right font-semibold text-slate-900" data-label="Total">{{ format_number($row->total_count, 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section aria-labelledby="riwayat-title" class="card">
        <header class="border-b border-slate-100 p-5">
            <h2 id="riwayat-title" class="text-base font-bold text-slate-900">Riwayat</h2>
            <p class="mt-0.5 text-sm text-slate-600">{{ format_number($activities->total(), 0) }} aktivitas, terbaru di atas.</p>
        </header>
        @if ($activities->isEmpty())
            <x-empty-state title="Belum ada aktivitas" icon="heroicon-o-clipboard-document-list" compact>
                Tidak ada pencatatan yang cocok dengan filter ini.
            </x-empty-state>
        @else
            @include('admin.partials.activity-list')
            <div class="border-t border-slate-100 p-4">{{ $activities->links() }}</div>
        @endif
    </section>
</x-layouts.admin>
