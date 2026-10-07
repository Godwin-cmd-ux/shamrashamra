<x-app-layout>
    <x-slot name="header">
        @include('events._tabs', ['event' => $event])
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        @can('manageMembers', $event)
            <div class="card p-6 lg:col-span-1">
                <h2 class="section-title">{{ __('events.members.invite') }}</h2>
                <p class="muted mt-1">{{ __('events.members.invite_help') }}</p>

                <form method="POST" action="{{ route('events.members.store', $event) }}" class="mt-4 space-y-4">
                    @csrf

                    <div>
                        <label class="label" for="email">{{ __('events.members.email') }} *</label>
                        <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" required>
                    </div>

                    <fieldset>
                        <legend class="label">{{ __('events.members.role') }} *</legend>
                        <div class="space-y-2">
                            @foreach ($roles as $role)
                                <label class="flex items-center gap-2 text-sm text-zinc-700">
                                    <input type="radio" name="role" value="{{ $role->value }}" @checked(old('role', 'committee') === $role->value) class="text-brand-700 focus:ring-brand-600">
                                    {{ $role->label() }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset x-data>
                        <legend class="label">{{ __('events.members.permissions') }}</legend>
                        <p class="mb-2 text-xs text-zinc-400">{{ __('events.members.permissions_help') }}</p>
                        <div class="grid gap-1.5">
                            @foreach ($permissions as $permission)
                                <label class="flex items-start gap-2 text-sm text-zinc-700">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission }}"
                                           @checked(in_array(old('permissions', []), [$permission], true) || in_array($permission, old('permissions', []), true))
                                           class="mt-0.5 h-4 w-4 rounded border-zinc-300 text-brand-700 focus:ring-brand-600"
                                           onclick="if (document.querySelector('input[name=role][value=attendant]:checked')) this.checked = false;">
                                    <span>
                                        <span class="font-medium">{{ \App\Enums\EventPermission::from($permission)->label() }}</span>
                                        <span class="block text-xs text-zinc-400">{{ \App\Enums\EventPermission::from($permission)->description() }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <button type="submit" class="btn btn-primary w-full">{{ __('events.members.add_member') }}</button>
                </form>
            </div>
        @endcan

        <div class="lg:col-span-2">
            @if ($members->isEmpty())
                <div class="empty-state">
                    <p class="font-semibold text-zinc-700">{{ __('events.members.no_members') }}</p>
                </div>
            @else
                <div class="card overflow-hidden">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th>{{ __('common.name') }}</th>
                                <th>{{ __('events.members.role') }}</th>
                                <th class="hidden sm:table-cell">{{ __('events.members.permissions') }}</th>
                                <th><span class="sr-only">{{ __('common.actions') }}</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($members as $member)
                                <tr>
                                    <td>
                                        <p class="font-semibold text-zinc-900">{{ $member->user?->name }}</p>
                                        <p class="text-xs text-zinc-400">{{ $member->user?->email }}</p>
                                        @if ($member->user_id === $event->organizer_id)
                                            <span class="badge mt-1 bg-brand-50 text-brand-700 ring-brand-200">{{ __('events.members.owner_label') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($member->user_id === $event->organizer_id)
                                            {{ \App\Enums\MemberRole::Owner->label() }}
                                        @else
                                            <form method="POST" action="{{ route('events.members.update', [$event, $member]) }}" class="flex items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <select name="role" class="input !py-1.5 text-xs" onchange="this.form.submit()" @disabled($member->user_id === $event->organizer_id)>
                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role->value }}" @selected($member->role === $role->value)>{{ $role->label() }}</option>
                                                    @endforeach
                                                </select>
                                                @foreach ($permissions as $permission)
                                                    <input type="hidden" name="permissions[]"
                                                           value="{{ $permission }}"
                                                           @checked($member->permissions->pluck('permission')->contains($permission))>
                                                @endforeach
                                            </form>
                                            <span class="mt-1 block text-[10px] uppercase tracking-wide text-zinc-400">{{ $member->status }}</span>
                                        @endif
                                    </td>
                                    <td class="hidden sm:table-cell">
                                        <div class="flex flex-wrap gap-1">
                                            @if ($member->user_id === $event->organizer_id)
                                                <span class="badge bg-zinc-100 text-zinc-600 ring-zinc-200">{{ __('common.all') }}</span>
                                            @elseif ($member->role === \App\Enums\MemberRole::Attendant)
                                                @foreach ($member->permissions as $perm)
                                                    <span class="badge bg-sky-50 text-sky-700 ring-sky-200">{{ \App\Enums\EventPermission::tryFrom($perm->permission)?->label() ?? $perm->permission }}</span>
                                                @endforeach
                                            @else
                                                @forelse ($member->permissions as $perm)
                                                    <span class="badge bg-zinc-100 text-zinc-600 ring-zinc-200">{{ \App\Enums\EventPermission::tryFrom($perm->permission)?->label() ?? $perm->permission }}</span>
                                                @empty
                                                    <span class="text-xs text-zinc-400">{{ __('common.none') }}</span>
                                                @endforelse
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        @if ($member->user_id !== $event->organizer_id && $member->status !== 'revoked')
                                            <form method="POST" action="{{ route('events.members.destroy', [$event, $member]) }}"
                                                  onsubmit="return confirm({{ json_encode(__('common.confirm_delete')) }});">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">{{ __('events.members.remove') }}</button>
                                            </form>
                                        @endif
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
