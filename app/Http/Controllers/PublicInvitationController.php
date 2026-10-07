<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Rsvp;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Unauthenticated, token-gated invitation pages for guests.
 * Guests never see guest lists, other guests' details, or finances.
 */
class PublicInvitationController extends Controller
{
    private function findByToken(string $token): ?Invitation
    {
        return Invitation::with(['event.category', 'guest'])
            ->where('token_hash', hash('sha256', $token))
            ->first();
    }

    public function show(string $token)
    {
        $invitation = $this->findByToken($token);

        if ($invitation === null) {
            return response()->view('public.invitation-invalid', ['reason' => 'not_found'], 404);
        }

        if ($invitation->status !== 'issued') {
            return response()->view('public.invitation-invalid', ['reason' => 'invalid'], 410);
        }

        $event = $invitation->event;

        if ($event === null || $event->status !== 'published') {
            return response()->view('public.invitation-invalid', ['reason' => 'unavailable'], 404);
        }

        $rsvpOpen = $event->rsvp_deadline === null || now()->lte($event->rsvp_deadline);

        return view('public.invitation', [
            'invitation' => $invitation,
            'event' => $event,
            'rsvpOpen' => $rsvpOpen,
            'checkedIn' => $invitation->attendanceRecord()->exists(),
        ]);
    }

    public function rsvp(Request $request, string $token)
    {
        $invitation = $this->findByToken($token);

        if ($invitation === null) {
            return response()->view('public.invitation-invalid', ['reason' => 'not_found'], 404);
        }

        if ($invitation->status !== 'issued') {
            return response()->view('public.invitation-invalid', ['reason' => 'invalid'], 410);
        }

        $event = $invitation->event;

        if ($event === null || $event->status !== 'published') {
            return response()->view('public.invitation-invalid', ['reason' => 'unavailable'], 404);
        }

        if ($event->rsvp_deadline !== null && now()->gt($event->rsvp_deadline)) {
            return back()->withErrors(['rsvp' => __('invitations.rsvp_deadline_passed')]);
        }

        $data = $request->validate([
            'rsvp_status' => ['required', Rule::in(['confirmed', 'declined'])],
            'guest_count' => ['nullable', 'integer', 'min:1', 'max:'.$invitation->entitlement_count],
            'note' => ['nullable', 'string', 'max:200'],
        ], [
            'guest_count.max' => __('invitations.guest_count_exceeds', ['max' => $invitation->entitlement_count]),
        ]);

        if ($data['rsvp_status'] === 'confirmed') {
            $data['guest_count'] = max(1, (int) ($data['guest_count'] ?? 1));

            // Capacity guard: never trust the browser for head-count limits.
            if ($event->guest_capacity) {
                $alreadyConfirmed = (int) $event->invitations()
                    ->where('rsvp_status', 'confirmed')
                    ->where('id', '!=', $invitation->id)
                    ->sum('rsvp_guest_count');

                if ($alreadyConfirmed + $data['guest_count'] > $event->guest_capacity) {
                    return back()->withErrors(['rsvp' => __('invitations.capacity_reached')])->withInput();
                }
            }
        } else {
            $data['guest_count'] = null;
        }

        $invitation->update([
            'rsvp_status' => $data['rsvp_status'],
            'rsvp_guest_count' => $data['guest_count'],
            'rsvp_responded_at' => now(),
            'rsvp_note' => $data['note'] ?? null,
        ]);

        Rsvp::create([
            'event_id' => $event->id,
            'invitation_id' => $invitation->id,
            'status' => $data['rsvp_status'],
            'guest_count' => $data['guest_count'],
            'note' => $data['note'] ?? null,
            'source' => 'link',
            'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
            'responded_at' => now(),
        ]);

        AuditService::record(null, $event, 'guest.rsvp', $invitation, [
            'rsvp_status' => $data['rsvp_status'],
        ], $request->ip());

        return back()->with('status', __('invitations.rsvp_saved'));
    }
}
