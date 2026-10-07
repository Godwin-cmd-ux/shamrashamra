<x-app-layout>
    <x-slot name="header">
        @include('events._tabs', ['event' => $event])
    </x-slot>

    <div class="card mx-auto max-w-2xl p-6 sm:p-8">
        <h2 class="section-title">{{ __('guests.create') }}</h2>
        <form method="POST" action="{{ route('events.guests.store', $event) }}" class="mt-5">
            @csrf
            @include('guests._form')

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('events.guests.index', $event) }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</x-app-layout>
