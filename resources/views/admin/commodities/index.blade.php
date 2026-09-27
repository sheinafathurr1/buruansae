<x-layouts.admin title="Komoditas">
    <x-admin.page-header title="Komoditas" description="Daftar komoditas per sektor. Durasi tanam dipakai untuk menghitung perkiraan tanggal panen.">
        <x-slot:actions>
            <a href="{{ route('admin.komoditas.create', ['sector' => $sectorId]) }}" class="btn btn-primary"><x-heroicon-m-plus class="size-4" aria-hidden="true" /> Tambah komoditas</a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="card mb-6 grid gap-4 p-4 sm:grid-cols-[2fr_1.2fr_auto] sm:items-end" role="search" aria-label="Cari komoditas">
        <div>
            <label for="q" class="form-label">Cari</label>
            <input type="search" id="q" name="q" value="{{ $search }}" class="form-control" placeholder="Nama komoditas">
        </div>
        <div>
            <label for="sector" class="form-label">Sektor</label>
            <select id="sector" name="sector" class="form-control">
                <option value="">Semua sektor</option>
                @foreach ($sectors as $sector)
                    <option value="{{ $sector->id }}" @selected($sectorId === $sector->id)>{{ $sector->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary flex-1"><x-heroicon-o-magnifying-glass class="size-4" aria-hidden="true" /> Cari</button>
            @if ($search !== '' || $sectorId)
                <a href="{{ route('admin.komoditas.index') }}" class="btn btn-secondary">Atur ulang</a>
            @endif
        </div>
    </form>

    <div class="card">
        @if ($commodities->isEmpty())
            <x-empty-state title="Belum ada komoditas" icon="heroicon-o-tag">
                {{ $search !== '' || $sectorId ? 'Tidak ada komoditas yang cocok.' : 'Tambahkan komoditas pertama.' }}
            </x-empty-state>
        @else
            <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel komoditas">
                <table class="table-base table-stack">
                    <thead>
                        <tr>
                            <th scope="col">Komoditas</th>
                            <th scope="col">Sektor</th>
                            <th scope="col" class="text-right">Durasi tanam</th>
                            <th scope="col" class="text-right">Dipakai</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($commodities as $commodity)
                            <tr class="hover:bg-slate-50">
                                <td data-label="Komoditas">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100 ring-1 ring-slate-200">
                                            @if ($commodity->image_url)
                                                <img src="{{ $commodity->image_url }}" alt="" class="size-full object-cover" loading="lazy">
                                            @else
                                                <x-heroicon-o-photo class="size-5 text-slate-400" aria-hidden="true" />
                                            @endif
                                        </span>
                                        <span class="font-semibold text-slate-900">{{ display_name($commodity->name) }}</span>
                                    </div>
                                </td>
                                <td data-label="Sektor">{{ $commodity->sector->name }}</td>
                                <td class="num text-right" data-label="Durasi tanam">{{ $commodity->growing_days ? format_number($commodity->growing_days, 0).' hari' : '–' }}</td>
                                <td class="num text-right" data-label="Dipakai">{{ format_number($commodity->productions_count, 0) }} siklus</td>
                                <td class="table-actions text-right whitespace-nowrap">
                                    <a href="{{ route('admin.komoditas.edit', $commodity) }}" class="inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-sm font-semibold text-brand-700 hover:bg-brand-50">
                                        <x-heroicon-o-pencil-square class="size-4" aria-hidden="true" /> Ubah<span class="sr-only"> {{ display_name($commodity->name) }}</span>
                                    </a>
                                    @if ($commodity->productions_count === 0)
                                        <x-admin.delete-button :action="route('admin.komoditas.destroy', $commodity)" :confirm="'Hapus komoditas '.display_name($commodity->name).'?'">
                                            Hapus<span class="sr-only"> {{ display_name($commodity->name) }}</span>
                                        </x-admin.delete-button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 p-4">{{ $commodities->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
