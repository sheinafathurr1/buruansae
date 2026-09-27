@props(['title', 'description' => null, 'breadcrumbs' => [], 'image' => null])
{{-- Kepala halaman: langit → judul → lapisan air & tanah (motif emblem), seragam di halaman publik. --}}
<section class="relative overflow-hidden bg-gradient-to-b from-sky-50 via-sky-100 to-sky-200">
    <div class="container-page relative flex items-end justify-between gap-8 pt-6 pb-16 sm:pb-20 lg:pt-8">
        <div class="min-w-0 pb-2">
            @if ($breadcrumbs)
                <x-breadcrumb :items="$breadcrumbs" />
            @endif
            <h1 class="display mt-5 text-4xl leading-[1.08] sm:text-5xl">{{ $title }}</h1>
            @if ($description)
                <p class="mt-3 max-w-2xl text-lg leading-8 text-ink-soft">{{ $description }}</p>
            @endif
            {{ $slot }}
        </div>
        @if ($image)
            <div class="arch hidden aspect-[4/5] w-36 shrink-0 items-end justify-center bg-white/70 px-4 pb-5 ring-8 ring-white sm:flex lg:w-44">
                <img src="{{ asset($image) }}" alt="" class="h-[70%] w-auto max-w-full object-contain">
            </div>
        @endif
    </div>
    <x-strata class="absolute inset-x-0 bottom-0" height="h-10 sm:h-14" />
</section>
