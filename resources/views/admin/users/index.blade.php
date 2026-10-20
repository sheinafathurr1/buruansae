@use('App\Enums\UserRole')
<x-layouts.admin title="Pengguna & petugas">
    <x-admin.page-header title="Pengguna & petugas"
                         description="Akun admin dan staf. Staf hanya bisa mengisi data kelompok binaan yang ditugaskan kepadanya.">
        <x-slot:actions>
            <a href="{{ route('admin.pengguna.create') }}" class="btn btn-primary"><x-heroicon-m-plus class="size-4" aria-hidden="true" /> Tambah pengguna</a>
        </x-slot:actions>
    </x-admin.page-header>

    @if ($groupsWithoutStaff > 0)
        <div class="mb-6 flex flex-wrap items-center gap-x-4 gap-y-2 rounded-2xl bg-amber-50 p-4 text-sm text-amber-900 ring-1 ring-amber-200">
            <x-heroicon-o-exclamation-triangle class="size-5 shrink-0" aria-hidden="true" />
            <p class="flex-1"><span class="font-semibold">{{ format_number($groupsWithoutStaff, 0) }} kelompok</span> belum punya petugas. Hanya admin yang bisa mengisi datanya.</p>
            <a href="{{ route('admin.kelompok.index', ['petugas' => 'none']) }}" class="font-semibold underline">Lihat kelompoknya</a>
        </div>
    @endif

    <form method="GET" class="card mb-6 grid gap-4 p-4 sm:grid-cols-[2fr_1fr_auto] sm:items-end" role="search" aria-label="Cari pengguna">
        <div>
            <label for="q" class="form-label">Cari</label>
            <input type="search" id="q" name="q" value="{{ $search }}" class="form-control" placeholder="Nama, username, atau email">
        </div>
        <div>
            <label for="peran" class="form-label">Peran</label>
            <select id="peran" name="peran" class="form-control">
                <option value="">Semua peran</option>
                @foreach (UserRole::cases() as $case)
                    <option value="{{ $case->value }}" @selected($role === $case)>{{ $case->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary flex-1"><x-heroicon-o-magnifying-glass class="size-4" aria-hidden="true" /> Cari</button>
            @if ($search !== '' || $role)
                <a href="{{ route('admin.pengguna.index') }}" class="btn btn-secondary">Atur ulang</a>
            @endif
        </div>
    </form>

    <div class="card">
        @if ($users->isEmpty())
            <x-empty-state title="Tidak ada pengguna" icon="heroicon-o-identification">Tidak ada akun yang cocok dengan pencarian.</x-empty-state>
        @else
            <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel pengguna">
                <table class="table-base table-stack">
                    <thead>
                        <tr>
                            <th scope="col">Pengguna</th>
                            <th scope="col">Peran</th>
                            <th scope="col">Kelompok binaan</th>
                            <th scope="col">Status</th>
                            <th scope="col">Terakhir masuk</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $item)
                            <tr class="hover:bg-slate-50">
                                <td data-label="Pengguna">
                                    <span class="font-semibold text-slate-900">{{ $item->name }}</span>
                                    @if ($item->is(auth()->user()))<span class="text-xs text-slate-500">(Anda)</span>@endif
                                    <span class="block text-xs text-slate-500">{{ $item->username }} · {{ $item->email }}</span>
                                </td>
                                <td data-label="Peran">
                                    <span @class([
                                        'rounded-full px-2.5 py-1 text-xs font-semibold',
                                        'bg-brand-950 text-white' => $item->isAdmin(),
                                        'bg-brand-50 text-brand-800 ring-1 ring-brand-200' => ! $item->isAdmin(),
                                    ])>{{ $item->role->label() }}</span>
                                </td>
                                <td data-label="Kelompok binaan">
                                    @if ($item->isAdmin())
                                        <span class="text-slate-500">Semua kelompok</span>
                                    @elseif ($item->farmer_groups_count === 0)
                                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800 ring-1 ring-amber-200">Belum ada</span>
                                    @else
                                        <a href="{{ route('admin.kelompok.index', ['petugas' => $item->id]) }}" class="num font-semibold text-brand-700 hover:underline">
                                            {{ format_number($item->farmer_groups_count, 0) }} kelompok
                                        </a>
                                    @endif
                                </td>
                                <td data-label="Status">
                                    @if ($item->is_active)
                                        <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-800 ring-1 ring-brand-200">Aktif</span>
                                    @else
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">Nonaktif</span>
                                    @endif
                                    @if ($item->must_change_password)
                                        <span class="mt-1.5 block text-xs text-amber-800">Belum ganti kata sandi sementara</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap" data-label="Terakhir masuk">
                                    {{ $item->last_login_at ? format_date($item->last_login_at, 'j M Y, H.i') : 'Belum pernah' }}
                                </td>
                                <td class="table-actions text-right whitespace-nowrap">
                                    <a href="{{ route('admin.pengguna.edit', $item) }}" class="inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-sm font-semibold text-brand-700 hover:bg-brand-50">
                                        <x-heroicon-o-pencil-square class="size-4" aria-hidden="true" /> Ubah<span class="sr-only"> {{ $item->name }}</span>
                                    </a>
                                    @unless ($item->is(auth()->user()))
                                        <x-admin.delete-button :action="route('admin.pengguna.destroy', $item)"
                                            :confirm="'Hapus akun '.$item->username.'? Akun yang sudah pernah mencatat data tidak bisa dihapus; nonaktifkan saja.'">
                                            Hapus<span class="sr-only"> {{ $item->name }}</span>
                                        </x-admin.delete-button>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 p-4">{{ $users->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
