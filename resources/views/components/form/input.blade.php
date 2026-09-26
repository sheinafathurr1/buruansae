{{-- Input berlabel + pesan galat. name boleh berbentuk array: feed[quantity] → galat "feed.quantity". --}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'required' => false, 'suffix' => null, 'id' => null])
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
    <div class="relative">
        <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}"
               @if ($required) required @endif
               @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
               @if ($hasError) aria-invalid="true" @endif
               @if ($suffix) style="padding-right: calc(1.75rem + {{ mb_strlen($suffix) }}ch)" @endif
               {{ $attributes->except('class')->class(['form-control', 'border-red-400 focus:border-red-500 focus:ring-red-100' => $hasError]) }}>
        @if ($suffix)
            <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-sm text-slate-500">{{ $suffix }}</span>
        @endif
    </div>
    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif
    @error($key)
        <p id="{{ $id }}-error" class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
