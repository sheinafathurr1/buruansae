@props(['code', 'title', 'message'])
<x-layouts.app :title="$title">
    <section class="container-page max-w-3xl py-20 lg:py-28">
        <p class="figure-number text-7xl text-leaf-700 sm:text-8xl">{{ $code }}</p>
        <h1 class="display mt-4 border-t border-ink pt-4 text-3xl leading-tight sm:text-4xl">{{ $title }}</h1>
        <p class="mt-3 max-w-md text-ink-soft">{{ $message }}</p>
        <p class="mt-8 flex flex-wrap gap-x-6 gap-y-3">
            <a href="{{ route('home') }}" class="button button-primary">Ke beranda</a>
            <a href="{{ route('map') }}" class="link self-center">Peta sebaran kelompok</a>
        </p>
    </section>
</x-layouts.app>
