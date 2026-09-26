@props(['name', 'label', 'hint' => null, 'required' => false, 'id' => null])
@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $id ??= str_replace('.', '_', $key);
    $hasError = $errors->has($key);
    $describedBy = trim(($hint ? $id.'-hint ' : '').($hasError ? $id.'-error' : ''));
@endphp
<div {{ $attributes->only('class')->class('min-w-0') }}>
    <label for="{{ $id }}" class="form-label">
        {{ $label }} @if ($required)<span class="text-red-600" aria-hidden="true">*</span>@endif
    </label>
    {{-- aria-label menjaga nama aksesibel bila select diganti Tom Select. --}}
    <select id="{{ $id }}" name="{{ $name }}" aria-label="{{ $label }}" @if ($required) required @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($hasError) aria-invalid="true" @endif
            {{ $attributes->except('class')->class(['form-control', 'border-red-400' => $hasError]) }}>
        {{ $slot }}
    </select>
    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif
    @error($key)
        <p id="{{ $id }}-error" class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
