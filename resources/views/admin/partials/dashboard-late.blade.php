{{-- Siklus yang perkiraan panennya sudah lewat. --}}
<section aria-labelledby="terlambat-title" class="card mt-10">
    <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">
        <div>
            <h2 id="terlambat-title" class="text-base font-bold text-slate-900">Perlu dicatat panennya</h2>
            <p class="mt-0.5 text-sm text-slate-500">Siklus yang perkiraan tanggal panennya sudah lewat, dari yang paling lama.</p>
        </div>
    </header>
    @if ($lateProductions->isEmpty())
        <x-empty-state title="Tidak ada siklus yang terlambat panen" icon="heroicon-o-check-circle" compact />
    @else
        <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel siklus terlambat panen">
            <table class="table-base table-stack">
                <thead>
                    <tr>
                        <th scope="col">Komoditas</th>
                        <th scope="col">Kelompok</th>
                        <th scope="col">Perkiraan panen</th>
                        <th scope="col"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lateProductions as $production)
                        @php($type = \App\Enums\SectorType::fromCode($production->commodity->sector->code))
                        <tr>
                            <td data-label="Komoditas">
                                <span class="font-semibold text-slate-900">{{ display_name($production->commodity->name) }}</span>
                                <span class="block text-xs text-slate-500">{{ $type?->label() }}</span>
                            </td>
                            <td data-label="Kelompok">
                                {{ $production->farmerGroup->name }}
                                <span class="block text-xs text-slate-500">Kel. {{ display_name($production->farmerGroup->village?->name) }}</span>
                            </td>
                            <td class="whitespace-nowrap" data-label="Perkiraan panen">
                                {{ format_date($production->estimated_harvest_date) }}
                                <span class="block text-xs text-red-700">{{ (int) $production->estimated_harvest_date->diffInDays($today) }} hari lalu</span>
                            </td>
                            <td class="table-actions text-right">
                                @if ($type)
                                    <a href="{{ route('admin.productions.harvest.edit', [$type, $production]) }}" class="btn btn-primary px-3 py-1.5 whitespace-nowrap">
                                        Catat {{ strtolower($type->harvestTerm()) }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
