<x-app-layout>
    <x-slot name="header">
        @include('events._tabs', ['event' => $event])
    </x-slot>

    <nav class="mb-4 flex gap-1 text-sm">
        <a href="{{ route('events.finance.pledges', $event) }}" class="rounded-lg bg-brand-50 px-3 py-1.5 font-semibold text-brand-800 ring-1 ring-brand-200">{{ __('finance.tabs.pledges') }}</a>
        <a href="{{ route('events.finance.contributions', $event) }}" class="rounded-lg px-3 py-1.5 font-semibold text-zinc-500 hover:bg-zinc-100">{{ __('finance.tabs.contributions') }}</a>
        <a href="{{ route('events.finance.expenses', $event) }}" class="rounded-lg px-3 py-1.5 font-semibold text-zinc-500 hover:bg-zinc-100">{{ __('finance.tabs.expenses') }}</a>
        <a href="{{ route('events.finance.budget', $event) }}" class="rounded-lg px-3 py-1.5 font-semibold text-zinc-500 hover:bg-zinc-100">{{ __('finance.tabs.budget') }}</a>
    </nav>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
        <x-stat :label="__('finance.pledges.total_pledged')" :value="\App\Support\Money::format($totals['pledged'])" />
        <x-stat :label="__('finance.pledges.total_received')" :value="\App\Support\Money::format($totals['received'])" tone="positive" />
        <x-stat :label="__('finance.pledges.outstanding')" :value="\App\Support\Money::format(max(0, $totals['pledged'] - $totals['received']))" tone="caution" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        @can('recordContributions', $event)
            <div class="card p-6 lg:col-span-1">
                <h2 class="section-title">{{ __('finance.pledges.add') }}</h2>
                <form method="POST" action="{{ route('events.finance.pledges.store', $event) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="label" for="contributor_name">{{ __('finance.pledges.contributor') }} *</label>
                        <input class="input" id="contributor_name" name="contributor_name" value="{{ old('contributor_name') }}" required maxlength="160">
                    </div>
                    <div>
                        <label class="label" for="contributor_contact">{{ __('finance.pledges.contact') }}</label>
                        <input class="input" id="contributor_contact" name="contributor_contact" value="{{ old('contributor_contact') }}" maxlength="64">
                    </div>
                    <div>
                        <label class="label" for="amount">{{ __('common.amount') }} *</label>
                        <input class="input" id="amount" name="amount" value="{{ old('amount') }}" required inputmode="decimal" placeholder="500000">
                        <p class="mt-1 text-xs text-zinc-400">{{ __('common.money_hint') }}</p>
                    </div>
                    <div>
                        <label class="label" for="due_date">{{ __('finance.pledges.due_date') }}</label>
                        <input class="input" type="date" id="due_date" name="due_date" value="{{ old('due_date') }}">
                    </div>
                    <div>
                        <label class="label" for="notes">{{ __('common.notes') }}</label>
                        <input class="input" id="notes" name="notes" value="{{ old('notes') }}" maxlength="500">
                    </div>
                    <button type="submit" class="btn btn-primary w-full">{{ __('common.save') }}</button>
                </form>
            </div>
        @endcan

        <div class="lg:col-span-2">
            @if ($pledges->isEmpty())
                <div class="empty-state">
                    <p class="font-semibold text-zinc-700">{{ __('finance.pledges.empty') }}</p>
                </div>
            @else
                <div class="card overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="table-base">
                            <thead>
                                <tr>
                                    <th>{{ __('finance.pledges.columns.contributor') }}</th>
                                    <th>{{ __('finance.pledges.columns.amount') }}</th>
                                    <th>{{ __('finance.pledges.columns.received') }}</th>
                                    <th class="hidden sm:table-cell">{{ __('finance.pledges.columns.outstanding') }}</th>
                                    <th>{{ __('finance.pledges.columns.status') }}</th>
                                    <th><span class="sr-only">{{ __('common.actions') }}</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pledges as $pledge)
                                    @php $received = $pledge->receivedMinor(); @endphp
                                    <tr>
                                        <td>
                                            <p class="font-semibold text-zinc-900">{{ $pledge->contributor_name }}</p>
                                            <p class="text-xs text-zinc-400">{{ $pledge->contributor_contact }}</p>
                                        </td>
                                        <td>{{ \App\Support\Money::format($pledge->amount_minor) }}</td>
                                        <td class="text-emerald-700">{{ \App\Support\Money::format($received) }}</td>
                                        <td class="hidden sm:table-cell text-zinc-600">{{ \App\Support\Money::format($pledge->outstandingMinor()) }}</td>
                                        <td>
                                            <span class="badge {{ match ($pledge->status) {
                                                'settled' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                                'partially_paid' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                                'cancelled' => 'bg-zinc-100 text-zinc-500 ring-zinc-200',
                                                default => 'bg-sky-50 text-sky-700 ring-sky-200',
                                            } }}">{{ __('finance.pledges.status.'.$pledge->status) }}</span>
                                        </td>
                                        <td class="text-right">
                                            @if (in_array($pledge->status, ['open', 'partially_paid'], true) && $received === 0)
                                                @can('recordContributions', $event)
                                                    <form method="POST" action="{{ route('events.finance.pledges.cancel', [$event, $pledge]) }}"
                                                          onsubmit="return confirm({{ json_encode(__('common.confirm_delete')) }});">
                                                        @csrf
                                                        <button type="submit" class="btn btn-secondary btn-sm">{{ __('finance.pledges.cancel_action') }}</button>
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
                <div class="mt-4">{{ $pledges->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
