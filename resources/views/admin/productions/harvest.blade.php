@use('App\Enums\DistributionGroup')
@php
    $term = strtolower($sector->harvestTerm());
    $title = ($production->harvest_date ? 'Ubah data ' : 'Catat data ').$term.' '.strtolower($sector->label());
    $decimal = fn ($value) => $value !== null && $value !== '' ? (float) $value : null;

    $rows = $categories->map(function ($category) use ($distributions, $decimal) {
        $existing = $distributions->get($category->id);

        return (object) [
            'category' => $category,
            'group' => DistributionGroup::fromCategoryCode($category->code),
            'quantity' => old("distributions.{$category->code}.quantity", $decimal($existing?->quantity)),
            'households' => old("distributions.{$category->code}.household_count", $existing?->household_count),
            'persons' => old("distributions.{$category->code}.person_count", $existing?->person_count),
        ];
    });
    $initialQuantities = $rows->mapWithKeys(fn ($r) => [$r->category->code => $r->quantity === null ? '' : (string) $r->quantity]);
    $groupDescriptions = [
        DistributionGroup::SelfConsumption->value => 'Hasil yang dikonsumsi sendiri oleh anggota kelompok.',
        DistributionGroup::Shared->value => 'Hasil yang dibagikan gratis kepada penerima manfaat.',
        DistributionGroup::Sold->value => 'Hasil yang dijual.',
    ];
