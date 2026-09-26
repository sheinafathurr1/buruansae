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
        <div class="relative hidden overflow-hidden bg-brand-900 lg:block">
            <img src="{{ asset('images/hero/farm.webp') }}" alt="" class="absolute inset-0 size-full object-cover opacity-40">
            <div class="absolute inset-0 bg-gradient-to-t from-brand-950 via-brand-950/60 to-transparent"></div>
            <div class="relative flex h-full flex-col justify-end p-12 text-white">
                <p class="text-sm font-semibold tracking-widest text-brand-200 uppercase">{{ config('buruansae.agency_short') }}</p>
                <p class="mt-3 max-w-md text-3xl font-extrabold leading-tight">Dashboard pengelola data Buruan SAE</p>
                <p class="mt-3 max-w-md text-brand-100">Catat data tanam, panen, dan penyaluran hasil kelompok. Data langsung tampil di portal publik.</p>
            </div>
        </div>

        <div class="flex items-center justify-center px-6 py-12 sm:px-12">
            <div class="w-full max-w-sm">
                <a href="{{ route('home') }}" class="inline-block" aria-label="Ke portal publik Buruan SAE">
                    <img src="{{ asset('images/brand/logo-buruansae-dkpp.png') }}" alt="Buruan SAE dan DKPP Kota Bandung" class="h-12 w-auto">
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
