@props(['tilt' => 0])
{{-- Daun dua warna dengan tulang daun putih, seperti tiga daun pada emblem logo. --}}
<svg {{ $attributes->merge(['viewBox' => '0 0 40 80', 'aria-hidden' => 'true', 'focusable' => 'false']) }} style="transform: rotate({{ $tilt }}deg)">
    <path d="M20 3C36 19 36 60 20 77 4 60 4 19 20 3Z" fill="#fff" />
    <path d="M20 7C7 21 7 58 20 73Z" fill="#8cc63f" />
    <path d="M20 7C33 21 33 58 20 73Z" fill="#2fae4a" />
    <path d="M20 9V71" stroke="#fff" stroke-width="1.5" />
</svg>
