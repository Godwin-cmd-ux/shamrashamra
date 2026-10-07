@php
    $guest = $guest ?? null;
    $categories = $categories ?? [];
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label class="label" for="name">{{ __('guests.columns.name') }} *</label>
        <input class="input" id="name" name="name" value="{{ old('name', $guest?->name) }}" required maxlength="160">
    </div>
    <div>
        <label class="label" for="category">{{ __('guests.columns.category') }}</label>
        <input class="input" id="category" name="category" value="{{ old('category', $guest?->category) }}" list="guest-categories" maxlength="64">
        <datalist id="guest-categories">
            @foreach ($categories as $category)
                <option value="{{ $category }}"></option>
            @endforeach
        </datalist>
    </div>
    <div>
        <label class="label" for="phone">{{ __('guests.columns.phone') }}</label>
        <input class="input" id="phone" name="phone" value="{{ old('phone', $guest?->phone) }}" maxlength="32" placeholder="+255 712 345 678">
    </div>
    <div>
        <label class="label" for="email">{{ __('guests.columns.email') }}</label>
        <input class="input" type="email" id="email" name="email" value="{{ old('email', $guest?->email) }}" maxlength="255">
    </div>
    <div class="sm:col-span-2">
        <label class="label" for="notes">{{ __('guests.columns.notes') }}</label>
        <textarea class="input" id="notes" name="notes" rows="3" maxlength="2000">{{ old('notes', $guest?->notes) }}</textarea>
        <p class="mt-1 text-xs text-zinc-400">{{ __('guests.notes_help') }}</p>
    </div>
</div>
