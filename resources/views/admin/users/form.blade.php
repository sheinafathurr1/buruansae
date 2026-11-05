@use('App\Enums\UserRole')
@use('Illuminate\Support\Str')
@php
    $editing = $user->exists;
    $title = $editing ? 'Ubah pengguna' : 'Tambah pengguna';
    $role = old('role', $user->role?->value ?? UserRole::Staff->value);
    $active = (bool) old('is_active', $editing ? $user->is_active : true);
    $search = fn (...$parts) => Str::lower(Str::ascii(implode(' ', array_filter($parts))));
@endphp
<x-layouts.admin :title="$title">
    <x-admin.page-header :title="$title" :breadcrumbs="[['Pengguna & petugas', route('admin.pengguna.index')], [$editing ? $user->name : 'Tambah', null]]" />

    <form method="POST" novalidate class="max-w-5xl space-y-6" x-data="{ role: @js($role) }"
          action="{{ $editing ? route('admin.pengguna.update', $user) : route('admin.pengguna.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        @if ($errors->any())
            <div class="rounded-2xl bg-red-50 p-4 text-sm text-red-900 ring-1 ring-red-200" role="alert">
                Periksa kembali isian yang ditandai merah ({{ $errors->count() }} galat).
            </div>
        @endif

        <section class="card p-5 sm:p-6" aria-labelledby="akun-title">
            <h2 id="akun-title" class="text-base font-bold text-slate-900">Data akun</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <x-form.input name="name" label="Nama lengkap" :value="old('name', $user->name)" required maxlength="255" class="sm:col-span-2" autocomplete="off" />
                <x-form.input name="username" label="Username" :value="old('username', $user->username)" required maxlength="30" autocomplete="off"
                              autocapitalize="none" spellcheck="false" hint="Untuk login. Huruf, angka, - dan _ tanpa spasi." />
                <x-form.input name="email" label="Email" type="email" :value="old('email', $user->email)" required maxlength="255" autocomplete="off" hint="Bisa juga dipakai untuk login." />
            </div>
        </section>

        <section class="card p-5 sm:p-6" aria-labelledby="peran-title">
            <h2 id="peran-title" class="text-base font-bold text-slate-900">Peran &amp; status</h2>
            @if ($isSelf)
                {{-- Admin tidak bisa menurunkan peran atau menonaktifkan akunnya sendiri. --}}
                <input type="hidden" name="role" value="{{ UserRole::Admin->value }}">
                <input type="hidden" name="is_active" value="1">
                <p class="mt-3 text-sm text-slate-600">
                    Ini akun Anda: <span class="font-semibold text-slate-800">{{ $user->role->label() }}</span>, aktif.
                    Peran dan status akun sendiri tidak bisa diubah agar dashboard tidak kehilangan admin.
                </p>
            @else
                <fieldset class="mt-4">
                    <legend class="form-label">Peran <span class="text-red-600" aria-hidden="true">*</span></legend>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach (UserRole::cases() as $case)
                            <label class="flex cursor-pointer gap-3 rounded-2xl border border-slate-300 p-4 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                                <input type="radio" name="role" value="{{ $case->value }}" x-model="role" @checked($role === $case->value)
                                       class="mt-0.5 size-4 border-slate-300 text-brand-600 focus:ring-brand-500">
                                <span>
                                    <span class="block font-semibold text-slate-900">{{ $case->label() }}</span>
                                    <span class="mt-0.5 block text-sm text-slate-600">{{ $case->description() }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('role')<p class="mt-1.5 text-sm text-red-700">{{ $message }}</p>@enderror
                </fieldset>

                <label class="mt-5 flex items-start gap-3 text-sm">
                    <input type="checkbox" name="is_active" value="1" @checked($active)
                           class="mt-0.5 size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <span>
                        <span class="font-semibold text-slate-900">Akun aktif</span>
                        <span class="block text-slate-600">Akun nonaktif tidak bisa masuk dan langsung dikeluarkan dari sesi yang sedang berjalan. Riwayat datanya tetap tersimpan.</span>
                    </span>
                </label>
                @error('is_active')<p class="mt-1.5 text-sm text-red-700">{{ $message }}</p>@enderror
            @endif
        </section>

        <section class="card p-5 sm:p-6" aria-labelledby="sandi-title">
            <h2 id="sandi-title" class="text-base font-bold text-slate-900">{{ $editing ? 'Setel ulang kata sandi' : 'Kata sandi sementara' }}</h2>
            @if ($isSelf)
                <p class="mt-3 text-sm text-slate-600">
                    Ganti kata sandi Anda sendiri dari halaman <a href="{{ route('admin.profile.edit') }}" class="font-semibold text-brand-700 underline">Profil &amp; kata sandi</a>.
                </p>
            @else
                <p class="mt-1 text-sm text-slate-600">
                    {{ $editing ? 'Kosongkan bila tidak ingin mengubah kata sandi. ' : '' }}Berikan kata sandi ini kepada pengguna; ia wajib menggantinya saat pertama masuk.
                </p>
                <div class="mt-5 grid gap-3 sm:max-w-md" x-data="passwordGenerator">
                    <x-form.input name="password" label="Kata sandi sementara" :required="! $editing" autocomplete="new-password"
                                  autocapitalize="none" spellcheck="false" x-ref="input"
                                  hint="Minimal 8 karakter, berisi huruf dan angka." />
                    <button type="button" class="btn btn-secondary w-fit" @click="generate()">
                        <x-heroicon-o-sparkles class="size-4" aria-hidden="true" /> Buat kata sandi acak
                    </button>
                </div>
            @endif
        </section>

        <section class="card p-5 sm:p-6" aria-labelledby="binaan-title">
            <h2 id="binaan-title" class="text-base font-bold text-slate-900">Kelompok binaan</h2>
            <p class="mt-1 text-sm text-slate-600" x-show="role === @js(UserRole::Admin->value)" x-cloak>
                Admin mengelola semua kelompok, jadi tidak perlu memilih kelompok binaan.
            </p>

            <div x-show="role !== @js(UserRole::Admin->value)" x-data="groupPicker" class="mt-1">
                <p class="text-sm text-slate-600">Staf hanya bisa melihat, mengisi, dan mengubah data kelompok yang dipilih di sini. Satu kelompok boleh dipegang lebih dari satu staf.</p>

                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <label for="group-search" class="form-label">Cari kelompok</label>
                        <input type="search" id="group-search" class="form-control" x-model.debounce.150ms="query" autocomplete="off"
                               placeholder="Nama kelompok, kelurahan, kecamatan, atau nama petugas lain">
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-700 sm:pb-2.5">
                        <input type="checkbox" x-model="onlySelected" class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        Hanya yang dipilih
                    </label>
                </div>
                <p class="mt-3 text-sm font-semibold text-slate-800" aria-live="polite">
                    <span class="num" x-text="count">{{ count($selectedGroups) }}</span> kelompok dipilih
                </p>
                @error('group_ids.*')<p class="mt-1.5 text-sm text-red-700">{{ $message }}</p>@enderror

                <div class="mt-3 max-h-[36rem] space-y-2 overflow-y-auto rounded-2xl border border-slate-200 p-2" tabindex="0" role="region" aria-label="Daftar kelompok per kecamatan">
                    @foreach ($groupsByDistrict as $groups)
                        @php($selectedInDistrict = $groups->whereIn('id', $selectedGroups)->count())
                        <details class="group/district rounded-xl bg-white" data-district @if ($selectedInDistrict > 0) open @endif>
                            <summary class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-900 hover:bg-slate-50">
                                <x-heroicon-m-chevron-right class="size-4 shrink-0 text-slate-400 transition group-open/district:rotate-90" aria-hidden="true" />
                                <span class="flex-1">Kec. {{ display_name($groups->first()->district_name) }}</span>
                                <span class="num rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">
                                    <span data-district-count>{{ $selectedInDistrict }}</span>/{{ $groups->count() }}
                                </span>
                            </summary>
                            <div class="flex flex-wrap gap-2 px-3 pt-1 pb-2">
                                <button type="button" class="rounded-lg px-2 py-1 text-xs font-semibold text-brand-700 hover:bg-brand-50" @click="toggleDistrict($el, true)">Pilih semua yang tampil</button>
                                <button type="button" class="rounded-lg px-2 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-100" @click="toggleDistrict($el, false)">Kosongkan</button>
                            </div>
                            <ul class="pb-2">
                                @foreach ($groups as $group)
                                    @php($others = $group->staff->reject(fn ($staff) => $staff->id === $user->id))
                                    <li data-group data-search="{{ $search($group->name, $group->village_name, $group->district_name, $others->pluck('name')->implode(' ')) }}">
                                        <label class="flex cursor-pointer items-start gap-3 rounded-xl px-3 py-2 hover:bg-slate-50 has-[:checked]:bg-brand-50">
                                            <input type="checkbox" name="group_ids[]" value="{{ $group->id }}" @checked(in_array($group->id, $selectedGroups, true))
                                                   @change="recount()" class="mt-0.5 size-4 shrink-0 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                            <span class="min-w-0 text-sm">
                                                <span class="font-semibold text-slate-800">{{ $group->name }}</span>
                                                <span class="block text-xs text-slate-600">
                                                    Kel. {{ display_name($group->village_name) }}@if ($group->rw) · RW {{ str_pad((string) $group->rw, 2, '0', STR_PAD_LEFT) }}@endif
                                                </span>
                                                @if ($others->isNotEmpty())
                                                    <span class="block text-xs text-amber-800">Juga dipegang: {{ $others->pluck('name')->implode(', ') }}</span>
                                                @endif
                                            </span>
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    @endforeach
                    <p class="px-3 py-6 text-center text-sm text-slate-500" x-show="noMatches" x-cloak>Tidak ada kelompok yang cocok.</p>
                </div>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.pengguna.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary"><x-heroicon-o-check class="size-5" aria-hidden="true" /> Simpan pengguna</button>
        </div>
    </form>
</x-layouts.admin>
