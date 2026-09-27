@props(['action', 'confirm', 'label' => 'Hapus'])
<form method="POST" action="{{ $action }}" class="inline" onsubmit="return confirm(@js($confirm))">
    @csrf
    @method('DELETE')
    <button type="submit" {{ $attributes->class('inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-50') }}>
        <x-heroicon-o-trash class="size-4" aria-hidden="true" /> {{ $slot->isEmpty() ? $label : $slot }}
    </button>
</form>
