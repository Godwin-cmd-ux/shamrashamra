@php
    $tabs = [
        'events.show' => ['label' => __('events.overview.quick_stats'), 'url' => route('events.show', $event), 'can' => 'view'],
        'events.guests.*' => ['label' => __('guests.heading'), 'url' => route('events.guests.index', $event), 'can' => 'viewGuests'],
        'events.invitations.*' => ['label' => __('invitations.heading'), 'url' => route('events.invitations.index', $event), 'can' => 'manageInvitations'],
        'events.checkin.*' => ['label' => __('checkin.heading'), 'url' => route('events.checkin.index', $event), 'can' => 'checkIn'],
        'events.finance.*' => ['label' => __('finance.tabs.finances'), 'url' => route('events.finance.pledges', $event), 'can' => 'viewFinance'],
        'events.vendors.*' => ['label' => __('vendors.heading'), 'url' => route('events.vendors.index', $event), 'can' => 'manageVendors'],
        'events.members.*' => ['label' => __('events.members.heading'), 'url' => route('events.members.index', $event), 'can' => 'manageMembers'],
        'events.reports.*' => ['label' => __('reports.heading'), 'url' => route('events.reports.index', $event), 'can' => 'viewReports'],
        'events.audit' => ['label' => __('events.overview.recent_activity'), 'url' => route('events.audit', $event), 'can' => 'viewAudit'],
    ];
@endphp

<div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="font-display text-2xl font-semibold text-zinc-900">{{ $event->title }}</h1>
            <span @class(['badge', $event->statusEnum()->badgeClasses()])>{{ $event->statusEnum()->label() }}</span>
        </div>
        <p class="mt-1 text-sm text-zinc-500">
            @if ($event->starts_at)
                {{ $event->starts_at->copy()->timezone($event->timezone)->isoFormat('dddd, D MMMM YYYY · HH:mm') }}
            @endif
            @if ($event->venue_name)
                <span class="text-zinc-300">·</span> {{ $event->venue_name }}
            @endif
        </p>
    </div>

    <div class="flex gap-2">
        @can('manageInvitations', $event)
            <a href="{{ route('events.invitations.create', $event) }}" class="btn btn-primary btn-sm">{{ __('invitations.issue') }}</a>
        @endcan
        @can('checkIn', $event)
            <a href="{{ route('events.checkin.index', $event) }}" class="btn btn-secondary btn-sm">{{ __('checkin.heading') }}</a>
        @endcan
        @can('update', $event)
            <a href="{{ route('events.edit', $event) }}" class="btn btn-secondary btn-sm">{{ __('events.edit') }}</a>
        @endcan
    </div>
</div>

<nav class="mt-4 -mb-5 flex gap-1 overflow-x-auto pb-1" aria-label="{{ __('common.tabs') }}">
    @foreach ($tabs as $pattern => $tab)
        @can($tab['can'], $event)
            <a href="{{ $tab['url'] }}"
               @class(['whitespace-nowrap rounded-lg px-3 py-2 text-sm font-semibold transition',
                   request()->routeIs($pattern) ? 'bg-brand-50 text-brand-800 ring-1 ring-brand-200' : 'text-zinc-500 hover:bg-zinc-50 hover:text-zinc-800'])>
                {{ $tab['label'] }}
            </a>
        @endcan
    @endforeach
</nav>
