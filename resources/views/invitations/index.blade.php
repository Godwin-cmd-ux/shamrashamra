<x-app-layout>
    <x-slot name="header">
        @include('events._tabs', ['event' => $event])
    </x-slot>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <x-stat :label="__('invitations.counts.issued')" :value="$counts['issued']" />
        <x-stat :label="__('invitations.counts.confirmed')" :value="$counts['confirmed']" tone="positive" />
        <x-stat :label="__('invitations.counts.checked_in')" :value="$counts['checked_in']" />
        <x-stat :label="__('invitations.counts.revoked')" :value="$counts['revoked']" tone="caution" />
    </div>

    <div class="mb-4 mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-1 flex-col gap-2 sm:flex-row">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('invitations.search_placeholder') }}" class="input sm:max-w-xs">
            <select name="rsvp" class="input sm:max-w-[12rem]" onchange="this.form.submit()">
                <option value="">{{ __('common.all') }}</option>
                @foreach (['pending', 'confirmed', 'declined'] as $rsvp)
                    <option value="{{ $rsvp }}" @selected(request('rsvp') === $rsvp)>{{ __('invitations.rsvp.'.$rsvp) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">{{ __('common.filter') }}</button>
        </form>

        <a href="{{ route('events.invitations.create', $event) }}" class="btn btn-primary">{{ __('invitations.issue') }}</a>
    </div>

    @if ($invitations->isEmpty())
        <div class="empty-state">
            <p class="font-semibold text-zinc-700">{{ __('invitations.empty') }}</p>
            <p class="muted">{{ __('invitations.empty_cta') }}</p>
            <a href="{{ route('events.invitations.create', $event) }}" class="btn btn-primary mt-2">{{ __('invitations.issue') }}</a>
        </div>
    @else
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>{{ __('invitations.fields.label') }}</th>
                            <th>{{ __('invitations.fields.entitlement_count') }}</th>
                            <th>{{ __('guests.columns.rsvp') }}</th>
                            <th>{{ __('common.status') }}</th>
                            <th class="hidden sm:table-cell">{{ __('invitations.fields.used') }}</th>
                            <th><span class="sr-only">{{ __('common.actions') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invitations as $invitation)
                            <tr>
                                <td>
                                    <a href="{{ route('events.invitations.show', [$event, $invitation]) }}" class="font-semibold text-zinc-900 hover:text-brand-700">
                                        {{ $invitation->displayLabel() }}
                                    </a>
                                    @if ($invitation->guest?->phone)
                                        <p class="text-xs text-zinc-400">{{ $invitation->guest->phone }}</p>
                                    @endif
                                </td>
                                <td>{{ $invitation->entitlement_count }}</td>
                                <td>
                                    <span @class(['badge', \App\Enums\RsvpStatus::from($invitation->rsvp_status)->badgeClasses()])>
                                        {{ \App\Enums\RsvpStatus::from($invitation->rsvp_status)->label() }}
                                    </span>
                                </td>
                                <td>
                                    <span @class(['badge',
                                        'bg-emerald-50 text-emerald-700 ring-emerald-200' => $invitation->status === 'issued',
                                        'bg-rose-50 text-rose-700 ring-rose-200' => $invitation->status === 'revoked',
                                        'bg-zinc-100 text-zinc-500 ring-zinc-200' => $invitation->status === 'replaced',
                                    ])>{{ __('invitations.status.'.$invitation->status) }}</span>
                                </td>
                                <td class="hidden sm:table-cell">
                                    @if ($invitation->attendanceRecord)
                                        <span class="text-xs font-medium text-emerald-700">{{ $invitation->attendanceRecord->checked_in_at->format('d M HH:mm') }}</span>
                                    @else
                                        <span class="text-xs text-zinc-400">{{ __('invitations.fields.not_used') }}</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('events.invitations.show', [$event, $invitation]) }}" class="btn btn-secondary btn-sm">{{ __('common.view') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">{{ $invitations->links() }}</div>
    @endif

    {{-- Notification / delivery outbox --}}
    <div class="mt-8 card p-6">
        <h2 class="section-title">{{ __('invitations.outbox.heading') }}</h2>
        <p class="muted mt-1 text-xs">{{ __('invitations.outbox.not_configured') }}</p>

        @if ($outbox->isEmpty())
            <p class="muted mt-3">{{ __('invitations.outbox.empty') }}</p>
        @else
            <div class="mt-3 overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>{{ __('invitations.outbox.when') }}</th>
                            <th>{{ __('invitations.outbox.channel') }}</th>
                            <th>{{ __('invitations.outbox.recipient') }}</th>
                            <th>{{ __('invitations.outbox.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($outbox as $notification)
                            <tr>
                                <td class="text-zinc-500">{{ $notification->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $notification->channel }}</td>
                                <td>{{ \Illuminate\Support\Str::mask($notification->recipient, '*', 0, 3) }}</td>
                                <td><span class="badge bg-zinc-100 text-zinc-600 ring-zinc-200">{{ $notification->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
