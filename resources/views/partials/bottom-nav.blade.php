@use('App\Enums\SectorType')
{{-- Navigasi bawah untuk layar kecil (gaya aplikasi, seperti versi lama). --}}
<div x-data="{ sheet: false }" class="lg:hidden" @keydown.escape.window="sheet = false">
    <nav aria-label="Navigasi utama" class="fixed inset-x-0 bottom-0 z-40 border-t border-rule bg-paper/95 pb-[env(safe-area-inset-bottom)] backdrop-blur">
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
                           @class(['flex h-full flex-col items-center justify-center gap-1', 'text-leaf-700' => $active, 'text-ink-muted' => ! $active])>
                            <x-dynamic-component :component="'heroicon-'.($active ? 's' : 'o').'-'.$icon" class="size-6" aria-hidden="true" />
                            {{ $label }}
                        </a>
                    @else
                        <button type="button" @click="sheet = true" aria-haspopup="dialog" :aria-expanded="sheet.toString()" aria-expanded="false"
                            @class(['flex h-full w-full flex-col items-center justify-center gap-1', 'text-leaf-700' => $active, 'text-ink-muted' => ! $active])>
                            <x-dynamic-component :component="'heroicon-'.($active ? 's' : 'o').'-'.$icon" class="size-6" aria-hidden="true" />
                            {{ $label }}
                        </button>
                    @endif
                </li>
            @endforeach
        </ul>
    </nav>

    <div x-show="sheet" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-labelledby="sheet-sektor-title">
        <div x-show="sheet" x-transition.opacity class="absolute inset-0 bg-ink/50" @click="sheet = false"></div>
        <div x-show="sheet" x-trap.noscroll="sheet"
             x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
             x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
             class="absolute inset-x-0 bottom-0 max-h-[85vh] overflow-y-auto rounded-t-xl bg-paper px-4 pt-3 pb-[calc(1.5rem+env(safe-area-inset-bottom))] shadow-2xl">
            <div class="mx-auto mb-3 h-1.5 w-10 rounded-full bg-rule" aria-hidden="true"></div>
            <div class="mb-3 flex items-center justify-between">
                <h2 id="sheet-sektor-title" class="display text-lg">Pilih data sektor</h2>
                <button type="button" @click="sheet = false" class="rounded-md p-2 text-ink-muted hover:bg-paper-deep" aria-label="Tutup">
                    <x-heroicon-o-x-mark class="size-5" aria-hidden="true" />
                </button>
            </div>
            <ul class="grid grid-cols-2 border-t border-rule">
                @foreach (SectorType::cases() as $case)
                    <li class="border-b border-rule odd:border-r">
                        <a href="{{ route('sectors.show', $case) }}" class="flex items-center gap-2.5 px-2 py-3 text-sm font-semibold text-ink active:bg-white">
                            <img src="{{ asset($case->image()) }}" alt="" class="size-9 shrink-0 object-contain" loading="lazy">
                            <span class="leading-tight">{{ $case->label() }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
