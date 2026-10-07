<x-app-layout>
    <x-slot name="header">
        @include('events._tabs', ['event' => $event])
    </x-slot>

    <div class="card mx-auto max-w-2xl p-6 sm:p-8">
        <div class="flex items-center justify-between">
            <h2 class="section-title">{{ __('guests.edit') }}</h2>
            @can('manageGuests', $event)
                <form method="POST" action="{{ route('events.guests.destroy', [$event, $guest]) }}"
                      onsubmit="return confirm({{ json_encode(__('common.confirm_delete')) }});">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">{{ __('common.delete') }}</button>
                </form>
            @endcan
        </div>

        <form method="POST" action="{{ route('events.guests.update', [$event, $guest]) }}" class="mt-5">
            @csrf
            @method('PUT')
            @include('guests._form', ['guest' => $guest])

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('events.guests.index', $event) }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</x-app-layout>
