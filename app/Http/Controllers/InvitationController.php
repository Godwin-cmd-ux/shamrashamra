<?php

namespace App\Http\Controllers;

use App\Enums\InvitationStatus;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Invitation;
use App\Models\OutgoingNotification;
use App\Services\AuditService;
use App\Services\InvitationService;
use App\Services\QrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    public function index(Request $request, Event $event)
    {
        $this->authorize('manageInvitations', $event);

        $invitations = $event->invitations()
            ->with(['guest', 'attendanceRecord'])
            ->when($request->filled('rsvp'), fn ($q) => $q->where('rsvp_status', $request->string('rsvp')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.strtolower($request->string('q')).'%';
                $q->where(fn ($w) => $w->whereRaw('lower(coalesce(label,\'\')) like ?', [$term])
                    ->orWhereHas('guest', fn ($g) => $g->whereRaw('lower(name) like ?', [$term])));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'issued' => $event->invitations()->where('status', 'issued')->count(),
            'revoked' => $event->invitations()->where('status', InvitationStatus::Revoked->value)->count(),
            'confirmed' => $event->invitations()->where('rsvp_status', 'confirmed')->count(),
            'checked_in' => $event->attendanceRecords()->count(),
        ];

        $outbox = $event->outgoingNotifications()->latest()->limit(10)->get();

        return view('invitations.index', [
            'event' => $event,
            'invitations' => $invitations,
            'counts' => $counts,
            'outbox' => $outbox,
        ]);
    }

    public function create(Request $request, Event $event)
    {
        $this->authorize('manageInvitations', $event);

        return view('invitations.create', [
            'event' => $event,
            'guests' => $event->guests()->orderBy('name')->limit(1000)->get(['id', 'name', 'phone']),
            'presetGuest' => $request->integer('guest_id') ?: null,
        ]);
    }

    public function store(Request $request, Event $event, InvitationService $invitations)
    {
        $this->authorize('manageInvitations', $event);

        $data = $request->validate([
            'guest_id' => ['nullable', 'integer', 'exists:guests,id'],
            'label' => ['nullable', 'string', 'max:160'],
            'entitlement_count' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        if (empty($data['guest_id']) && empty($data['label'])) {
            return back()->withErrors(['label' => __('invitations.need_guest_or_label')])->withInput();
        }

        if (! empty($data['guest_id'])) {
            $guest = Guest::where('event_id', $event->id)->find($data['guest_id']);
            abort_unless($guest !== null, 404);
            $data['label'] = $data['label'] ?: $guest->name;
        }

        [$invitation] = $invitations->issue($event, $data, Auth::user());

        return redirect()->route('events.invitations.show', [$event, $invitation])
            ->with('status', __('invitations.issued'));
    }

    public function show(Event $event, Invitation $invitation)
    {
        $this->authorize('manageInvitations', $event);

        abort_unless($invitation->event_id === $event->id, 404);

        return view('invitations.show', [
            'event' => $event,
            'invitation' => $invitation->load(['guest', 'attendanceRecord.attendant', 'issuedBy']),
            'url' => $invitation->token_encrypted
                ? app(InvitationService::class)->urlForToken($invitation->token_encrypted)
                : null,
        ]);
    }

    public function qr(Event $event, Invitation $invitation, QrService $qr)
    {
        $this->authorize('manageInvitations', $event);

        abort_unless($invitation->event_id === $event->id, 404);
        abort_unless($invitation->status === InvitationStatus::Issued->value, 410);

        $url = app(InvitationService::class)->urlForToken($invitation->token_encrypted);

        return response($qr->png($url), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="qr-'.$invitation->public_id.'.png"',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    public function revoke(Request $request, Event $event, Invitation $invitation, InvitationService $service)
    {
        $this->authorize('manageInvitations', $event);

        abort_unless($invitation->event_id === $event->id, 404);

        if ($invitation->status !== InvitationStatus::Issued->value) {
            return back()->withErrors(['invitation' => __('invitations.not_issued')]);
        }

        $service->revoke($invitation, $request->user(), $request->ip());

        return back()->with('status', __('invitations.revoked'));
    }

    public function replace(Request $request, Event $event, Invitation $invitation, InvitationService $service)
    {
        $this->authorize('manageInvitations', $event);

        abort_unless($invitation->event_id === $event->id, 404);

        if ($invitation->status !== InvitationStatus::Issued->value) {
            return back()->withErrors(['invitation' => __('invitations.not_issued')]);
        }

        [$new] = $service->replace($invitation, $request->user(), $request->ip());

        return redirect()->route('events.invitations.show', [$event, $new])
            ->with('status', __('invitations.replaced'));
    }

    /** Log a WhatsApp share attempt, then hand off to the share chooser. */
    public function share(Request $request, Event $event, Invitation $invitation)
    {
        $this->authorize('manageInvitations', $event);

        abort_unless($invitation->event_id === $event->id, 404);
        abort_unless($invitation->status === InvitationStatus::Issued->value, 410);

        $url = app(InvitationService::class)->urlForToken($invitation->token_encrypted);

        $notification = OutgoingNotification::create([
            'event_id' => $event->id,
            'invitation_id' => $invitation->id,
            'channel' => 'whatsapp',
            'recipient' => $invitation->guest?->phone ?: __('invitations.unset_recipient'),
            'subject' => $event->title,
            'status' => 'manual',
            'created_by' => $request->user()->id,
        ]);

        $notification->attempts()->create([
            'attempt' => 1,
            'status' => 'manual',
            'detail' => 'shared_via_whatsapp_chooser',
            'created_at' => now(),
        ]);

        AuditService::record($request->user(), $event, 'invitation.shared', $invitation, [
            'channel' => 'whatsapp',
        ], $request->ip());

        $text = rawurlencode($event->title.' — '.__('invitations.share_text').' '.$url);

        return redirect()->away('https://wa.me/?text='.$text);
    }
}
