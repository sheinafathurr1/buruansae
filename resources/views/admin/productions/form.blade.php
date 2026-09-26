@php
    $editing = $production->exists;
    $title = ($editing ? 'Ubah data ' : 'Tambah data ').strtolower($sector->label());
    $detail = $production->processedProductDetail;
    $feed = $editing ? $production->inputs->first(fn ($i) => $i->type === \App\Enums\InputType::Feed) : null;

    $groupInfo = $groups->mapWithKeys(fn ($g) => [$g->id => [
        'officer' => $g->extension_officer,
        'facilitator' => $g->facilitator,
        'village' => display_name($g->village?->name),
        'district' => display_name($g->village?->district?->name),
        'rw' => $g->rw,
    ]]);
    $growingDays = $commodities->mapWithKeys(fn ($c) => [$c->id => $c->growing_days]);

    $state = [
        'groupId' => (string) old('farmer_group_id', $production->farmer_group_id),
        'commodityId' => (string) old('commodity_id', $production->commodity_id),
        'startDate' => old('start_date', $production->start_date?->toDateString()),
        'estimatedDate' => old('estimated_harvest_date', $production->estimated_harvest_date?->toDateString()),
    ];
    $decimal = fn ($value) => $value !== null ? (float) $value : null;
@endphp
<x-layouts.admin :title="$title">
    <x-admin.page-header :title="$title" :image="$sector->image()"
                         :breadcrumbs="[['Data '.$sector->label(), route('admin.productions.index', $sector)], [$editing ? 'Ubah' : 'Tambah', null]]" />

    <form method="POST" novalidate class="max-w-4xl space-y-6"
          action="{{ $editing ? route('admin.productions.update', [$sector, $production]) : route('admin.productions.store', $sector) }}"
          x-data="productionForm(@js($state), @js($groupInfo), @js($growingDays))">
        @csrf
        @if ($editing) @method('PUT') @endif

        @if ($errors->any())
            <div class="rounded-2xl bg-red-50 p-4 text-sm text-red-900 ring-1 ring-red-200" role="alert">
                Periksa kembali isian yang ditandai merah ({{ $errors->count() }} galat).
            </div>
        @endif

        <section class="card p-5 sm:p-6" aria-labelledby="kelompok-title">
            <h2 id="kelompok-title" class="text-base font-bold text-slate-900">Kelompok</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <x-form.select name="farmer_group_id" label="Kelompok" required data-searchable class="sm:col-span-2"
                               x-on:change="groupId = $event.target.value" hint="Ketik nama kelompok, kelurahan, atau kecamatan.">
                    <option value="">Pilih kelompok</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}" @selected($state['groupId'] === (string) $group->id)
                                data-data="{{ json_encode(['search' => ($group->village?->name).' '.($group->village?->district?->name)]) }}">{{ $group->name }}</option>
                    @endforeach
                </x-form.select>
                <dl class="grid gap-3 rounded-2xl bg-slate-50 p-4 text-sm sm:col-span-2 sm:grid-cols-4" x-show="group" x-cloak aria-live="polite">
                    <div><dt class="text-xs text-slate-500">Penyuluh</dt><dd class="font-semibold text-slate-800" x-text="group?.officer || '–'"></dd></div>
                    <div><dt class="text-xs text-slate-500">Pendamping</dt><dd class="font-semibold text-slate-800" x-text="group?.facilitator || '–'"></dd></div>
                    <div><dt class="text-xs text-slate-500">Kelurahan</dt><dd class="font-semibold text-slate-800" x-text="group ? group.village + (group.rw ? ' · RW ' + group.rw : '') : '–'"></dd></div>
                    <div><dt class="text-xs text-slate-500">Kecamatan</dt><dd class="font-semibold text-slate-800" x-text="group?.district || '–'"></dd></div>
                </dl>
            </div>
        </section>

        <section class="card p-5 sm:p-6" aria-labelledby="tanam-title">
            <h2 id="tanam-title" class="text-base font-bold text-slate-900">{{ $sector === \App\Enums\SectorType::ProcessedProduct ? 'Data produk' : 'Data tanam' }}</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <x-form.select name="commodity_id" :label="$sector->commodityLabel()" required data-searchable x-on:change="commodityId = $event.target.value"
                               :hint="$commodities->isEmpty() ? 'Belum ada komoditas untuk sektor ini. Tambahkan dulu di menu Komoditas.' : null">
                    <option value="">Pilih {{ strtolower($sector->commodityLabel()) }}</option>
                    @foreach ($commodities as $commodity)
                        <option value="{{ $commodity->id }}" @selected($state['commodityId'] === (string) $commodity->id)>{{ display_name($commodity->name) }}</option>
                    @endforeach
                </x-form.select>

                @if ($sector->plantingCategories())
                    <fieldset>
                        <legend class="form-label">Kategori tanam <span class="text-red-600" aria-hidden="true">*</span></legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($sector->plantingCategories() as $category)
                                <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-300 px-3.5 py-2 text-sm has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:text-brand-900">
                                    <input type="radio" name="planting_category" value="{{ $category->value }}" required
                                           @checked(old('planting_category', $production->planting_category?->value) === $category->value)
                                           class="size-4 border-slate-300 text-brand-600 focus:ring-brand-500">
                                    {{ $category->label() }}
                                </label>
                            @endforeach
                        </div>
                        @error('planting_category')<p class="mt-1.5 text-sm text-red-700">{{ $message }}</p>@enderror
                    </fieldset>
                @endif

                <x-form.input name="start_date" :label="$sector->startDateLabel()" type="date" required x-model="startDate" :value="$state['startDate']" />

                @if ($sector->initialQuantityLabel())
                    <x-form.input name="initial_quantity" :label="$sector->initialQuantityLabel()" type="number" min="0" step="any" inputmode="decimal" required
                                  :suffix="$sector->initialQuantityUnit()" :value="old('initial_quantity', $decimal($production->initial_quantity))" />
                @endif
            </div>
        </section>

        @if ($sector->tracksHarvestEstimate())
            <section class="card p-5 sm:p-6" aria-labelledby="perkiraan-title">
                <h2 id="perkiraan-title" class="text-base font-bold text-slate-900">Perkiraan panen</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Tanggal perkiraan dihitung otomatis dari {{ strtolower($sector->startDateLabel()) }} + durasi tanam komoditas<span x-show="growingDays" x-cloak> (<span x-text="growingDays"></span> hari)</span>. Boleh diubah bila perlu.
                </p>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <x-form.input name="estimated_harvest_quantity" label="Perkiraan jumlah panen" type="number" min="0" step="any" inputmode="decimal" required
                                  :suffix="$unit" :value="old('estimated_harvest_quantity', $decimal($production->estimated_harvest_quantity))" />
                    <div>
                        <x-form.input name="estimated_harvest_date" label="Perkiraan tanggal panen" type="date" x-model="estimatedDate" x-on:input="autoDate = false" :value="$state['estimatedDate']" />
                        <button type="button" x-show="!autoDate && computedDate" x-cloak @click="autoDate = true; estimatedDate = computedDate"
                                class="mt-1.5 text-sm font-semibold text-brand-700 hover:underline">Pakai tanggal otomatis (<span x-text="computedLabel"></span>)</button>
                    </div>
                </div>
            </section>
        @endif

        @if ($sector->tracksFeed())
            <section class="card p-5 sm:p-6" aria-labelledby="pakan-title">
                <h2 id="pakan-title" class="text-base font-bold text-slate-900">Pemberian pakan <span class="text-sm font-normal text-slate-500">(opsional)</span></h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-3">
                    <x-form.input name="feed[applied_date]" label="Tanggal pakan" type="date" :value="old('feed.applied_date', $feed?->applied_date?->toDateString())" />
                    <x-form.input name="feed[name]" label="Jenis pakan" :value="old('feed.name', $feed?->name)" maxlength="150" placeholder="mis. Pelet" />
                    <x-form.input name="feed[quantity]" label="Jumlah pakan" type="number" min="0" step="any" inputmode="decimal" suffix="kg" :value="old('feed.quantity', $decimal($feed?->quantity))" />
                </div>
            </section>
        @endif

        @if ($sector === \App\Enums\SectorType::ProcessedProduct)
            <section class="card p-5 sm:p-6" aria-labelledby="produk-title">
                <h2 id="produk-title" class="text-base font-bold text-slate-900">Detail produk &amp; perizinan</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <x-form.input name="base_ingredient" label="Bahan dasar" :value="old('base_ingredient', $detail?->base_ingredient)" required maxlength="255" />
                    <x-form.input name="brand" label="Merek" :value="old('brand', $detail?->brand)" required maxlength="150" />
                    <x-form.textarea name="recipe" label="Resep" :value="old('recipe', $detail?->recipe)" class="sm:col-span-2" />
                    <x-form.input name="pirt_permit" label="Izin PIRT" :value="old('pirt_permit', $detail?->pirt_permit)" maxlength="255" hint="Nomor izin, atau kosongkan bila belum ada." />
                    <x-form.input name="halal_permit" label="Sertifikat halal" :value="old('halal_permit', $detail?->halal_permit)" maxlength="255" />
                    <x-form.input name="lab_test" label="Hasil uji lab" :value="old('lab_test', $detail?->lab_test)" maxlength="255" />
                </div>
            </section>
        @endif

        <section class="card grid gap-5 p-5 sm:grid-cols-2 sm:p-6" aria-label="Keterangan">
            @if ($sector === \App\Enums\SectorType::Nursery)
                <x-form.input name="origin" label="Asal bibit" :value="old('origin', $production->seedlingDetail?->origin)" maxlength="100" list="origin-options" />
                <datalist id="origin-options"><option value="DKPP"><option value="Swadaya"><option value="Bantuan CSR"></datalist>
            @endif
            <x-form.input name="notes" label="Keterangan" :value="old('notes', $production->notes)" maxlength="255" hint="Opsional." :class="$sector !== \App\Enums\SectorType::Nursery ? 'sm:col-span-2' : ''" />
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.productions.index', $sector) }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary"><x-heroicon-o-check class="size-5" aria-hidden="true" /> Simpan data</button>
        </div>
    </form>
</x-layouts.admin>
