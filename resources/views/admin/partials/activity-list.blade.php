{{-- Daftar riwayat aktivitas. $activities: koleksi/paginator App\Models\ActivityLog. --}}
@use('App\Enums\ActivityAction')
@php
    $tones = [
        'info' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'success' => 'bg-brand-50 text-brand-800 ring-brand-200',
        'critical' => 'bg-red-50 text-red-700 ring-red-200',
        'neutral' => 'bg-slate-100 text-slate-700 ring-slate-200',
    ];
    $now = now();
@endphp
<ul class="divide-y divide-slate-100">
    @foreach ($activities as $log)
        @php
            $harvestAction = in_array($log->action, [ActivityAction::HarvestRecorded, ActivityAction::HarvestUpdated], true);
            $link = $log->production_id && $log->sector
                ? route($harvestAction ? 'admin.productions.harvest.edit' : 'admin.productions.edit', [$log->sector, $log->production_id])
                : null;
        @endphp
        <li class="flex gap-3 px-5 py-3.5">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-800 uppercase" aria-hidden="true">
                {{ mb_substr($log->user?->name ?? '?', 0, 1) }}
            </span>
            <div class="min-w-0 flex-1 text-sm">
                <p class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    <span class="font-semibold text-slate-900">{{ $log->user?->name ?? 'Akun dihapus' }}</span>
                    <span class="inline-block rounded-full px-2 py-0.5 text-xs font-semibold whitespace-nowrap ring-1 {{ $tones[$log->action->tone()] }}">{{ $log->action->label($log->sector) }}</span>
                </p>
                <p class="mt-1 text-slate-800">
                    {{ display_name($log->commodity_name) ?: '–' }}<span class="text-slate-600"> · {{ $log->sector?->label() }}</span>@if ($log->detail)<span class="text-slate-600"> · </span><span class="num font-semibold">{{ $log->detail }}</span>@endif
                </p>
                @if ($log->farmerGroup)
                    <p class="text-xs text-slate-600">{{ $log->farmerGroup->name }}, Kel. {{ display_name($log->farmerGroup->village?->name) }}</p>
                @endif
            </div>
            <div class="shrink-0 text-right text-xs text-slate-600">
                <time datetime="{{ $log->created_at->toIso8601String() }}" title="{{ format_date($log->created_at, 'j M Y, H.i') }}">
                    {{ $log->created_at->diffInDays($now) < 7 ? $log->created_at->locale('id')->diffForHumans() : format_date($log->created_at, 'j M Y') }}
                </time>
                @if ($link)
                    <a href="{{ $link }}" class="mt-1 block font-semibold text-brand-700 hover:underline">Lihat<span class="sr-only"> data {{ display_name($log->commodity_name) }}</span></a>
                @endif
            </div>
        </li>
    @endforeach
</ul>
