@php
    $event = $event ?? null;
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="label" for="title">{{ __('events.fields.title') }} *</label>
        <input class="input" id="title" name="title" value="{{ old('title', $event?->title) }}" required maxlength="160">
    </div>

    <div>
        <label class="label" for="category_id">{{ __('events.fields.category') }}</label>
        <select class="input" id="category_id" name="category_id">
            <option value="">{{ __('common.none') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $event?->category_id) === (string) $category->id)>{{ $category->name() }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="label" for="timezone">{{ __('events.fields.timezone') }} *</label>
        <select class="input" id="timezone" name="timezone" required>
            @foreach ($timezones as $tz)
                <option value="{{ $tz }}" @selected(old('timezone', $event?->timezone ?? 'Africa/Dar_es_Salaam') === $tz)>{{ $tz }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="label" for="starts_at">{{ __('events.fields.starts_at') }}</label>
        <input class="input" type="datetime-local" id="starts_at" name="starts_at"
               value="{{ old('starts_at', isset($event) && $event->starts_at ? $event->starts_at->format('Y-m-d\TH:i') : '') }}">
    </div>

    <div>
        <label class="label" for="rsvp_deadline">{{ __('events.fields.rsvp_deadline') }}</label>
        <input class="input" type="datetime-local" id="rsvp_deadline" name="rsvp_deadline"
               value="{{ old('rsvp_deadline', isset($event) && $event->rsvp_deadline ? $event->rsvp_deadline->format('Y-m-d\TH:i') : '') }}">
    </div>

    <div>
        <label class="label" for="venue_name">{{ __('events.fields.venue_name') }}</label>
        <input class="input" id="venue_name" name="venue_name" value="{{ old('venue_name', $event?->venue_name) }}" maxlength="160">
    </div>

    <div>
        <label class="label" for="venue_address">{{ __('events.fields.venue_address') }}</label>
        <input class="input" id="venue_address" name="venue_address" value="{{ old('venue_address', $event?->venue_address) }}" maxlength="255">
    </div>

    <div>
        <label class="label" for="map_link">{{ __('events.fields.map_link') }}</label>
        <input class="input" type="url" id="map_link" name="map_link" value="{{ old('map_link', $event?->map_link) }}" maxlength="255">
    </div>

    <div>
        <label class="label" for="guest_capacity">{{ __('events.fields.guest_capacity') }} <span class="text-zinc-400">({{ __('events.fields.guest_capacity_help') }})</span></label>
        <input class="input" type="number" min="1" id="guest_capacity" name="guest_capacity" value="{{ old('guest_capacity', $event?->guest_capacity) }}">
    </div>

    <div>
        <label class="label" for="host_names">{{ __('events.fields.host_names') }}</label>
        <input class="input" id="host_names" name="host_names" value="{{ old('host_names', $event?->host_names) }}" maxlength="255">
    </div>

    <div>
        <label class="label" for="dress_code">{{ __('events.fields.dress_code') }}</label>
        <input class="input" id="dress_code" name="dress_code" value="{{ old('dress_code', $event?->dress_code) }}" maxlength="120">
    </div>

    <div class="sm:col-span-2">
        <label class="label" for="description">{{ __('events.fields.description') }}</label>
        <textarea class="input" id="description" name="description" rows="4" maxlength="5000">{{ old('description', $event?->description) }}</textarea>
    </div>
</div>
