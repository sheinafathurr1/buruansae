@props(['code', 'title', 'message'])
<x-layouts.app :title="$title">
    <section class="container-page flex flex-col items-center py-20 text-center lg:py-28">
        <p class="text-6xl font-extrabold tracking-tight text-brand-600 sm:text-7xl">{{ $code }}</p>
        <h1 class="mt-4 text-2xl font-extrabold text-slate-900 sm:text-3xl">{{ $title }}</h1>
        <p class="mt-3 max-w-md text-base text-slate-600">{{ $message }}</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('home') }}" class="btn btn-primary"><x-heroicon-o-home class="size-4" aria-hidden="true" /> Ke beranda</a>
            <a href="{{ route('map') }}" class="btn btn-secondary"><x-heroicon-o-map class="size-4" aria-hidden="true" /> Peta sebaran</a>
        </div>
    </section>
</x-layouts.app>
