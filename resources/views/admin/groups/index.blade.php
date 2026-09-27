<x-layouts.admin title="Kelompok">
    <x-admin.page-header title="Kelompok" description="Data kelompok Buruan SAE beserta wilayah, penyuluh, dan pendampingnya.">
        <x-slot:actions>
            <a href="{{ route('admin.kelompok.create') }}" class="btn btn-primary"><x-heroicon-m-plus class="size-4" aria-hidden="true" /> Tambah kelompok</a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="card mb-6 grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-[2fr_1.2fr_1fr_auto] lg:items-end" role="search" aria-label="Cari kelompok">
        <div>
            <label for="q" class="form-label">Cari</label>
            <input type="search" id="q" name="q" value="{{ $search }}" class="form-control" placeholder="Nama kelompok, ketua, atau penyuluh">
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
        <div>
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="">Semua status</option>
                <option value="active" @selected($status === 'active')>Aktif</option>
                <option value="inactive" @selected($status === 'inactive')>Tidak aktif</option>
                <option value="unknown" @selected($status === 'unknown')>Belum diketahui</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary flex-1"><x-heroicon-o-magnifying-glass class="size-4" aria-hidden="true" /> Cari</button>
            @if ($search !== '' || $districtId || $status)
                <a href="{{ route('admin.kelompok.index') }}" class="btn btn-secondary">Atur ulang</a>
            @endif
        </div>
    </form>

    <div class="card">
        @if ($groups->isEmpty())
            <x-empty-state title="Belum ada kelompok" icon="heroicon-o-user-group">
                {{ $search !== '' || $districtId || $status ? 'Tidak ada kelompok yang cocok dengan pencarian.' : 'Tambahkan kelompok pertama.' }}
            </x-empty-state>
        @else
            <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel kelompok">
                <table class="table-base table-stack">
                    <thead>
                        <tr>
                            <th scope="col">Kelompok</th>
                            <th scope="col">Wilayah</th>
                            <th scope="col">Penyuluh / pendamping</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-right">Produksi</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groups as $group)
                            <tr class="hover:bg-slate-50">
                                <td data-label="Kelompok">
                                    <span class="font-semibold text-slate-900">{{ $group->name }}</span>
                                    @if ($group->leader_name)
                                        <span class="block text-xs text-slate-500">Ketua: {{ $group->leader_name }}</span>
                                    @endif
                                </td>
                                <td data-label="Wilayah">
                                    Kel. {{ display_name($group->village?->name) }}
                                    <span class="block text-xs text-slate-500">
                                        Kec. {{ display_name($group->village?->district?->name) }}@if ($group->rw) · RW {{ str_pad((string) $group->rw, 2, '0', STR_PAD_LEFT) }}@endif
                                    </span>
                                </td>
                                <td data-label="Penyuluh / pendamping">
                                    {{ $group->extension_officer ?: '–' }}
                                    <span class="block text-xs text-slate-500">{{ $group->facilitator ?: '–' }}</span>
                                </td>
                                <td data-label="Status">
                                    @if ($group->is_active === true)
                                        <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-800 ring-1 ring-brand-200">Aktif</span>
                                    @elseif ($group->is_active === false)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">Tidak aktif</span>
                                    @else
                                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800 ring-1 ring-amber-200">Belum diketahui</span>
                                    @endif
                                </td>
                                <td class="num text-right" data-label="Produksi">{{ format_number($group->productions_count, 0) }}</td>
                                <td class="table-actions text-right whitespace-nowrap">
                                    <a href="{{ route('admin.kelompok.edit', $group) }}" class="inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-sm font-semibold text-brand-700 hover:bg-brand-50">
                                        <x-heroicon-o-pencil-square class="size-4" aria-hidden="true" /> Ubah<span class="sr-only"> {{ $group->name }}</span>
                                    </a>
                                    <x-admin.delete-button :action="route('admin.kelompok.destroy', $group)"
                                        :confirm="'Hapus kelompok '.$group->name.'? Data produksinya tetap disimpan tetapi tidak lagi dihitung di portal publik.'">
                                        Hapus<span class="sr-only"> {{ $group->name }}</span>
                                    </x-admin.delete-button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 p-4">{{ $groups->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
