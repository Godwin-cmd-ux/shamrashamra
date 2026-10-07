<x-app-layout>
    <x-slot name="header">
        @include('events._tabs', ['event' => $event])
    </x-slot>

    <script>
        window.checkinI18n = {
            scanning: @js(__('checkin.scanning')),
            permission: @js(__('checkin.permission_denied')),
            unavailable: @js(__('checkin.camera_unavailable')),
            unsupported: @js(__('checkin.unsupported_browser')),
            throttled: @js(__('auth.throttle', ['seconds' => 60, 'minutes' => 1]))
            network: @js(__('common.loading')),
        };
    </script>

    <div x-data="checkinController(@js(route('events.checkin.verify', $event)), @js(csrf_token()), window.checkinI18n)" class="mx-auto max-w-2xl">

        <div class="grid grid-cols-3 gap-3">
            <x-stat :label="__('checkin.stats.checked_in')" :value="$stats['checked_in']" />
            <x-stat :label="__('checkin.stats.guests')" :value="$stats['guests']" tone="positive" />
            <x-stat :label="__('checkin.stats.issued')" :value="$stats['issued']" />
        </div>

        {{-- Result panel --}}
        <div class="mt-4 rounded-2xl p-5 text-center ring-1 transition"
             :class="{
                'bg-zinc-50 ring-zinc-200': result.status === 'idle',
                'bg-emerald-50 ring-emerald-300': result.status === 'valid',
                'bg-amber-50 ring-amber-300': result.status === 'duplicate',
                'bg-rose-50 ring-rose-300': ['invalid', 'revoked', 'wrong_event'].includes(result.status),
             }"
             role="status" aria-live="polite">
            <p class="font-display text-xl font-semibold"
               :class="{
                    'text-zinc-700': result.status === 'idle',
                    'text-emerald-800': result.status === 'valid',
                    'text-amber-800': result.status === 'duplicate',
                    'text-rose-800': ['invalid', 'revoked', 'wrong_event'].includes(result.status),
               }"
               x-text="result.message">{{ __('checkin.scanning') }}</p>

            <template x-if="result.label">
                <div class="mt-3 grid grid-cols-2 gap-2 text-left text-sm sm:grid-cols-4">
                    <div class="rounded-lg bg-white/70 p-2">
                        <p class="text-[10px] uppercase tracking-wide text-zinc-400">{{ __('invitations.fields.label') }}</p>
                        <p class="font-semibold text-zinc-800" x-text="result.label"></p>
                    </div>
                    <div class="rounded-lg bg-white/70 p-2">
                        <p class="text-[10px] uppercase tracking-wide text-zinc-400">{{ __('checkin.result_labels.person') }}</p>
                        <p class="font-semibold text-zinc-800" x-text="result.guestCount ?? result.entitlement"></p>
                    </div>
                    <div class="rounded-lg bg-white/70 p-2">
                        <p class="text-[10px] uppercase tracking-wide text-zinc-400">{{ __('checkin.result_labels.checked_at') }}</p>
                        <p class="font-semibold text-zinc-800" x-text="result.checkedAt ?? '—'"></p>
                    </div>
                    <div class="rounded-lg bg-white/70 p-2">
                        <p class="text-[10px] uppercase tracking-wide text-zinc-400">{{ __('checkin.result_labels.checked_by') }}</p>
                        <p class="font-semibold text-zinc-800" x-text="result.checkedBy ?? @js(auth()->user()->name)"></p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Camera --}}
        <div class="mt-4 card p-5">
            <div class="flex items-center justify-between gap-3">
                <h2 class="section-title">{{ __('checkin.heading') }}</h2>
                <div class="flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm" x-show="!scanning" @click="start()">{{ __('checkin.start_camera') }}</button>
                    <button type="button" class="btn btn-secondary btn-sm" x-show="scanning" @click="stop()">{{ __('checkin.stop_camera') }}</button>
                </div>
            </div>

            <div class="mt-3 overflow-hidden rounded-xl bg-brand-950" x-show="cameraSupported" x-cloak>
                <video x-ref="video" playsinline muted class="mx-auto aspect-[4/3] w-full object-cover"></video>
            </div>

            <p class="mt-3 text-sm text-zinc-500" x-show="scanning" x-text="cameraMessage">{{ __('checkin.scanning') }}</p>

            <div class="mt-3 rounded-xl bg-amber-50 p-4 text-sm text-amber-800 ring-1 ring-amber-200" x-show="cameraError" x-cloak x-text="cameraError"></div>
        </div>

        {{-- Manual fallback --}}
        <div class="mt-4 card p-5">
            <h2 class="section-title">{{ __('checkin.manual_title') }}</h2>
            <p class="muted mt-1 text-xs">{{ __('checkin.manual_hint') }}</p>
            <form class="mt-3 flex flex-col gap-2 sm:flex-row" @submit.prevent="submitManual()">
                <input type="text" x-model="manualToken" class="input flex-1" :placeholder="manualPlaceholder" autocomplete="off">
                <button type="submit" class="btn btn-primary">{{ __('checkin.manual_verify') }}</button>
            </form>
        </div>

        {{-- Recent --}}
        <div class="mt-4 card p-5">
            <h2 class="section-title">{{ __('checkin.recent') }}</h2>
            @if ($recent->isEmpty())
                <p class="muted mt-2">{{ __('checkin.no_recent') }}</p>
            @else
                <ul class="mt-3 divide-y divide-zinc-100 text-sm">
                    @foreach ($recent as $record)
                        <li class="flex items-center justify-between gap-3 py-2">
                            <div>
                                <p class="font-semibold text-zinc-800">{{ $record->invitation?->displayLabel() }}</p>
                                <p class="text-xs text-zinc-400">{{ $record->attendant?->name }} · {{ $record->guest_count }} {{ __('checkin.result_labels.person') }}</p>
                            </div>
                            <span class="text-xs text-zinc-500">{{ $record->checked_in_at->format('H:i') }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-app-layout>
