<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="page-title">{{ __('events.heading') }}</h1>
                <p class="muted mt-1">{{ __('events.subheading') }}</p>
            </div>
            <a href="{{ route('events.create') }}" class="btn btn-primary">{{ __('events.create') }}</a>
        </div>
    </x-slot>

    <form method="GET" class="mb-6 flex flex-col gap-3 sm:flex-row">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('events.search_placeholder') }}" class="input sm:max-w-xs">
        <select name="status" class="input sm:max-w-[12rem]" onchange="this.form.submit()">
            <option value="">{{ __('common.all') }}</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-secondary">{{ __('common.filter') }}</button>
    </form>

    @if ($events->isEmpty())
        <div class="empty-state">
            <p class="font-semibold text-zinc-700">{{ __('events.empty') }}</p>
            <p class="muted">{{ __('events.empty_cta') }}</p>
            <a href="{{ route('events.create') }}" class="btn btn-primary mt-2">{{ __('events.create') }}</a>
        </div>
    @else
        <div class="card overflow-hidden">
            <table class="table-base">
                <thead>
                    <tr>
                        <th>{{ __('events.fields.title') }}</th>
                        <th>{{ __('common.date') }}</th>
                        <th class="hidden sm:table-cell">{{ __('events.fields.venue_name') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th class="hidden md:table-cell">{{ __('guests.heading') }}</th>
                        <th><span class="sr-only">{{ __('common.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($events as $event)
                        <tr>
                            <td>
                                <a href="{{ route('events.show', $event) }}" class="font-semibold text-zinc-900 hover:text-brand-700">{{ $event->title }}</a>
                                <p class="text-xs text-zinc-400">{{ $event->organizer?->name }}</p>
                            </td>
                            <td class="whitespace-nowrap text-zinc-500">{{ $event->starts_at?->copy()->timezone($event->timezone)->isoFormat('D MMM YYYY · HH:mm') ?? '—' }}</td>
                            <td class="hidden text-zinc-500 sm:table-cell">{{ $event->venue_name ?? '—' }}</td>
                            <td><span @class(['badge', $event->statusEnum()->badgeClasses()])>{{ $event->statusEnum()->label() }}</span></td>
                            <td class="hidden text-zinc-500 md:table-cell">{{ $event->guests()->count() }}</td>
                            <td class="text-right">
                                <a href="{{ route('events.show', $event) }}" class="btn btn-secondary btn-sm">{{ __('common.view') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $events->links() }}</div>
    @endif
</x-app-layout>
