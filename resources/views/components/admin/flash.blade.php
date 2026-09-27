@foreach (['success' => ['bg-brand-50 text-brand-900 ring-brand-200', 'check-circle', 'status'], 'error' => ['bg-red-50 text-red-900 ring-red-200', 'exclamation-triangle', 'alert'], 'status' => ['bg-blue-50 text-blue-900 ring-blue-200', 'information-circle', 'status']] as $key => [$tone, $icon, $role])
    @if (session($key))
        <div x-data="{ show: true }" x-show="show" role="{{ $role }}" class="mb-6 flex items-start gap-3 rounded-2xl p-4 text-sm ring-1 {{ $tone }}">
            <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-5 shrink-0" aria-hidden="true" />
            <p class="flex-1">{{ session($key) }}</p>
            <button type="button" @click="show = false" class="-m-1 rounded-lg p-1 opacity-70 hover:opacity-100" aria-label="Tutup pesan">
                <x-heroicon-m-x-mark class="size-4" aria-hidden="true" />
            </button>
        </div>
    @endif
@endforeach
