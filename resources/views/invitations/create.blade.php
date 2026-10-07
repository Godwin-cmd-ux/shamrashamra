<x-app-layout>
    <x-slot name="header">
        @include('events._tabs', ['event' => $event])
    </x-slot>

    <div class="card mx-auto max-w-2xl p-6 sm:p-8">
        <h2 class="section-title">{{ __('invitations.issue') }}</h2>

        <form method="POST" action="{{ route('events.invitations.store', $event) }}" class="mt-5 space-y-5">
            @csrf

            <div>
                <label class="label" for="guest_id">{{ __('invitations.fields.guest') }}</label>
                <select class="input" id="guest_id" name="guest_id">
                    <option value="">{{ __('invitations.fields.guest_help') }}</option>
                    @foreach ($guests as $guest)
                        <option value="{{ $guest->id }}" @selected((string) old('guest_id', $presetGuest) === (string) $guest->id)>
                            {{ $guest->name }}{{ $guest->phone ? ' · '.$guest->phone : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="label" for="label">{{ __('invitations.fields.label') }}</label>
                <input class="input" id="label" name="label" value="{{ old('label') }}" maxlength="160">
                <p class="mt-1 text-xs text-zinc-400">{{ __('invitations.fields.label_help') }}</p>
            </div>

            <div>
                <label class="label" for="entitlement_count">{{ __('invitations.fields.entitlement_count') }} *</label>
                <input class="input" type="number" min="1" max="50" id="entitlement_count" name="entitlement_count" value="{{ old('entitlement_count', 1) }}" required>
                <p class="mt-1 text-xs text-zinc-400">{{ __('invitations.fields.entitlement_help') }}</p>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('events.invitations.index', $event) }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('invitations.issue') }}</button>
            </div>
        </form>
    </div>
</x-app-layout>
