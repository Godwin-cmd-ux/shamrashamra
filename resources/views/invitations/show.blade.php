<x-app-layout>
    <x-slot name="header">
        @include('events._tabs', ['event' => $event])
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Invitation card --}}
        <div class="card overflow-hidden">
            <div class="bg-brand-950 px-6 py-5 text-center">
                <p class="text-[10px] uppercase tracking-[0.3em] text-gold-400">{{ __('invitations.guest_view.title') }}</p>
                <p class="mt-3 font-display text-2xl text-white">{{ $invitation->displayLabel() }}</p>
                <p class="mt-1 text-sm text-brand-100/70">{{ $event->title }}</p>
                @if ($event->starts_at)
                    <p class="mt-1 text-xs text-brand-100/60">{{ $event->starts_at->copy()->timezone($event->timezone)->isoFormat('dddd, D MMMM YYYY') }}</p>
                @endif
            </div>

            <div class="p-6 text-center">
                @if ($invitation->status === 'issued')
                    <img src="{{ route('events.invitations.qr', [$event, $invitation]) }}"
                         alt="{{ __('invitations.download_qr') }}"
                         class="mx-auto h-44 w-44 rounded-xl ring-1 ring-zinc-200"
                         loading="lazy">
                    <p class="muted mt-3 text-xs">{{ __('invitations.token_hint') }}: ••••{{ substr($invitation->token_hash, -6) }}</p>
                @else
                    <div class="rounded-xl bg-zinc-50 p-6 text-sm text-zinc-500 ring-1 ring-zinc-200">
                        {{ $invitation->status === 'revoked' ? __('invitations.revoked_notice') : __('invitations.replaced_notice') }}
                    </div>
                @endif

                @if ($url)
                    <div class="mt-4">
                        <input class="input text-xs" type="text" readonly value="{{ $url }}" onclick="this.select()" id="invite-url">
                        <div class="mt-3 flex flex-wrap justify-center gap-2">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('invite-url').value); this.textContent = {{ json_encode(__('invitations.copied')) }};">
                                {{ __('invitations.copy_link') }}
                            </button>
                            <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">{{ __('common.view') }}</a>
                            @if ($invitation->status === 'issued')
                                <a href="{{ route('events.invitations.qr', [$event, $invitation]) }}" download class="btn btn-secondary btn-sm">{{ __('invitations.download_qr') }}</a>
                                <form method="POST" action="{{ route('events.invitations.share', [$event, $invitation]) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">{{ __('invitations.share') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Details + actions --}}
        <div class="space-y-6">
            <div class="card p-6">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span @class(['badge',
                            'bg-emerald-50 text-emerald-700 ring-emerald-200' => $invitation->status === 'issued',
                            'bg-rose-50 text-rose-700 ring-rose-200' => $invitation->status === 'revoked',
                            'bg-zinc-100 text-zinc-500 ring-zinc-200' => $invitation->status === 'replaced',
                        ])>{{ __('invitations.status.'.$invitation->status) }}</span>
                        <span @class(['badge ms-1', \App\Enums\RsvpStatus::from($invitation->rsvp_status)->badgeClasses()])>
                            {{ \App\Enums\RsvpStatus::from($invitation->rsvp_status)->label() }}
                        </span>
                    </div>
                </div>

                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-zinc-500">{{ __('invitations.fields.guest') }}</dt><dd class="text-right font-medium">{{ $invitation->guest?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-zinc-500">{{ __('invitations.fields.entitlement_count') }}</dt><dd class="text-right font-medium">{{ $invitation->entitlement_count }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-zinc-500">{{ __('invitations.fields.guest_count') }}</dt><dd class="text-right font-medium">{{ $invitation->rsvp_guest_count ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-zinc-500">{{ __('invitations.fields.issued_by') }}</dt><dd class="text-right font-medium">{{ $invitation->issuedBy?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-zinc-500">{{ __('invitations.fields.issued_at') }}</dt><dd class="text-right font-medium">{{ $invitation->created_at->format('d M Y H:i') }}</dd></div>
                    @if ($invitation->rsvp_note)
                        <div class="flex justify-between gap-4"><dt class="text-zinc-500">{{ __('invitations.fields.rsvp_note') }}</dt><dd class="text-right font-medium">{{ $invitation->rsvp_note }}</dd></div>
                    @endif
                    <div class="flex justify-between gap-4">
                        <dt class="text-zinc-500">{{ __('invitations.fields.used') }}</dt>
                        <dd class="text-right font-medium">
                            @if ($invitation->attendanceRecord)
                                <span class="text-emerald-700">
                                    {{ $invitation->attendanceRecord->checked_in_at->format('d M Y HH:mm') }}
                                    · {{ $invitation->attendanceRecord->guest_count }} {{ __('checkin.result_labels.person') }}
                                </span>
                            @else
                                {{ __('invitations.fields.not_used') }}
                            @endif
                        </dd>
                    </div>
                </dl>

                @if ($invitation->status === 'issued')
                    <div class="mt-5 flex flex-wrap gap-2 border-t border-zinc-100 pt-4">
                        <form method="POST" action="{{ route('events.invitations.revoke', [$event, $invitation]) }}"
                              onsubmit="return confirm({{ json_encode(__('invitations.revoked_notice')) }});">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">{{ __('invitations.revoke') }}</button>
                        </form>
                        <form method="POST" action="{{ route('events.invitations.replace', [$event, $invitation]) }}"
                              onsubmit="return confirm({{ json_encode(__('invitations.replaced_notice')) }});">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm">{{ __('invitations.replace') }}</button>
                        </form>
                    </div>
                @endif
            </div>

            <div class="card p-6">
                <h3 class="section-title">{{ __('invitations.preview_note') }}</h3>
                <p class="muted mt-1 text-xs">{{ __('invitations.token_note') }}</p>
                @if ($url)
                    <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-secondary mt-3">{{ __('invitations.guest_view.title') }}</a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
