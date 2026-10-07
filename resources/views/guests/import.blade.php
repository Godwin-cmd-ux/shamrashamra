<x-app-layout>
    <x-slot name="header">
        @include('events._tabs', ['event' => $event])
    </x-slot>

    <div class="card mx-auto max-w-2xl p-6 sm:p-8">
        <h2 class="section-title">{{ __('guests.import_heading') }}</h2>
        <p class="muted mt-1">{{ __('guests.import_help') }}</p>

        <div class="mt-4 rounded-xl bg-zinc-50 p-4 text-sm text-zinc-600 ring-1 ring-zinc-200">
            <p class="font-semibold">{{ __('guests.import_hint') }}</p>
        </div>

        <form method="POST" action="{{ route('events.guests.import.store', $event) }}" enctype="multipart/form-data" class="mt-5">
            @csrf
            <div>
                <label class="label" for="csv_file">CSV</label>
                <input class="input" type="file" id="csv_file" name="csv_file" accept=".csv,text/csv" required>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('events.guests.index', $event) }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('guests.import_button') }}</button>
            </div>
        </form>
    </div>
</x-app-layout>
