<x-app-layout>
    <x-slot name="header">
        @include('events._tabs', ['event' => $event])
    </x-slot>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-1 flex-col gap-2 sm:flex-row">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('guests.search_placeholder') }}" class="input sm:max-w-xs">
            <select name="category" class="input sm:max-w-[12rem]" onchange="this.form.submit()">
                <option value="">{{ __('guests.all_categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">{{ __('common.filter') }}</button>
        </form>

        @can('manageGuests', $event)
            <div class="flex gap-2">
                <a href="{{ route('events.guests.import', $event) }}" class="btn btn-secondary">{{ __('guests.import_csv') }}</a>
                <a href="{{ route('events.guests.create', $event) }}" class="btn btn-primary">{{ __('guests.add') }}</a>
            </div>
        @endcan
    </div>

    @if (session('importResult'))
        <div class="mb-4 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
            <p class="font-semibold">{{ __('guests.import.summary', [
                'imported' => session('importResult.imported'),
                'duplicates' => session('importResult.duplicates'),
                'invalid' => count(session('importResult.invalid')),
            ]) }}</p>
            @if (count(session('importResult.invalid')) > 0)
                <ul class="mt-1 list-inside list-disc">
                    @foreach (session('importResult.invalid') as $row)
                        <li>Row {{ $row['row'] }}: {{ $row['reason'] }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <div class="mb-3 flex justify-end">
        <a href="{{ route('events.guests.export', $event) }}" class="btn btn-secondary btn-sm">{{ __('guests.export') }}</a>
    </div>

    @if ($guests->isEmpty())
        <div class="empty-state">
            <p class="font-semibold text-zinc-700">{{ __('guests.empty') }}</p>
            <p class="muted">{{ __('guests.empty_cta') }}</p>
            @can('manageGuests', $event)
                <div class="mt-2 flex gap-2">
                    <a href="{{ route('events.guests.create', $event) }}" class="btn btn-primary">{{ __('guests.add') }}</a>
                    <a href="{{ route('events.guests.import', $event) }}" class="btn btn-secondary">{{ __('guests.import_csv') }}</a>
                </div>
            @endcan
        </div>
    @else
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>{{ __('guests.columns.name') }}</th>
                            <th class="hidden sm:table-cell">{{ __('guests.columns.phone') }}</th>
                            <th class="hidden md:table-cell">{{ __('guests.columns.category') }}</th>
                            <th>{{ __('guests.columns.rsvp') }}</th>
                            <th>{{ __('guests.columns.invitation') }}</th>
                            <th><span class="sr-only">{{ __('common.actions') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($guests as $guest)
                            <tr>
                                <td>
                                    <p class="font-semibold text-zinc-900">{{ $guest->name }}</p>
                                    <p class="text-xs text-zinc-400 sm:hidden">{{ $guest->phone ?: ($guest->email ?: __('guests.no_phone_email')) }}</p>
                                    @if ($guest->notes)
                                        <p class="text-xs text-zinc-400">{{ \Illuminate\Support\Str::limit($guest->notes, 80) }}</p>
                                    @endif
                                </td>
                                <td class="hidden text-zinc-500 sm:table-cell">{{ $guest->phone ?: '—' }}</td>
                                <td class="hidden text-zinc-500 md:table-cell">{{ $guest->category ? \Illuminate\Support\Str::of($guest->category)->replace('_',' ')->title() : '—' }}</td>
                                <td>
                                    @if ($guest->latestInvitation)
                                        <span @class(['badge', \App\Enums\RsvpStatus::from($guest->latestInvitation->rsvp_status)->badgeClasses()])>
                                            {{ \App\Enums\RsvpStatus::from($guest->latestInvitation->rsvp_status)->label() }}
                                        </span>
                                    @else
                                        <span class="text-xs text-zinc-400">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($guest->latestInvitation)
                                        <span class="text-xs font-medium {{ $guest->latestInvitation->status === 'issued' ? 'text-emerald-700' : 'text-zinc-400' }}">
                                            {{ __('invitations.status.'.$guest->latestInvitation->status) }}
                                        </span>
                                    @elseif (true)
                                        @can('manageInvitations', $event)
                                            <a href="{{ route('events.invitations.create', ['event' => $event, 'guest_id' => $guest->id]) }}" class="text-xs font-semibold text-brand-700 hover:underline">{{ __('guests.invite') }}</a>
                                        @endcan
                                    @endif
                                </td>
                                <td class="text-right">
                                    @can('manageGuests', $event)
                                        <a href="{{ route('events.guests.edit', [$event, $guest]) }}" class="btn btn-secondary btn-sm">{{ __('common.edit') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">{{ $guests->links() }}</div>
    @endif
</x-app-layout>
