@php
    $isAdmin = auth()->user()->isAdmin();
    $filtered = $search !== '' || $districtId || $status || $staffFilter !== '';
@endphp
<x-layouts.admin :title="$isAdmin ? 'Kelompok' : 'Kelompok binaan'">
    <x-admin.page-header :title="$isAdmin ? 'Kelompok' : 'Kelompok binaan'"
                         :description="$isAdmin ? 'Data kelompok Buruan SAE beserta wilayah dan petugas yang bertanggung jawab.' : 'Kelompok yang ditugaskan kepada Anda. Hubungi admin untuk menambah atau memindahkan kelompok.'">
        @can('create', App\Models\FarmerGroup::class)
            <x-slot:actions>
                <a href="{{ route('admin.kelompok.create') }}" class="btn btn-primary"><x-heroicon-m-plus class="size-4" aria-hidden="true" /> Tambah kelompok</a>
            </x-slot:actions>
        @endcan
    </x-admin.page-header>

    <form method="GET" @class([
        'card mb-6 grid gap-4 p-4 sm:grid-cols-2',
        // Admin punya filter petugas: lima kolom baru muat di layar lebar.
        'xl:grid-cols-[minmax(0,2fr)_minmax(0,1.2fr)_minmax(9rem,1fr)_minmax(0,1.2fr)_auto] xl:items-end' => $isAdmin,
        'lg:grid-cols-[2fr_1.2fr_1fr_auto] lg:items-end' => ! $isAdmin,
    ]) role="search" aria-label="Cari kelompok">
        <div>
            <label for="q" class="form-label">Cari</label>
            <input type="search" id="q" name="q" value="{{ $search }}" class="form-control" placeholder="Nama kelompok, ketua, atau petugas">
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
        @if ($isAdmin)
            <div>
                <label for="petugas" class="form-label">Petugas</label>
                <select id="petugas" name="petugas" class="form-control" aria-label="Petugas" data-searchable>
                    <option value="">Semua petugas</option>
                    <option value="none" @selected($staffFilter === 'none')>Belum ada petugas</option>
                    @foreach ($staffUsers as $staff)
                        <option value="{{ $staff->id }}" @selected($staffFilter === (string) $staff->id)>{{ $staff->name }}{{ $staff->is_active ? '' : ' (nonaktif)' }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary flex-1"><x-heroicon-o-magnifying-glass class="size-4" aria-hidden="true" /> Cari</button>
            @if ($filtered)
                <a href="{{ route('admin.kelompok.index') }}" class="btn btn-secondary">Atur ulang</a>
            @endif
        </div>
    </form>

    <div class="card">
        @if ($groups->isEmpty())
            <x-empty-state title="Belum ada kelompok" icon="heroicon-o-user-group">
                @if ($filtered)
                    Tidak ada kelompok yang cocok dengan pencarian.
                @elseif ($isAdmin)
                    Tambahkan kelompok pertama.
                @else
                    Belum ada kelompok yang ditugaskan kepada Anda. Hubungi admin DKPP.
                @endif
            </x-empty-state>
        @else
            <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel kelompok">
                <table class="table-base table-stack">
                    <thead>
                        <tr>
                            <th scope="col">Kelompok</th>
                            <th scope="col">Wilayah</th>
                            <th scope="col">Petugas</th>
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
                                <td data-label="Petugas">
                                    @forelse ($group->staff as $staff)
                                        <span class="block">{{ $staff->name }}@if ($staff->is(auth()->user())) <span class="text-xs text-slate-600">(Anda)</span>@endif</span>
                                    @empty
                                        <span class="inline-block rounded-full whitespace-nowrap bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800 ring-1 ring-amber-200">Belum ada</span>
                                    @endforelse
                                </td>
                                <td data-label="Status">
                                    @if ($group->is_active === true)
                                        <span class="inline-block rounded-full whitespace-nowrap bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-800 ring-1 ring-brand-200">Aktif</span>
                                    @elseif ($group->is_active === false)
                                        <span class="inline-block rounded-full whitespace-nowrap bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">Tidak aktif</span>
                                    @else
                                        <span class="inline-block rounded-full whitespace-nowrap bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800 ring-1 ring-amber-200">Belum diketahui</span>
                                    @endif
                                </td>
                                <td class="num text-right" data-label="Produksi">{{ format_number($group->productions_count, 0) }}</td>
                                <td class="table-actions text-right whitespace-nowrap">
                                    <a href="{{ route('admin.kelompok.edit', $group) }}" class="inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-sm font-semibold text-brand-700 hover:bg-brand-50">
                                        <x-heroicon-o-pencil-square class="size-4" aria-hidden="true" /> Ubah<span class="sr-only"> {{ $group->name }}</span>
                                    </a>
                                    @can('delete', $group)
                                        <x-admin.delete-button :action="route('admin.kelompok.destroy', $group)"
                                            :confirm="'Hapus kelompok '.$group->name.'? Data produksinya tetap disimpan tetapi tidak lagi dihitung di portal publik.'">
                                            Hapus<span class="sr-only"> {{ $group->name }}</span>
                                        </x-admin.delete-button>
                                    @endcan
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
