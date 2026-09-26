@php
    $editing = $commodity->exists;
    $title = $editing ? 'Ubah komoditas' : 'Tambah komoditas';
@endphp
<x-layouts.admin :title="$title">
    <x-admin.page-header :title="$title" :breadcrumbs="[['Komoditas', route('admin.komoditas.index')], [$editing ? display_name($commodity->name) : 'Tambah', null]]" />

    <form method="POST" enctype="multipart/form-data" novalidate
          action="{{ $editing ? route('admin.komoditas.update', $commodity) : route('admin.komoditas.store') }}" class="max-w-3xl space-y-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <section class="card grid gap-5 p-5 sm:grid-cols-2 sm:p-6" aria-label="Data komoditas">
            <x-form.select name="sector_id" label="Sektor" required>
                <option value="">Pilih sektor</option>
                @foreach ($sectors as $sector)
                    <option value="{{ $sector->id }}" @selected((int) old('sector_id', $commodity->sector_id) === $sector->id)>{{ $sector->name }}</option>
                @endforeach
            </x-form.select>
            <x-form.input name="name" label="Nama komoditas" :value="old('name', $commodity->name)" required maxlength="150" hint="Disimpan dengan huruf besar, mis. KANGKUNG." />
            <x-form.input name="growing_days" label="Durasi tanam" type="number" min="1" max="3650" inputmode="numeric" suffix="hari"
                          :value="old('growing_days', $commodity->growing_days)" hint="Wajib untuk semua sektor kecuali Olahan Hasil." />
            <x-form.photo name="image" label="Gambar komoditas" :current="$commodity->image_url" removable class="sm:col-span-2" hint="Tampil di dashboard publik saat komoditas dipilih. JPG/PNG/WebP, maks. 4 MB." />
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.komoditas.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary"><x-heroicon-o-check class="size-5" aria-hidden="true" /> Simpan komoditas</button>
        </div>
    </form>
</x-layouts.admin>
