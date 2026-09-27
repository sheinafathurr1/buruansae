@use('App\Enums\SectorType')
{{-- Navigasi bawah untuk layar kecil (gaya aplikasi, seperti versi lama). --}}
<div x-data="{ sheet: false }" class="lg:hidden" @keydown.escape.window="sheet = false">
    <nav aria-label="Navigasi utama" class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur">
        <ul class="mx-auto grid h-16 max-w-lg grid-cols-4 text-[11px] font-semibold">
            @foreach ([
                ['Beranda', route('home'), request()->routeIs('home'), 'home'],
                ['Sektor', null, request()->routeIs('sectors.*'), 'squares-2x2'],
                ['Peta', route('map'), request()->routeIs('map'), 'map'],
                ['Berita', route('news.index'), request()->routeIs('news.*'), 'newspaper'],
            ] as [$label, $href, $active, $icon])
                <li>
                    @if ($href)
                        <a href="{{ $href }}" @if($active) aria-current="page" @endif
                           @class(['flex h-full flex-col items-center justify-center gap-1', 'text-brand-700' => $active, 'text-slate-600' => ! $active])>
                            <x-dynamic-component :component="'heroicon-'.($active ? 's' : 'o').'-'.$icon" class="size-6" aria-hidden="true" />
                            {{ $label }}
                        </a>
                    @else
                        <button type="button" @click="sheet = true" aria-haspopup="dialog" :aria-expanded="sheet.toString()" aria-expanded="false"
                            @class(['flex h-full w-full flex-col items-center justify-center gap-1', 'text-brand-700' => $active, 'text-slate-600' => ! $active])>
                            <x-dynamic-component :component="'heroicon-'.($active ? 's' : 'o').'-'.$icon" class="size-6" aria-hidden="true" />
                            {{ $label }}
                        </button>
                    @endif
                </li>
            @endforeach
        </ul>
    </nav>

    <div x-show="sheet" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-labelledby="sheet-sektor-title">
        <div x-show="sheet" x-transition.opacity class="absolute inset-0 bg-slate-900/50" @click="sheet = false"></div>
        <div x-show="sheet" x-trap.noscroll="sheet"
             x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
             x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
             class="absolute inset-x-0 bottom-0 max-h-[85vh] overflow-y-auto rounded-t-3xl bg-white px-4 pt-3 pb-[calc(1.5rem+env(safe-area-inset-bottom))] shadow-2xl">
            <div class="mx-auto mb-3 h-1.5 w-10 rounded-full bg-slate-200" aria-hidden="true"></div>
            <div class="mb-3 flex items-center justify-between">
                <h2 id="sheet-sektor-title" class="text-base font-bold text-slate-900">Pilih data sektor</h2>
                <button type="button" @click="sheet = false" class="rounded-full p-2 text-slate-500 hover:bg-slate-100" aria-label="Tutup">
                    <x-heroicon-o-x-mark class="size-5" aria-hidden="true" />
                </button>
            </div>
            <ul class="grid grid-cols-2 gap-2">
                @foreach (SectorType::cases() as $case)
                    <li>
                        <a href="{{ route('sectors.show', $case) }}" class="flex items-center gap-2.5 rounded-2xl border border-slate-200 p-2.5 text-sm font-semibold text-slate-800 active:bg-brand-50">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 p-1.5">
                                <img src="{{ asset($case->image()) }}" alt="" class="max-h-full max-w-full object-contain" loading="lazy">
                            </span>
                            <span class="leading-tight">{{ $case->label() }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
