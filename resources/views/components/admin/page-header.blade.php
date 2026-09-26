@props(['title', 'description' => null, 'breadcrumbs' => [], 'image' => null])
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="flex min-w-0 items-center gap-4">
        @if ($image)
            <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-white p-2 ring-1 ring-slate-200">
                <img src="{{ asset($image) }}" alt="" class="max-h-full max-w-full object-contain">
            </span>
        @endif
        <div class="min-w-0">
            @if ($breadcrumbs)
                <x-breadcrumb :items="$breadcrumbs" class="mb-1" />
            @endif
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ $title }}</h1>
            @if ($description)
                <p class="mt-1 text-sm text-slate-600">{{ $description }}</p>
            @endif
        </div>
    </div>
    @if (isset($actions))
        <div class="flex shrink-0 flex-wrap gap-2">{{ $actions }}</div>
    @endif
</div>
