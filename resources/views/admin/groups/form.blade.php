@php
    $isAdmin = auth()->user()->isAdmin();
    $editing = $group->exists;
    $selectedStaff = array_map('intval', old('staff_ids', $editing ? $group->staff->pluck('id')->all() : []));
    $title = $editing ? 'Ubah kelompok' : 'Tambah kelompok';
    $activeValue = old('is_active', $group->is_active === null ? '' : ($group->is_active ? '1' : '0'));
@endphp
<x-layouts.admin :title="$title">
    <x-admin.page-header :title="$title" :breadcrumbs="[[$isAdmin ? 'Kelompok' : 'Kelompok binaan', route('admin.kelompok.index')], [$editing ? $group->name : 'Tambah', null]]" />

    <form method="POST" enctype="multipart/form-data" novalidate
          action="{{ $editing ? route('admin.kelompok.update', $group) : route('admin.kelompok.store') }}" class="space-y-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        @if ($errors->any())
            <div class="rounded-2xl bg-red-50 p-4 text-sm text-red-900 ring-1 ring-red-200" role="alert">
                Periksa kembali isian yang ditandai merah ({{ $errors->count() }} galat).
            </div>
        @endif

        <section class="card p-5 sm:p-6" aria-labelledby="identitas-title">
            <h2 id="identitas-title" class="text-base font-bold text-slate-900">Identitas kelompok</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                @if ($isAdmin)
                    <x-form.input name="name" label="Nama kelompok" :value="old('name', $group->name)" required maxlength="150" class="sm:col-span-2" />

                    <x-form.select name="village_id" label="Kelurahan" required data-searchable hint="Ketik nama kelurahan atau kecamatan untuk mencari.">
                        <option value="">Pilih kelurahan</option>
                        @foreach ($districts as $district)
                            <optgroup label="Kec. {{ display_name($district->name) }}">
                                @foreach ($district->villages as $village)
                                    <option value="{{ $village->id }}" data-data="{{ json_encode(['search' => $district->name]) }}"
                                            @selected((int) old('village_id', $group->village_id) === $village->id)>{{ display_name($village->name) }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </x-form.select>
                    <x-form.input name="rw" label="RW" type="number" min="1" max="255" inputmode="numeric" :value="old('rw', $group->rw)" />
                @else
                    {{-- Staf tidak mengubah nama & wilayah: keduanya menentukan kelompok mana ini. --}}
                    <div class="rounded-2xl bg-slate-50 p-4 text-sm sm:col-span-2">
                        <dl class="grid gap-3 sm:grid-cols-[2fr_1fr_1fr_auto]">
                            <div><dt class="text-xs text-slate-500">Nama kelompok</dt><dd class="font-semibold text-slate-800">{{ $group->name }}</dd></div>
                            <div><dt class="text-xs text-slate-500">Kelurahan</dt><dd class="font-semibold text-slate-800">{{ display_name($group->village?->name) }}</dd></div>
                            <div><dt class="text-xs text-slate-500">Kecamatan</dt><dd class="font-semibold text-slate-800">{{ display_name($group->village?->district?->name) }}</dd></div>
                            <div><dt class="text-xs text-slate-500">RW</dt><dd class="font-semibold text-slate-800">{{ $group->rw ? str_pad((string) $group->rw, 2, '0', STR_PAD_LEFT) : '–' }}</dd></div>
                        </dl>
                        <p class="mt-3 text-xs text-slate-500">Nama dan wilayah kelompok hanya bisa diubah oleh admin.</p>
                    </div>
                @endif

                <fieldset class="sm:col-span-2">
                    <legend class="form-label">Status keaktifan</legend>
                    <div class="flex flex-wrap gap-2">
                        @foreach (['1' => 'Aktif', '0' => 'Tidak aktif', '' => 'Belum diketahui'] as $value => $label)
                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-300 px-3.5 py-2 text-sm has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:text-brand-900">
                                <input type="radio" name="is_active" value="{{ $value }}" @checked((string) $activeValue === (string) $value) class="size-4 border-slate-300 text-brand-600 focus:ring-brand-500">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                <x-form.input name="status_note" label="Keterangan status" :value="old('status_note', $group->status_note)" maxlength="255" class="sm:col-span-2" hint="Opsional, mis. alasan tidak aktif." />
            </div>
        </section>

        <section class="card p-5 sm:p-6" aria-labelledby="pengurus-title">
            <h2 id="pengurus-title" class="text-base font-bold text-slate-900">Pengurus &amp; pendamping</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <x-form.input name="leader_name" label="Nama ketua" :value="old('leader_name', $group->leader_name)" maxlength="150" />
                <x-form.input name="phone" label="Nomor kontak" type="tel" inputmode="tel" :value="old('phone', $group->phone)" maxlength="20" placeholder="08xxxxxxxxxx" />
                <x-form.input name="extension_officer" label="Penyuluh" :value="old('extension_officer', $group->extension_officer)" required maxlength="150" />
                <x-form.input name="facilitator" label="Pendamping" :value="old('facilitator', $group->facilitator)" required maxlength="150" />
            </div>
        </section>

        <section class="card p-5 sm:p-6" aria-labelledby="lahan-title">
            <h2 id="lahan-title" class="text-base font-bold text-slate-900">Lahan &amp; dokumentasi</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <x-form.input name="land_area_m2" label="Luas lahan" type="number" min="0" step="any" inputmode="decimal" suffix="m²" :value="old('land_area_m2', $group->land_area_m2 !== null ? (float) $group->land_area_m2 : null)" />
                <x-form.input name="land_status" label="Status lahan" :value="old('land_status', $group->land_status)" maxlength="50" list="land-status-options" hint="Mis. Milik Pribadi, Fasos/Fasum, Pinjam Pakai." />
                <datalist id="land-status-options">
                    <option value="Milik Pribadi"><option value="Fasos/Fasum"><option value="Pinjam Pakai"><option value="Sewa">
                </datalist>
                <x-form.input name="description_url" label="Tautan lokasi / deskripsi" type="url" :value="old('description_url', $group->description_url)" maxlength="500" class="sm:col-span-2" placeholder="https://maps.google.com/…" hint="Opsional: tautan Google Maps atau halaman profil kelompok." />
                <x-form.photo name="land_photo" label="Foto lahan" :current="$group->land_photo_url" removable hint="JPG/PNG/WebP, maks. 8 MB. Foto besar otomatis diperkecil." />
                <x-form.photo name="leader_photo" label="Foto ketua" :current="$group->leader_photo_url" removable hint="JPG/PNG/WebP, maks. 8 MB." />
            </div>
        </section>

        @if ($isAdmin)
            <section class="card p-5 sm:p-6" aria-labelledby="petugas-title">
                <h2 id="petugas-title" class="text-base font-bold text-slate-900">Petugas</h2>
                <p class="mt-1 text-sm text-slate-600">Staf yang boleh mengisi dan mengubah data kelompok ini. Admin selalu bisa mengelola semua kelompok.</p>
                @if ($staffUsers->isEmpty())
                    <p class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                        Belum ada akun staf. <a href="{{ route('admin.pengguna.create') }}" class="font-semibold text-brand-700 underline">Buat akun staf</a> lebih dulu.
                    </p>
                @else
                    <fieldset class="mt-4">
                        <legend class="sr-only">Pilih petugas kelompok ini</legend>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($staffUsers as $staff)
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                                    <input type="checkbox" name="staff_ids[]" value="{{ $staff->id }}" @checked(in_array($staff->id, $selectedStaff, true))
                                           class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                    <span class="min-w-0">
                                        <span class="block truncate font-semibold text-slate-800">{{ $staff->name }}</span>
                                        <span class="block truncate text-xs text-slate-500">{{ $staff->username }}{{ $staff->is_active ? '' : ' · nonaktif' }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    @error('staff_ids.*')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                @endif
            </section>
        @endif

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.kelompok.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary"><x-heroicon-o-check class="size-5" aria-hidden="true" /> Simpan kelompok</button>
        </div>
    </form>
</x-layouts.admin>
