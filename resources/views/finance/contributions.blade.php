<x-app-layout>
    <x-slot name="header">
        @include('events._tabs', ['event' => $event])
    </x-slot>

    <nav class="mb-4 flex gap-1 text-sm">
        <a href="{{ route('events.finance.pledges', $event) }}" class="rounded-lg px-3 py-1.5 font-semibold text-zinc-500 hover:bg-zinc-100">{{ __('finance.tabs.pledges') }}</a>
        <a href="{{ route('events.finance.contributions', $event) }}" class="rounded-lg bg-brand-50 px-3 py-1.5 font-semibold text-brand-800 ring-1 ring-brand-200">{{ __('finance.tabs.contributions') }}</a>
        <a href="{{ route('events.finance.expenses', $event) }}" class="rounded-lg px-3 py-1.5 font-semibold text-zinc-500 hover:bg-zinc-100">{{ __('finance.tabs.expenses') }}</a>
        <a href="{{ route('events.finance.budget', $event) }}" class="rounded-lg px-3 py-1.5 font-semibold text-zinc-500 hover:bg-zinc-100">{{ __('finance.tabs.budget') }}</a>
    </nav>

    <x-stat :label="__('finance.contributions.total')" :value="\App\Support\Money::format($totalMinor)" tone="positive" :hint="__('dashboard.note_separation')" />

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        @can('recordContributions', $event)
            <div class="card p-6 lg:col-span-1">
                <h2 class="section-title">{{ __('finance.contributions.add') }}</h2>
                <form method="POST" action="{{ route('events.finance.contributions.store', $event) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="label" for="amount">{{ __('finance.contributions.amount') }} *</label>
                        <input class="input" id="amount" name="amount" value="{{ old('amount') }}" required inputmode="decimal" placeholder="250000">
                        <p class="mt-1 text-xs text-zinc-400">{{ __('common.money_hint') }}</p>
                    </div>
                    <div>
                        <label class="label" for="received_at">{{ __('finance.contributions.received_at') }} *</label>
                        <input class="input" type="date" id="received_at" name="received_at" value="{{ old('received_at', date('Y-m-d')) }}" required>
                    </div>
                    <div>
                        <label class="label" for="method">{{ __('finance.contributions.method') }} *</label>
                        <select class="input" id="method" name="method" required>
                            @foreach (['cash', 'momo', 'bank', 'other'] as $method)
                                <option value="{{ $method }}" @selected(old('method', 'cash') === $method)>{{ __('finance.contributions.methods.'.$method) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="reference">{{ __('finance.contributions.reference') }}</label>
                        <input class="input" id="reference" name="reference" value="{{ old('reference') }}" maxlength="120">
                    </div>
                    <div>
                        <label class="label" for="pledge_id">{{ __('finance.contributions.link_pledge') }}</label>
                        <select class="input" id="pledge_id" name="pledge_id">
                            <option value="">{{ __('common.none') }}</option>
                            @foreach ($pledges as $pledge)
                                <option value="{{ $pledge->id }}" @selected((string) old('pledge_id') === (string) $pledge->id)>
                                    {{ $pledge->contributor_name }} · {{ \App\Support\Money::format($pledge->outstandingMinor()) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="note">{{ __('common.notes') }}</label>
                        <input class="input" id="note" name="note" value="{{ old('note') }}" maxlength="500">
                    </div>
                    <button type="submit" class="btn btn-primary w-full">{{ __('common.save') }}</button>
                </form>
            </div>
        @endcan

        <div class="lg:col-span-2">
            @if ($contributions->isEmpty())
                <div class="empty-state">
                    <p class="font-semibold text-zinc-700">{{ __('finance.contributions.empty') }}</p>
                </div>
            @else
                <div class="card overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="table-base">
                            <thead>
                                <tr>
                                    <th>{{ __('finance.contributions.columns.date') }}</th>
                                    <th>{{ __('finance.contributions.columns.amount') }}</th>
                                    <th class="hidden sm:table-cell">{{ __('finance.contributions.columns.method') }}</th>
                                    <th class="hidden md:table-cell">{{ __('finance.contributions.columns.pledge') }}</th>
                                    <th><span class="sr-only">{{ __('common.actions') }}</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($contributions as $contribution)
                                    <tr class="{{ $contribution->isReversal() ? 'bg-rose-50/40' : '' }}">
                                        <td class="whitespace-nowrap text-zinc-500">{{ $contribution->received_at->format('d M Y') }}</td>
                                        <td class="font-semibold {{ $contribution->amount_minor < 0 ? 'text-rose-700' : 'text-zinc-900' }}">
                                            {{ \App\Support\Money::format($contribution->amount_minor) }}
                                        </td>
                                        <td class="hidden sm:table-cell">{{ __('finance.contributions.methods.'.$contribution->method) }}</td>
                                        <td class="hidden md:table-cell text-zinc-500">{{ $contribution->pledge?->contributor_name ?? '—' }}</td>
                                        <td class="text-right">
                                            @if ($contribution->isReversed())
                                                <span class="badge bg-rose-50 text-rose-700 ring-rose-200">{{ __('finance.contributions.reversed_badge') }}</span>
                                            @elseif (! $contribution->isReversal())
                                                @can('recordContributions', $event)
                                                    <form method="POST" action="{{ route('events.finance.contributions.reverse', [$event, $contribution]) }}"
                                                          class="flex items-center justify-end gap-1"
                                                          onsubmit="event.preventDefault(); const reason = prompt({{ json_encode(__('finance.reverse_reason')) }}); if (reason) { this.reason.value = reason; this.submit(); };">
                                                        @csrf
                                                        <input type="hidden" name="reason" value="">
                                                        <button type="submit" class="btn btn-secondary btn-sm">{{ __('finance.contributions.reverse_action') }}</button>
                                                    </form>
                                                @endcan
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <p class="muted mt-2 text-xs">{{ __('finance.reverse_help') }}</p>
                <div class="mt-4">{{ $contributions->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
