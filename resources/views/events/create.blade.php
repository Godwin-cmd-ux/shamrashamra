<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="page-title">{{ __('events.create') }}</h1>
            <p class="muted mt-1">{{ __('events.subheading') }}</p>
        </div>
    </x-slot>

    <div class="card mx-auto max-w-3xl p-6 sm:p-8">
        <form method="POST" action="{{ route('events.store') }}">
            @csrf

            @include('events._form', ['event' => null])

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('events.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('common.create') }}</button>
            </div>
        </form>
    </div>
</x-app-layout>
