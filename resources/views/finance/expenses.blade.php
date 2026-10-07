<x-app-layout>
    <x-slot name="header">
        @include('events._tabs', ['event' => $event])
    </x-slot>

    <nav class="mb-4 flex gap-1 text-sm">
        <a href="{{ route('events.finance.pledges', $event) }}" class="rounded-lg px-3 py-1.5 font-semibold text-zinc-500 hover:bg-zinc-100">{{ __('finance.tabs.pledges') }}</a>
        <a href="{{ route('events.finance.contributions', $event) }}" class="rounded-lg px-3 py-1.5 font-semibold text-zinc-500 hover:bg-zinc-100">{{ __('finance.tabs.contributions') }}</a>
        <a href="{{ route('events.finance.expenses', $event) }}" class="rounded-lg bg-brand-50 px-3 py-1.5 font-semibold text-brand-800 ring-1 ring-brand-200">{{ __('finance.tabs.expenses') }}</a>
        <a href="{{ route('events.finance.budget', $event) }}" class="rounded-lg px-3 py-1.5 font-semibold text-zinc-500 hover:bg-zinc-100">{{ __('finance.tabs.budget') }}</a>
    </nav>

    <x-stat :label="__('finance.expenses.total')" :value="\App\Support\Money::format($totalMinor)" tone="caution" />

    @if ($requireApproval)
        <p class="muted mt-2 text-xs">{{ __('finance.expenses.require_approval_notice') }}</p>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        @can('recordExpenses', $event)
            <div class="card p-6 lg:col-span-1">
                <h2 class="section-title">{{ __('finance.expenses.add') }}</h2>
                <form method="POST" action="{{ route('events.finance.expenses.store', $event) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="label" for="description">{{ __('finance.expenses.description') }} *</label>
                        <input class="input" id="description" name="description" value="{{ old('description') }}" required maxlength="255">
                    </div>
                    <div>
                        <label class="label" for="amount">{{ __('common.amount') }} *</label>
                        <input class="input" id="amount" name="amount" value="{{ old('amount') }}" required inputmode="decimal" placeholder="150000">
                        <p class="mt-1 text-xs text-zinc-400">{{ __('common.money_hint') }}</p>
                    </div>
                    <div>
                        <label class="label" for="incurred_at">{{ __('finance.expenses.incurred_at') }} *</label>
                        <input class="input" type="date" id="incurred_at" name="incurred_at" value="{{ old('incurred_at', date('Y-m-d')) }}" required>
                    </div>
                    <div>
                        <label class="label" for="payment_status">{{ __('finance.expenses.payment_status') }} *</label>
                        <select class="input" id="payment_status" name="payment_status" required>
                            <option value="pending" @selected(old('payment_status', 'pending') === 'pending')>{{ __('finance.expenses.payment.pending') }}</option>
                            <option value="paid" @selected(old('payment_status') === 'paid')>{{ __('finance.expenses.payment.paid') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="label" for="budget_category_id">{{ __('finance.expenses.category') }}</label>
                        <select class="input" id="budget_category_id" name="budget_category_id">
                            <option value="">{{ __('common.none') }}</option>
                            @foreach ($budgetCategories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('budget_category_id') === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="vendor_id">{{ __('finance.expenses.vendor') }}</label>
                        <select class="input" id="vendor_id" name="vendor_id">
                            <option value="">{{ __('common.none') }}</option>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->id }}" @selected((string) old('vendor_id') === (string) $vendor->id)>{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="receipt">{{ __('finance.expenses.receipt') }}</label>
                        <input class="input" type="file" id="receipt" name="receipt" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                    <button type="submit" class="btn btn-primary w-full">{{ __('common.save') }}</button>
                </form>
            </div>
        @endcan

        <div class="lg:col-span-2">
            @if ($expenses->isEmpty())
                <div class="empty-state">
                    <p class="font-semibold text-zinc-700">{{ __('finance.expenses.empty') }}</p>
                </div>
            @else
                <div class="card overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="table-base">
                            <thead>
                                <tr>
                                    <th>{{ __('finance.expenses.columns.date') }}</th>
                                    <th>{{ __('finance.expenses.columns.description') }}</th>
                                    <th>{{ __('finance.expenses.columns.amount') }}</th>
                                    <th class="hidden sm:table-cell">{{ __('finance.expenses.columns.category') }}</th>
                                    <th>{{ __('finance.expenses.columns.approval') }}</th>
                                    <th><span class="sr-only">{{ __('common.actions') }}</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($expenses as $expense)
                                    <tr class="{{ $expense->isReversal() ? 'bg-rose-50/40' : '' }}">
                                        <td class="whitespace-nowrap text-zinc-500">{{ $expense->incurred_at->format('d M Y') }}</td>
                                        <td>
                                            <p class="font-medium text-zinc-900">{{ $expense->description }}</p>
                                            <p class="text-xs text-zinc-400">{{ $expense->vendor?->name ?? '' }} {{ $expense->payment_status === 'paid' ? '· '.__('finance.expenses.payment.paid') : '· '.__('finance.expenses.payment.pending') }}</p>
                                        </td>
                                        <td class="font-semibold {{ $expense->amount_minor < 0 ? 'text-rose-700' : 'text-zinc-900' }}">{{ \App\Support\Money::format($expense->amount_minor) }}</td>
                                        <td class="hidden sm:table-cell text-zinc-500">{{ $expense->budgetCategory?->name ?? '—' }}</td>
                                        <td>
                                            @if ($expense->isReversed())
                                                <span class="badge bg-rose-50 text-rose-700 ring-rose-200">{{ __('finance.contributions.reversed_badge') }}</span>
                                            @else
                                                <span class="badge {{ match ($expense->approval_status) {
                                                    'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                                    'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                                    default => 'bg-zinc-100 text-zinc-500 ring-zinc-200',
                                                } }}">{{ __('finance.expenses.approval.'.$expense->approval_status) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if (! $expense->isReversal() && ! $expense->isReversed())
                                                @can('recordExpenses', $event)
                                                    <div class="flex flex-wrap items-center justify-end gap-1">
                                                        @if ($expense->approval_status === 'pending')
                                                            <form method="POST" action="{{ route('events.finance.expenses.approve', [$event, $expense]) }}">
                                                                @csrf
                                                                <button type="submit" class="btn btn-primary btn-sm">{{ __('finance.expenses.approve') }}</button>
                                                            </form>
                                                        @endif
                                                        <form method="POST" action="{{ route('events.finance.expenses.reverse', [$event, $expense]) }}"
                                                              onsubmit="event.preventDefault(); const reason = prompt({{ json_encode(__('finance.reverse_reason')) }}); if (reason) { this.reason.value = reason; this.submit(); };">
                                                            @csrf
                                                            <input type="hidden" name="reason" value="">
                                                            <button type="submit" class="btn btn-secondary btn-sm">{{ __('finance.expenses.reverse_action') }}</button>
                                                        </form>
                                                    </div>
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
                <div class="mt-4">{{ $expenses->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
