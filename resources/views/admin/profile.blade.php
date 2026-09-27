<x-layouts.admin title="Profil">
    <x-admin.page-header title="Profil & kata sandi" description="Perbarui nama, email, dan kata sandi akun Anda." />

    <div class="grid max-w-5xl gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('admin.profile.update') }}" class="card space-y-5 p-5 sm:p-6" novalidate aria-labelledby="profil-title">
            @csrf
            @method('PUT')
            <h2 id="profil-title" class="text-base font-bold text-slate-900">Data akun</h2>
            <div>
                <p class="form-label">Username</p>
                <p class="rounded-xl bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700">{{ $user->username ?? '–' }}</p>
                <p class="mt-1.5 text-xs text-slate-500">Username hanya bisa diubah oleh admin server.</p>
            </div>
            <x-form.input name="name" label="Nama" :value="old('name', $user->name)" required maxlength="255" autocomplete="name" />
            <x-form.input name="email" label="Email" type="email" :value="old('email', $user->email)" required maxlength="255" autocomplete="email" />
            <button type="submit" class="btn btn-primary"><x-heroicon-o-check class="size-5" aria-hidden="true" /> Simpan profil</button>
        </form>

        <form method="POST" action="{{ route('admin.profile.password') }}" class="card space-y-5 p-5 sm:p-6" novalidate aria-labelledby="sandi-title">
            @csrf
            @method('PUT')
            <h2 id="sandi-title" class="text-base font-bold text-slate-900">Ganti kata sandi</h2>
            <x-form.input name="current_password" label="Kata sandi saat ini" type="password" required autocomplete="current-password" />
            <x-form.input name="password" label="Kata sandi baru" type="password" required autocomplete="new-password" hint="Minimal 8 karakter, berisi huruf dan angka." />
            <x-form.input name="password_confirmation" label="Ulangi kata sandi baru" type="password" required autocomplete="new-password" />
            <button type="submit" class="btn btn-primary"><x-heroicon-o-key class="size-5" aria-hidden="true" /> Ganti kata sandi</button>
        </form>
    </div>
</x-layouts.admin>
