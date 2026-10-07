<x-app-layout>
    <x-slot name="header">
        @include('events._tabs', ['event' => $event])
    </x-slot>

    <div class="card overflow-hidden">
        <div class="border-b border-zinc-100 px-5 py-4">
            <h2 class="section-title">{{ __('events.overview.recent_activity') }}</h2>
            <p class="muted mt-1">{{ __('events.overview.no_activity') }}</p>
        </div>

        @if ($logs->isEmpty())
            <div class="empty-state border-0">
                <p class="muted">{{ __('events.overview.no_activity') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>{{ __('common.date') }}</th>
                            <th>{{ __('common.name') }}</th>
                            <th>Action</th>
                            <th class="hidden sm:table-cell">Metadata</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td class="whitespace-nowrap text-zinc-500">{{ $log->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $log->actor?->name ?? __('common.unknown') }}</td>
                                <td><span class="badge bg-zinc-100 text-zinc-700 ring-zinc-200">{{ $log->action }}</span></td>
                                <td class="hidden max-w-md text-xs text-zinc-500 sm:table-cell">
                                    @if ($log->subject_type)
                                        <span class="block">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</span>
                                    @endif
                                    @if ($log->metadata)
                                        <span class="block truncate">{{ json_encode($log->metadata) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
</x-app-layout>
