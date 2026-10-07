<x-app-layout>
    <x-slot name="header">
        @include('events._tabs', ['event' => $event])
    </x-slot>

    @php
        $m = $metrics;
        $transitions = $event->statusEnum()->transitions()[$event->status] ?? [];
    @endphp

    {{-- Quick stats --}}
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <x-stat :label="__('guests.heading')" :value="$m['guests']" />
        <x-stat :label="__('invitations.counts.issued')" :value="$m['invitations_issued']" :hint="__('invitations.counts.confirmed').' '.$m['rsvp_confirmed']" />
        <x-stat :label="__('checkin.stats.checked_in')" :value="$m['checkin_records'].' / '.$m['invitations_issued']" />
        <x-stat :label="__('checkin.stats.guests')" :value="$m['attendance_guests']" tone="positive" />
    </div>

    <div class="mt-4 card p-5">
        <div class="flex items-center justify-between text-sm">
            <span class="font-semibold text-zinc-700">{{ __('dashboard.checkin_progress') }}</span>
            <span class="font-semibold text-brand-700">{{ $m['checkin_progress'] }}%</span>
        </div>
        <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-zinc-100">
            <div class="h-full rounded-full bg-brand-600 transition-all" style="width: {{ $m['checkin_progress'] }}%"></div>
        </div>
        <div class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-xs text-zinc-500">
            <span>{{ __('invitations.rsvp.confirmed') }}: {{ $m['rsvp_confirmed'] }}</span>
            <span>{{ __('invitations.rsvp.pending') }}: {{ $m['rsvp_pending'] }}</span>
            <span>{{ __('invitations.rsvp.declined') }}: {{ $m['rsvp_declined'] }}</span>
        </div>
    </div>

    {{-- Finance summary --}}
    @can('viewFinance', $event)
        <div class="mt-6">
            <h2 class="section-title">{{ __('events.overview.finance_summary') }}</h2>
            <div class="mt-3 grid grid-cols-2 gap-4 lg:grid-cols-4">
                <x-stat :label="__('dashboard.metrics.total_pledged')" :value="\App\Support\Money::format($m['pledged_minor'])" />
                <x-stat :label="__('dashboard.metrics.total_contributed')" :value="\App\Support\Money::format($m['contributed_minor'])" tone="positive" />
                <x-stat :label="__('dashboard.metrics.planned_budget')" :value="\App\Support\Money::format($m['planned_budget_minor'])" />
                <x-stat :label="__('dashboard.metrics.expenses')" :value="\App\Support\Money::format($m['expenses_minor'])" tone="caution" />
            </div>
            <p class="mt-2 text-xs text-zinc-400">{{ __('dashboard.note_separation') }}</p>
        </div>
    @endcan

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Details --}}
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <h2 class="section-title">{{ __('events.overview.details') }}</h2>
                @can('update', $event)
                    <a href="{{ route('events.edit', $event) }}" class="btn btn-secondary btn-sm">{{ __('common.edit') }}</a>
                @endcan
            </div>

            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-zinc-500">{{ __('events.fields.category') }}</dt><dd class="text-right font-medium">{{ $event->category?->name() ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-zinc-500">{{ __('events.fields.starts_at') }}</dt><dd class="text-right font-medium">{{ $event->starts_at?->copy()->timezone($event->timezone)->isoFormat('dddd, D MMMM YYYY · HH:mm') ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-zinc-500">{{ __('events.fields.timezone') }}</dt><dd class="text-right font-medium">{{ $event->timezone }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-zinc-500">{{ __('events.fields.venue_name') }}</dt><dd class="text-right font-medium">{{ $event->venue_name ?? '—' }}</dd></div>                    <div class="flex justify-between gap-4"><dt class="text-zinc-500">{{ __('events.fields.venue_address') }}</dt><dd class="text-right font-medium">{{ $event->venue_address ?? '—' }}</dd></div>
                    @if ($event->map_link)
                        <div class="flex justify-between gap-4"><dt class="text-zinc-500">{{ __('invitations.guest_view.directions') }}</dt><dd class="text-right"><a class="font-medium text-brand-700 hover:underline" href="{{ $event->map_link }}" target="_blank" rel="noopener">{{ __('invitations.guest_view.directions') }}</a></dd></div>
                    @endif
                <div class="flex justify-between gap-4"><dt class="text-zinc-500">{{ __('events.fields.host_names') }}</dt><dd class="text-right font-medium">{{ $event->host_names ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-zinc-500">{{ __('events.fields.dress_code') }}</dt><dd class="text-right font-medium">{{ $event->dress_code ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-zinc-500">{{ __('events.fields.rsvp_deadline') }}</dt><dd class="text-right font-medium">{{ $event->rsvp_deadline?->format('d M Y H:i') ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-zinc-500">{{ __('events.fields.guest_capacity') }}</dt><dd class="text-right font-medium">{{ $event->guest_capacity ?? '—' }}</dd></div>
            </dl>

            @if ($event->description)
                <div class="prose-event mt-4 border-t border-zinc-100 pt-4 text-sm">{{ nl2br(e($event->description)) }}</div>
            @endif
        </div>

        <div class="space-y-6">
            {{-- Lifecycle --}}
            @can('update', $event)
                <div class="card p-6">
                    <h2 class="section-title">{{ __('events.lifecycle.label') }}</h2>
                    @if (count($transitions) > 0)
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($transitions as $target)
                                <form method="POST" action="{{ route('events.status', $event) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $target }}">
                                    <button type="submit" class="btn btn-secondary btn-sm"
                                            @if (in_array($target, ['cancelled'], true)) onclick="return confirm({{ json_encode(__('common.confirm_delete')) }});" @endif>
                                        {{ __('events.status.'.$target) }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                        <p class="muted mt-3 text-xs">{{ __('events.lifecycle.allowed') }}</p>
                    @else
                        <p class="muted mt-2">{{ __('events.lifecycle.none') }}</p>
                    @endif
                </div>
            @endcan

            {{-- Activity --}}
            @can('viewAudit', $event)
                <div class="card p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="section-title">{{ __('events.overview.recent_activity') }}</h2>
                        <a href="{{ route('events.audit', $event) }}" class="text-sm font-semibold text-brand-700 hover:underline">{{ __('common.view') }}</a>
                    </div>
                    @if ($recentAudit->isEmpty())
                        <p class="muted mt-3">{{ __('events.overview.no_activity') }}</p>
                    @else
                        <ul class="mt-3 space-y-2 text-sm">
                            @foreach ($recentAudit as $log)
                                <li class="flex items-start justify-between gap-3">
                                    <span class="text-zinc-700">{{ $log->action }}</span>
                                    <span class="shrink-0 text-xs text-zinc-400">{{ $log->created_at->diffForHumans() }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan
        </div>
    </div>
</x-app-layout>
