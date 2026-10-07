<x-guest-layout>
    @php
        $style = $event->settings['template_style'] ?? null;
        $template = \App\Models\InvitationTemplate::platform()
            ->when($style, fn ($q) => $q->where('style', $style))
            ->first()
            ?? \App\Models\InvitationTemplate::platform()->first();

        $cfg = $template?->config ?? [
            'background' => '#12261f', 'surface' => '#1b3a30', 'text' => '#f5efe2',
            'muted' => '#c9bfa8', 'accent' => '#d4af37',
            'heading_font' => 'Playfair Display, Georgia, serif',
            'body_font' => 'Instrument Sans, system-ui, sans-serif',
            'ornament' => 'diamond',
        ];

        $rsvp = $invitation->rsvp_status;
    @endphp

    <div class="mx-auto max-w-2xl px-4 py-8 sm:px-0">
        {{-- Invitation card --}}
        <article class="overflow-hidden rounded-3xl shadow-soft ring-1 ring-black/5"
                 style="background: {{ $cfg['background'] }}; color: {{ $cfg['text'] }};">
            <div class="px-6 py-10 text-center sm:px-10">
                <p class="text-[11px] uppercase tracking-[0.35em]" style="color: {{ $cfg['accent'] }};">
                    {{ __('invitations.guest_view.title') }}
                </p>

                <div class="my-5 flex items-center justify-center gap-3" aria-hidden="true">
                    <span class="h-px w-12" style="background: {{ $cfg['accent'] }}66;"></span>
                    <span style="color: {{ $cfg['accent'] }};">&#10022;</span>
                    <span class="h-px w-12" style="background: {{ $cfg['accent'] }}66;"></span>
                </div>

                @if ($event->host_names)
                    <p class="text-sm" style="color: {{ $cfg['muted'] }};">
                        {{ $event->host_names }} {{ __('invitations.guest_view.event_by') }}
                    </p>
                @endif

                <h1 class="mt-3 text-3xl leading-tight sm:text-4xl" style="font-family: {{ $cfg['heading_font'] }};">
                    {{ $event->title }}
                </h1>

                <p class="mt-4 text-sm font-semibold" style="color: {{ $cfg['accent'] }};">
                    {{ $invitation->displayLabel() }}
                    @if ($invitation->entitlement_count > 1)
                        · {{ $invitation->entitlement_count }} {{ __('invitations.fields.entitlement_count') }}
                    @endif
                </p>

                <dl class="mx-auto mt-8 max-w-sm space-y-4 text-center text-sm">
                    @if ($event->starts_at)
                        <div>
                            <dt class="text-[10px] uppercase tracking-widest" style="color: {{ $cfg['muted'] }};">{{ __('invitations.guest_view.when') }}</dt>
                            <dd class="mt-1 font-medium">
                                {{ $event->starts_at->copy()->timezone($event->timezone)->isoFormat('dddd, D MMMM YYYY') }}<br>
                                {{ $event->starts_at->copy()->timezone($event->timezone)->format('HH:mm') }} ({{ $event->timezone }})
                            </dd>
                        </div>
                    @endif

                    <div>
                        <dt class="text-[10px] uppercase tracking-widest" style="color: {{ $cfg['muted'] }};">{{ __('invitations.guest_view.where') }}</dt>
                        <dd class="mt-1 font-medium">
                            {{ $event->venue_name ?? '—' }}
                            @if ($event->venue_address)
                                <br><span class="text-xs" style="color: {{ $cfg['muted'] }};">{{ $event->venue_address }}</span>
                            @endif
                        </dd>
                        @if ($event->map_link)
                            <dd class="mt-2">
                                <a href="{{ $event->map_link }}" target="_blank" rel="noopener"
                                   class="underline underline-offset-4" style="color: {{ $cfg['accent'] }};">
                                    {{ __('invitations.guest_view.directions') }}
                                </a>
                            </dd>
                        @endif
                    </div>

                    @if ($event->dress_code)
                        <div>
                            <dt class="text-[10px] uppercase tracking-widest" style="color: {{ $cfg['muted'] }};">{{ __('invitations.guest_view.dress_code') }}</dt>
                            <dd class="mt-1 font-medium">{{ $event->dress_code }}</dd>
                        </div>
                    @endif
                </dl>

                @if ($event->description)
                    <div class="mx-auto mt-8 max-w-md border-t pt-6 text-sm leading-relaxed" style="border-color: {{ $cfg['accent'] }}33; color: {{ $cfg['muted'] }};">
                        <p class="text-[10px] uppercase tracking-widest" style="color: {{ $cfg['accent'] }};">{{ __('invitations.guest_view.message') }}</p>
                        <p class="mt-2">{{ $event->description }}</p>
                    </div>
                @endif
            </div>

            {{-- RSVP --}}
            <div class="px-6 pb-10 sm:px-10" style="background: {{ $cfg['surface'] }};">
                <div class="rounded-2xl p-5 text-center" style="background: {{ $cfg['background'] }}22;">
                    <p class="text-sm font-semibold">{{ __('invitations.guest_view.rsvp_title') }}</p>

                    @if ($checkedIn)
                        <p class="mt-2 text-xs font-medium" style="color: {{ $cfg['accent'] }};">{{ __('invitations.guest_view.checked_in_note') }}</p>
                    @endif

                    @if ($rsvp !== 'pending')
                        <p class="mt-2 text-xs" style="color: {{ $cfg['muted'] }};">
                            {{ __('invitations.guest_view.already_responded', ['date' => $invitation->rsvp_responded_at?->format('d M Y H:i') ?? '—']) }}
                        </p>
                        <p class="mt-1 text-sm font-semibold">
                            {{ __('invitations.guest_view.rs.'.$rsvp) }}
                            @if ($rsvp === 'confirmed' && $invitation->rsvp_guest_count)
                                — {{ __('invitations.guest_view.confirmed_headcount', ['count' => $invitation->rsvp_guest_count]) }}
                            @endif
                        </p>
                    @endif

                    @if ($rsvpOpen)
                        <form method="POST" action="{{ route('public.invitation.rsvp', $invitation->token_encrypted) }}" class="mt-4 space-y-3 text-left">
                            @csrf

                            <label class="flex items-center gap-3 rounded-xl p-3 ring-1" style="ring-color: {{ $cfg['accent'] }}44;">
                                <input type="radio" name="rsvp_status" value="confirmed" required
                                       @checked(old('rsvp_status', $rsvp) === 'confirmed') class="h-4 w-4">
                                <span class="text-sm font-medium">{{ __('invitations.guest_view.attending') }}</span>
                            </label>

                            <label class="flex items-center gap-3 rounded-xl p-3 ring-1" style="ring-color: {{ $cfg['accent'] }}44;">
                                <input type="radio" name="rsvp_status" value="declined"
                                       @checked(old('rsvp_status') === 'declined') class="h-4 w-4">
                                <span class="text-sm font-medium">{{ __('invitations.guest_view.declining') }}</span>
                            </label>

                            <div>
                                <label class="text-xs font-semibold" for="guest_count">{{ __('invitations.guest_view.guest_count_label') }}</label>
                                <input class="input mt-1" type="number" min="1" max="{{ $invitation->entitlement_count }}"
                                       id="guest_count" name="guest_count"
                                       value="{{ old('guest_count', $invitation->rsvp_guest_count ?? 1) }}"
                                       max="{{ $invitation->entitlement_count }}">
                            </div>

                            <div>
                                <label class="text-xs font-semibold" for="note">{{ __('common.notes') }}</label>
                                <input class="input mt-1" id="note" name="note" value="{{ old('note', $invitation->rsvp_note) }}" maxlength="200">
                            </div>

                            <button type="submit" class="btn w-full" style="background: {{ $cfg['accent'] }}; color: #101; ">
                                {{ __('invitations.guest_view.submit') }}
                            </button>
                        </form>

                        @if ($event->rsvp_deadline)
                            <p class="mt-3 text-xs" style="color: {{ $cfg['muted'] }};">
                                {{ __('invitations.guest_view.deadline', ['date' => $event->rsvp_deadline->format('d M Y')]) }}
                            </p>
                        @endif
                    @else
                        <p class="mt-3 text-xs font-medium" style="color: {{ $cfg['muted'] }};">{{ __('invitations.guest_view.deadline_passed') }}</p>
                    @endif

                    <p class="mt-5 text-[10px] uppercase tracking-widest" style="color: {{ $cfg['muted'] }};">
                        {{ __('invitations.guest_view.token_note') }}
                    </p>
                </div>
            </div>
        </article>

        <p class="mt-4 text-center text-xs text-zinc-400">shamrashamra.com</p>
    </div>
</x-guest-layout>
