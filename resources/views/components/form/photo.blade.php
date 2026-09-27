{{-- Unggah foto dengan pratinjau; $current = URL foto yang sudah ada. --}}
@props(['name', 'label', 'current' => null, 'hint' => null, 'required' => false, 'removable' => false])
@php
    $hasError = $errors->has($name);
    $describedBy = trim(($hint ? $name.'-hint ' : '').($hasError ? $name.'-error' : ''));
@endphp
<div {{ $attributes->class('min-w-0') }} x-data="{ preview: @js($current), removed: false }">
    <p class="form-label" id="{{ $name }}-label">
        {{ $label }} @if ($required)<span class="text-red-600" aria-hidden="true">*</span>@endif
    </p>
    <div class="flex items-start gap-4">
        <div class="flex size-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-slate-100 ring-1 ring-slate-200">
            <template x-if="preview && !removed"><img :src="preview" alt="Pratinjau {{ strtolower($label) }}" class="size-full object-cover"></template>
            <template x-if="!preview || removed"><x-heroicon-o-photo class="size-8 text-slate-400" aria-hidden="true" /></template>
        </div>
        <div class="min-w-0 flex-1 space-y-2">
            <input type="file" id="{{ $name }}" name="{{ $name }}" accept="image/jpeg,image/png,image/webp"
                   aria-labelledby="{{ $name }}-label" @if ($required) required @endif
                   @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                   @if ($hasError) aria-invalid="true" @endif
                   @change="const f = $event.target.files[0]; if (f) { preview = URL.createObjectURL(f); removed = false }"
                   class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-800 hover:file:bg-brand-100">
            @if ($removable && $current)
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remove_{{ $name }}" value="1" x-model="removed" class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Hapus foto saat ini
                </label>
            @endif
            @if ($hint)
                <p id="{{ $name }}-hint" class="text-xs text-slate-500">{{ $hint }}</p>
            @endif
            @error($name)
                <p id="{{ $name }}-error" class="text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
