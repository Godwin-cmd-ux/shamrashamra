<x-app-layout>
    <x-slot name="header">
        @include('events._tabs', ['event' => $event])
    </x-slot>

    <p class="muted mb-4">{{ __('reports.help') }}</p>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($reports as $key => $report)
            <div class="card flex flex-col p-5">
                <h2 class="section-title">{{ $report['label'] }}</h2>
                <p class="muted mt-1 flex-1">{{ $report['description'] }}</p>
                <a href="{{ route('events.reports.export', [$event, $key]) }}" class="btn btn-secondary mt-4 self-start">
                    {{ __('reports.download') }}
                </a>
            </div>
        @endforeach

        @if (empty($reports))
            <div class="empty-state sm:col-span-2 lg:col-span-3">
                <p class="font-semibold text-zinc-700">{{ __('common.no_results') }}</p>
            </div>
        @endif
    </div>
</x-app-layout>
