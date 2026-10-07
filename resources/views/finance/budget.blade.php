<x-app-layout>
    <x-slot name="header">
        @include('events._tabs', ['event' => $event])
    </x-slot>

    <nav class="mb-4 flex gap-1 text-sm">
        <a href="{{ route('events.finance.pledges', $event) }}" class="rounded-lg px-3 py-1.5 font-semibold text-zinc-500 hover:bg-zinc-100">{{ __('finance.tabs.pledges') }}</a>
        <a href="{{ route('events.finance.contributions', $event) }}" class="rounded-lg px-3 py-1.5 font-semibold text-zinc-500 hover:bg-zinc-100">{{ __('finance.tabs.contributions') }}</a>
        <a href="{{ route('events.finance.expenses', $event) }}" class="rounded-lg px-3 py-1.5 font-semibold text-zinc-500 hover:bg-zinc-100">{{ __('finance.tabs.expenses') }}</a>
        <a href="{{ route('events.finance.budget', $event) }}" class="rounded-lg bg-brand-50 px-3 py-1.5 font-semibold text-brand-800 ring-1 ring-brand-200">{{ __('finance.tabs.budget') }}</a>
    </nav>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
        <x-stat :label="__('finance.budget.planned_total')" :value="\App\Support\Money::format($plannedMinor)" />
        <x-stat :label="__('finance.budget.spent_total')" :value="\App\Support\Money::format($spentMinor)" tone="caution" />
        <x-stat :label="__('finance.budget.remaining_total')" :value="\App\Support\Money::format($plannedMinor - $spentMinor)" tone="positive" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        @can('manageBudget', $event)
            <div class="card p-6 lg:col-span-1">
                <h2 class="section-title">{{ __('finance.budget.add') }}</h2>
                <form method="POST" action="{{ route('events.finance.budget.store', $event) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="label" for="name">{{ __('finance.budget.name') }} *</label>
                        <input class="input" id="name" name="name" value="{{ old('name') }}" required maxlength="120">
                    </div>
                    <div>
                        <label class="label" for="planned_amount">{{ __('finance.budget.planned_amount') }} *</label>
                        <input class="input" id="planned_amount" name="planned_amount" value="{{ old('planned_amount') }}" required inputmode="decimal" placeholder="1000000">
                        <p class="mt-1 text-xs text-zinc-400">{{ __('common.money_hint') }}</p>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">{{ __('common.save') }}</button>
                </form>
            </div>
        @endcan

        <div class="lg:col-span-2">
            @if ($categories->isEmpty())
                <div class="empty-state">
                    <p class="font-semibold text-zinc-700">{{ __('finance.budget.empty') }}</p>
                </div>
            @else
                <div class="card overflow-hidden">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th>{{ __('finance.budget.columns.name') }}</th>
                                <th>{{ __('finance.budget.columns.planned') }}</th>
                                <th>{{ __('finance.budget.columns.spent') }}</th>
                                <th>{{ __('finance.budget.columns.variance') }}</th>
                                <th><span class="sr-only">{{ __('common.actions') }}</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                @php $spent = $category->spentMinor(); @endphp
                                <tr>
                                    <td class="font-semibold text-zinc-900">{{ $category->name }}</td>
                                    <td>{{ \App\Support\Money::format($category->planned_amount_minor) }}</td>
                                    <td class="text-amber-700">{{ \App\Support\Money::format($spent) }}</td>
                                    <td class="{{ $category->planned_amount_minor - $spent >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ \App\Support\Money::format($category->planned_amount_minor - $spent) }}
                                    </td>
                                    <td class="text-right">
                                        @can('manageBudget', $event)
                                            <div class="flex items-center justify-end gap-1">
                                                <button type="button" class="btn btn-secondary btn-sm"
                                                        x-data
                                                        @click="
                                                            const name = prompt({{ json_encode(__('finance.budget.name')) }}, @js($category->name));
                                                            if (name === null) return;
                                                            const amount = prompt({{ json_encode(__('finance.budget.planned_amount')) }}, @js((string) ($category->planned_amount_minor / 100)));
                                                            if (amount === null) return;
                                                            const form = document.getElementById('budget-edit-{{ $category->id }}');
                                                            form.name.value = name;
                                                            form.planned_amount.value = amount;
                                                            form.submit();
                                                        ">{{ __('common.edit') }}</button>
                                                <form id="budget-edit-{{ $category->id }}" method="POST" action="{{ route('events.finance.budget.update', [$event, $category]) }}" class="hidden">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="name" value="">
                                                    <input type="hidden" name="planned_amount" value="">
                                                </form>
                                                <form method="POST" action="{{ route('events.finance.budget.destroy', [$event, $category]) }}"
                                                      onsubmit="return confirm({{ json_encode(__('common.confirm_delete')) }});">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">{{ __('common.delete') }}</button>
                                                </form>
                                            </div>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
