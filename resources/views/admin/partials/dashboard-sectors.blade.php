{{-- Kartu input per sektor. $sectors berisi hitungan yang sudah dibatasi sesuai peran. --}}
@php($canCreate = auth()->user()->can('create', App\Models\Production::class))
<section aria-labelledby="sektor-title" class="mt-10">
    <h2 id="sektor-title" class="text-lg font-bold text-slate-900">Input data per sektor</h2>
    <ul class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($sectors as $item)
            <li class="card flex flex-col p-4">
                <div class="flex items-center gap-3">
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 p-1.5 ring-1 ring-brand-100">
                        <img src="{{ asset($item->type->image()) }}" alt="" class="max-h-full max-w-full object-contain">
                    </span>
                    <h3 class="font-bold text-slate-900">{{ $item->type->label() }}</h3>
                </div>
                <dl class="mt-4 grid grid-cols-3 gap-2 text-center">
                    <div class="rounded-xl bg-slate-50 px-1 py-2">
                        <dt class="text-[11px] font-medium text-slate-500">Berjalan</dt>
                        <dd class="num text-base font-bold text-slate-900">{{ format_number($item->running, 0) }}</dd>
                    </div>
                    <div @class(['rounded-xl px-1 py-2', 'bg-red-50' => $item->late > 0, 'bg-slate-50' => $item->late === 0])>
                        <dt class="text-[11px] font-medium text-slate-600">Terlambat</dt>
                        <dd @class(['num text-base font-bold', 'text-red-700' => $item->late > 0, 'text-slate-900' => $item->late === 0])>{{ format_number($item->late, 0) }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-1 py-2">
                        <dt class="text-[11px] font-medium text-slate-500">Panen bln ini</dt>
                        <dd class="num text-base font-bold text-slate-900">{{ format_number($item->harvested_this_month, 0) }}</dd>
                    </div>
                </dl>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('admin.productions.index', $item->type) }}" class="btn btn-secondary flex-1 px-3 py-2">Lihat data</a>
                    @if ($canCreate)
                        <a href="{{ route('admin.productions.create', $item->type) }}" class="btn btn-primary px-3 py-2" title="Tambah data {{ strtolower($item->type->label()) }}">
                            <x-heroicon-m-plus class="size-4" aria-hidden="true" /><span class="sr-only">Tambah data {{ strtolower($item->type->label()) }}</span>
                        </a>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
</section>
