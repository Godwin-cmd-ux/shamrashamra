<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="page-title">{{ __('events.edit') }}</h1>
                <p class="muted mt-1">{{ $event->title }}</p>
            </div>
            <a href="{{ route('events.show', $event) }}" class="btn btn-secondary">{{ __('common.back') }}</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-6">
        <div class="card p-6 sm:p-8">
            <form method="POST" action="{{ route('events.update', $event) }}">
                @csrf
                @method('PUT')

                @include('events._form', ['event' => $event])

                <div class="mt-8 border-t border-zinc-100 pt-6">
                    <h2 class="section-title">{{ __('events.fields.settings') }}</h2>

                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="label" for="guest_categories_text">{{ __('events.fields.guest_categories') }}</label>
                            <input class="input" id="guest_categories_text" name="guest_categories_text"
                                   value="{{ old('guest_categories_text', implode(', ', $event->guestCategories())) }}">
                            <p class="mt-1 text-xs text-zinc-400">{{ __('events.fields.guest_categories_help') }}</p>
                        </div>

                        <div class="sm:col-span-2 flex items-start gap-3 rounded-xl bg-zinc-50 p-4 ring-1 ring-zinc-200">
                            <input type="hidden" name="require_expense_approval" value="0">
                            <input type="checkbox" id="require_expense_approval" name="require_expense_approval" value="1"
                                   @checked(old('require_expense_approval', $event->requireExpenseApproval())) class="mt-0.5 h-4 w-4 rounded border-zinc-300 text-brand-700 focus:ring-brand-600">
                            <div>
                                <label for="require_expense_approval" class="text-sm font-semibold text-zinc-700">{{ __('events.fields.require_expense_approval') }}</label>
                                <p class="text-xs text-zinc-500">{{ __('events.fields.require_expense_approval_help') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('events.show', $event) }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('common.save') }}</button>
                </div>
            </form>
        </div>

        @can('delete', $event)
            <div class="card border-rose-200 p-6">
                <h2 class="section-title text-rose-700">{{ __('events.overview.danger_zone') }}</h2>
                <p class="muted mt-1">{{ __('events.overview.delete_help') }}</p>
                <form method="POST" action="{{ route('events.destroy', $event) }}" class="mt-4"
                      onsubmit="return confirm({{ json_encode(__('common.confirm_delete')) }});">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('common.delete') }}</button>
                </form>
            </div>
        @endcan
    </div>
</x-app-layout>
