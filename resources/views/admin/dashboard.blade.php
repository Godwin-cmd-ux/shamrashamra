<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="page-title">{{ __('admin.heading') }}</h1>
            <p class="muted mt-1">{{ __('admin.subheading') }}</p>
        </div>
    </x-slot>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
        <x-stat :label="__('admin.stats.users')" :value="$stats['users']" />
        <x-stat :label="__('admin.stats.active_users')" :value="$stats['active_users']" />
        <x-stat :label="__('admin.stats.events')" :value="$stats['events']" />
        <x-stat :label="__('admin.stats.published')" :value="$stats['published']" tone="positive" />
        <x-stat :label="__('admin.stats.drafts')" :value="$stats['drafts']" />
        <x-stat :label="__('admin.stats.archived')" :value="$stats['archived']" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="card p-6">
            <h2 class="section-title">{{ __('admin.categories') }}</h2>

            <form method="POST" action="{{ route('admin.categories.store') }}" class="mt-4 grid gap-3">
                @csrf
                <div>
                    <label class="label" for="name_en">{{ __('admin.name_en') }} *</label>
                    <input class="input" id="name_en" name="name_en" value="{{ old('name_en') }}" required maxlength="80">
                </div>
                <div>
                    <label class="label" for="name_sw">{{ __('admin.name_sw') }} *</label>
                    <input class="input" id="name_sw" name="name_sw" value="{{ old('name_sw') }}" required maxlength="80">
                </div>
                <div>
                    <label class="label" for="slug">{{ __('admin.slug') }} *</label>
                    <input class="input" id="slug" name="slug" value="{{ old('slug') }}" required pattern="[a-z0-9\-]+" maxlength="80">
                </div>
                <button type="submit" class="btn btn-primary">{{ __('admin.add_category') }}</button>
            </form>

            <ul class="mt-5 divide-y divide-zinc-100 text-sm">
                @foreach ($categories as $category)
                    <li class="flex items-center justify-between gap-2 py-2">
                        <div>
                            <p class="font-semibold text-zinc-800">{{ $category->name_en }} / {{ $category->name_sw }}</p>
                            <p class="text-xs text-zinc-400">{{ $category->slug }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.categories.toggle', $category) }}">
                            @csrf
                            <button type="submit" class="badge {{ $category->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-zinc-100 text-zinc-500 ring-zinc-200' }}">
                                {{ $category->is_active ? __('common.active') : __('common.inactive') }}
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="card overflow-hidden lg:col-span-2">
            <div class="border-b border-zinc-100 px-5 py-4">
                <h2 class="section-title">{{ __('admin.recent_events') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>{{ __('admin.columns.event') }}</th>
                            <th>{{ __('admin.columns.organizer') }}</th>
                            <th>{{ __('admin.columns.status') }}</th>
                            <th class="hidden sm:table-cell">{{ __('admin.columns.created') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentEvents as $event)
                            <tr>
                                <td class="font-semibold text-zinc-900">{{ $event->title }}</td>
                                <td class="text-zinc-500">{{ $event->organizer?->name }}</td>
                                <td><span @class(['badge', $event->statusEnum()->badgeClasses()])>{{ $event->statusEnum()->label() }}</span></td>
                                <td class="hidden text-zinc-500 sm:table-cell">{{ $event->created_at->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
