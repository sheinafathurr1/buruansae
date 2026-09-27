<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk · Dashboard Pengelola Buruan SAE</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white">
    <main class="grid min-h-screen lg:grid-cols-2">
        {{-- Identitas: langit, logo, lapisan air & tanah (seperti portal publik). --}}
        <div class="relative hidden overflow-hidden bg-gradient-to-b from-sky-50 via-sky-100 to-sky-200 lg:flex lg:flex-col">
            <div class="flex flex-1 flex-col items-center justify-center px-12 pt-12 pb-32 text-center">
                <div class="arch flex w-56 items-center justify-center bg-white px-8 pt-10 pb-7 ring-8 ring-white/60">
                    <img src="{{ asset('images/brand/logo-buruansae-utama.png') }}" alt="" width="770" height="898" class="w-full">
                </div>
                <p class="display mt-10 max-w-md text-3xl leading-tight">Dashboard pengelola data Buruan SAE</p>
                <p class="mt-3 max-w-md text-ink-soft">Catat data tanam, panen, dan penyaluran hasil kelompok. Data langsung tampil di portal publik.</p>
            </div>
            <x-strata class="absolute inset-x-0 bottom-0" end="#ffffff" />
        </div>

        <div class="flex items-center justify-center px-6 py-12 sm:px-12">
            <div class="w-full max-w-sm">
                <a href="{{ route('home') }}" class="inline-block lg:hidden" aria-label="Ke portal publik Buruan SAE">
                    <img src="{{ asset('images/brand/logo-horizontal.png') }}" alt="Buruan Saé Utama" width="898" height="360" class="h-14 w-auto">
                </a>
                <h1 class="mt-8 text-2xl font-extrabold tracking-tight text-slate-900">Masuk ke dashboard</h1>
                <p class="mt-2 text-sm text-slate-600">Khusus pengelola dan penyuluh DKPP Kota Bandung.</p>

                @if (session('status'))
                    <p class="mt-6 rounded-xl bg-brand-50 p-3 text-sm text-brand-900 ring-1 ring-brand-200" role="status">{{ session('status') }}</p>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5" novalidate>
                    @csrf
                    <x-form.input name="login" label="Username atau email" :value="old('login')" required autofocus autocomplete="username" />
                    <div x-data="{ show: false }">
                        <x-form.input name="password" label="Kata sandi" type="password" x-bind:type="show ? 'text' : 'password'" required autocomplete="current-password" />
                        <label class="mt-2 flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" x-model="show" class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"> Tampilkan kata sandi
                        </label>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        Ingat saya di perangkat ini
                    </label>
                    <button type="submit" class="btn btn-primary w-full py-3">
                        <x-heroicon-o-arrow-right-end-on-rectangle class="size-5" aria-hidden="true" /> Masuk
                    </button>
                </form>

                <p class="mt-8 text-xs leading-5 text-slate-500">
                    Lupa kata sandi? Hubungi admin {{ config('buruansae.agency_short') }} untuk disetel ulang.
                </p>
                <a href="{{ route('home') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:text-brand-800">
                    <x-heroicon-m-arrow-left class="size-4" aria-hidden="true" /> Kembali ke portal publik
                </a>
            </div>
        </div>
    </main>
</body>
</html>