@endphp
<x-layouts.admin :title="$title">
    <x-admin.page-header :title="$title" :image="$sector->image()"
                         :breadcrumbs="[['Data '.$sector->label(), route('admin.productions.index', $sector)], ['Data '.$term, null]]" />

    <div class="grid gap-6 xl:grid-cols-[1fr_20rem] xl:items-start">
        <form method="POST" enctype="multipart/form-data" novalidate class="min-w-0 space-y-6"
              action="{{ route('admin.productions.harvest.update', [$sector, $production]) }}"
              x-data="harvestForm(@js($initialQuantities))">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="rounded-2xl bg-red-50 p-4 text-sm text-red-900 ring-1 ring-red-200" role="alert">
                    <p>Periksa kembali isian yang ditandai merah ({{ $errors->count() }} galat).</p>
                    @error('distributions')<p class="mt-1 font-semibold">{{ $message }}</p>@enderror
                </div>
            @endif

            <section class="card p-5 sm:p-6" aria-labelledby="hasil-title">
                <h2 id="hasil-title" class="text-base font-bold text-slate-900">Hasil {{ $term }}</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <x-form.input name="harvest_date" :label="'Tanggal '.$term" type="date" required
                                  :max="today()->toDateString()" :min="$production->start_date?->toDateString()"
                                  :value="old('harvest_date', $production->harvest_date?->toDateString() ?? today()->toDateString())" />
                    @if ($sector->tracksHeadCount())
                        <x-form.input name="harvest_head_count" label="Jumlah panen (ekor)" type="number" min="0" step="1" inputmode="numeric" suffix="ekor" required
                                      :value="old('harvest_head_count', $decimal($production->harvest_head_count))" />
                    @endif
                    <x-form.photo name="photo" :label="'Foto hasil '.$term" :current="$production->image_url" :required="$production->image === null" class="sm:col-span-2"
                                  :hint="($errors->any() && $production->image === null ? 'Pilih ulang foto setelah memperbaiki isian lain. ' : '').'Wajib saat pertama kali mencatat. JPG/PNG/WebP, maks. 8 MB — foto dari ponsel otomatis diperkecil.'" />
                </div>
            </section>

            @foreach (DistributionGroup::cases() as $group)
                @php($groupRows = $rows->filter(fn ($r) => $r->group === $group))
                @continue($groupRows->isEmpty())
                <section class="card p-5 sm:p-6" aria-labelledby="penyaluran-{{ $group->value }}">
                    <h2 id="penyaluran-{{ $group->value }}" class="text-base font-bold text-slate-900">{{ $group->label() }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $groupDescriptions[$group->value] }} Kosongkan kategori yang tidak ada.</p>
                    <div class="mt-4 space-y-4">
                        @foreach ($groupRows as $row)
                            @php($code = $row->category->code)
                            <fieldset class="rounded-2xl border border-slate-200 p-4">
                                <legend @class(['px-1 text-sm font-semibold text-slate-800', 'sr-only' => $groupRows->count() === 1])>{{ $row->category->name }}</legend>
                                <div class="grid gap-4 sm:grid-cols-3">
                                    <x-form.input :name="'distributions['.$code.'][quantity]'" label="Jumlah" type="number" min="0" step="any" inputmode="decimal" :suffix="$unit"
                                                  :value="$row->quantity" x-model="quantities['{{ $code }}']" />
                                    <x-form.input :name="'distributions['.$code.'][household_count]'" label="Jumlah KK" type="number" min="0" step="1" inputmode="numeric" suffix="KK"
                                                  :value="$row->households" />
                                    <x-form.input :name="'distributions['.$code.'][person_count]'" label="Jumlah orang" type="number" min="0" step="1" inputmode="numeric" suffix="orang"
                                                  :value="$row->persons" />
                                </div>
                            </fieldset>
                        @endforeach
                        @if ($group === DistributionGroup::Sold)
                            <x-form.input name="selling_price" label="Total harga jual" type="number" min="0" step="1" inputmode="numeric"
                                          :value="old('selling_price', $production->selling_price)" hint="Dalam rupiah, tanpa titik. Wajib bila ada hasil yang dijual." class="sm:max-w-xs" />
                        @endif
                    </div>
                </section>
            @endforeach

            @if ($sector->tracksFertilizer())
                <section class="card p-5 sm:p-6" aria-labelledby="pupuk-title">
                    <h2 id="pupuk-title" class="text-base font-bold text-slate-900">Pemupukan <span class="text-sm font-normal text-slate-500">(opsional)</span></h2>
                    <div class="mt-5 grid gap-5 sm:grid-cols-3">
                        <x-form.input name="fertilizer[applied_date]" label="Tanggal pemupukan" type="date" :value="old('fertilizer.applied_date', $fertilizer?->applied_date?->toDateString())" />
                        <x-form.input name="fertilizer[name]" label="Jenis pupuk" :value="old('fertilizer.name', $fertilizer?->name)" maxlength="150" placeholder="mis. Kompos" />
                        <x-form.input name="fertilizer[quantity]" label="Jumlah pupuk" type="number" min="0" step="any" inputmode="decimal" suffix="kg" :value="old('fertilizer.quantity', $decimal($fertilizer?->quantity))" />
                    </div>
                </section>
            @endif

            {{-- Total selalu terlihat saat mengisi form. --}}
            <div class="sticky bottom-[calc(1rem+env(safe-area-inset-bottom))] z-20 flex flex-col gap-3 rounded-2xl bg-brand-950 p-4 text-white shadow-2xl sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm" aria-live="polite">
                    Total hasil {{ $term }}:
                    <span class="num text-xl font-extrabold" x-text="totalLabel">{{ format_number($initialQuantities->sum(fn ($v) => (float) $v)) }}</span>
                    <span class="font-semibold">{{ $unit }}</span>
                    <span class="block text-xs text-brand-200">Dihitung dari konsumsi pribadi + dibagikan + dijual.</span>
                </p>
                <div class="flex gap-2">
                    <a href="{{ route('admin.productions.index', $sector) }}" class="btn border border-white/30 text-white hover:bg-white/10">Batal</a>
                    <button type="submit" class="btn bg-white text-brand-900 hover:bg-brand-50"><x-heroicon-o-check class="size-5" aria-hidden="true" /> Simpan data {{ $term }}</button>
                </div>
            </div>
        </form>

        <aside class="card p-5 xl:sticky xl:top-24" aria-labelledby="siklus-title">
            <h2 id="siklus-title" class="text-sm font-bold tracking-wide text-slate-500 uppercase">Data tanam</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div><dt class="text-xs text-slate-500">{{ $sector->commodityLabel() }}</dt><dd class="font-semibold text-slate-900">{{ display_name($production->commodity->name) }}</dd></div>
                <div>
                    <dt class="text-xs text-slate-500">Kelompok</dt>
                    <dd class="font-semibold text-slate-900">{{ $production->farmerGroup->name }}</dd>
                    <dd class="text-xs text-slate-500">Kel. {{ display_name($production->farmerGroup->village?->name) }}, Kec. {{ display_name($production->farmerGroup->village?->district?->name) }}</dd>
                </div>
                <div><dt class="text-xs text-slate-500">{{ $sector->startDateLabel() }}</dt><dd class="font-semibold text-slate-900">{{ format_date($production->start_date) }}</dd></div>
                @if ($sector->initialQuantityLabel() && $production->initial_quantity !== null)
                    <div><dt class="text-xs text-slate-500">{{ $sector->initialQuantityLabel() }}</dt><dd class="font-semibold text-slate-900">{{ format_quantity($production->initial_quantity, $sector->initialQuantityUnit()) }}</dd></div>
                @endif
                @if ($production->estimated_harvest_date)
                    <div>
                        <dt class="text-xs text-slate-500">Perkiraan panen</dt>
                        <dd class="font-semibold text-slate-900">{{ format_date($production->estimated_harvest_date) }}</dd>
                        @if ($production->estimated_harvest_quantity !== null)
                            <dd class="text-xs text-slate-500">± {{ format_quantity($production->estimated_harvest_quantity, $unit) }}</dd>
                        @endif
                    </div>
                @endif
            </dl>
            <a href="{{ route('admin.productions.edit', [$sector, $production]) }}" class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:underline">
                <x-heroicon-o-pencil-square class="size-4" aria-hidden="true" /> Ubah data tanam
            </a>
        </aside>
    </div>
</x-layouts.admin>
