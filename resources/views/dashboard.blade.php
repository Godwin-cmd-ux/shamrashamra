<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="page-title">{{ __('dashboard.greeting', ['name' => auth()->user()->name]) }}</h1>
                <p class="muted mt-1">{{ __('dashboard.subheading') }}</p>
            </div>
            <a href="{{ route('events.create') }}" class="btn btn-primary">{{ __('dashboard.create_event') }}</a>
        </div>
    </x-slot>

    {{-- Metrics --}}
    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
        <x-stat :label="__('dashboard.metrics.active_events')" :value="$metrics['active_events']" />
        <x-stat :label="__('dashboard.metrics.upcoming_events')" :value="$metrics['upcoming_events']" />
        <x-stat :label="__('dashboard.metrics.invited_guests')" :value="$metrics['invited_guests']" />
        <x-stat :label="__('dashboard.metrics.rsvp_confirmed')" :value="$metrics['rsvp_confirmed']" tone="positive" />
        <x-stat :label="__('dashboard.metrics.attendance_count')" :value="$metrics['attendance_count']" />
        <x-stat :label="__('dashboard.metrics.rsvp_declined')" :value="$metrics['rsvp_declined']" tone="caution" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        {{-- Money (kept strictly separate) --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:col-span-2">
            <x-stat :label="__('dashboard.metrics.total_pledged')" :value="\App\Support\Money::format($metrics['total_pledged_minor'])" :hint="__('dashboard.note_separation')" />
            <x-stat :label="__('dashboard.metrics.total_contributed')" :value="\App\Support\Money::format($metrics['total_contributed_minor'])" tone="positive" :hint="__('dashboard.note_separation')" />
            <x-stat :label="__('dashboard.metrics.outstanding')" :value="\App\Support\Money::format($metrics['outstanding_minor'])" tone="caution" />
            <x-stat :label="__('dashboard.metrics.remaining_budget')" :value="\App\Support\Money::format($metrics['remaining_budget_minor'])" :hint="\App\Support\Money::format($metrics['planned_budget_minor']).' · '.__('dashboard.metrics.expenses').' '.\App\Support\Money::format($metrics['expenses_minor'])" />
        </div>

        {{-- Platform admin stats --}}
        @if ($adminStats)
            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <p class="section-title">{{ __('dashboard.platform_admin') }}</p>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">{{ __('dashboard.open_admin') }}</a>
                </div>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-zinc-500">{{ __('dashboard.admin_stats.users') }}</dt><dd class="font-semibold">{{ $adminStats['users'] }}</dd></div>
                    <div class="flex justify-between"><dt class="text-zinc-500">{{ __('dashboard.admin_stats.active_events') }}</dt><dd class="font-semibold">{{ $adminStats['published_events'] }}</dd></div>
                    <div class="flex justify-between"><dt class="text-zinc-500">{{ __('dashboard.admin_stats.events') }}</dt><dd class="font-semibold">{{ $adminStats['events'] }}</dd></div>
                    <div class="flex justify-between"><dt class="text-zinc-500">{{ __('dashboard.admin_stats.categories') }}</dt><dd class="font-semibold">{{ $adminStats['categories'] }}</dd></div>
                </dl>
            </div>
        @endif
    </div>

    {{-- Events --}}
    <div class="mt-8">
        <h2 class="section-title">{{ __('dashboard.your_events') }}</h2>

        @if ($events->isEmpty())
            <div class="empty-state mt-4">
                <p class="font-semibold text-zinc-700">{{ __('dashboard.no_events') }}</p>
                <p class="muted">{{ __('dashboard.no_events_cta') }}</p>
                <a href="{{ route('events.create') }}" class="btn btn-primary mt-2">{{ __('dashboard.create_event') }}</a>
            </div>
        @else
            <div class="card mt-4 overflow-hidden">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>{{ __('events.fields.title') }}</th>
                            <th>{{ __('common.date') }}</th>
                            <th>{{ __('common.status') }}</th>
                            <th class="hidden sm:table-cell">{{ __('checkin.stats.checked_in') }}</th>
                            <th><span class="sr-only">{{ __('common.actions') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($events as $event)
                            <tr>
                                <td>
                                    <a href="{{ route('events.show', $event) }}" class="font-semibold text-zinc-900 hover:text-brand-700">{{ $event->title }}</a>
                                    @if ($event->category)
                                        <span class="ms-2 text-xs text-zinc-400">{{ $event->category->name() }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-zinc-500">
                                    {{ $event->starts_at?->copy()->timezone($event->timezone)->isoFormat('D MMM YYYY') ?? '—' }}
                                </td>
                                <td><span @class(['badge', $event->statusEnum()->badgeClasses()])>{{ $event->statusEnum()->label() }}</span></td>
                                <td class="hidden text-zinc-500 sm:table-cell">{{ $event->attendanceRecords()->count() }} / {{ $event->invitations()->where('status', 'issued')->count() }}</td>
                                <td class="text-right">
                                    <a href="{{ route('events.show', $event) }}" class="btn btn-secondary btn-sm">{{ __('dashboard.view_event') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $events->links() }}</div>
        @endif
    </div>
</x-app-layout>
