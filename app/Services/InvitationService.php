<?php

namespace App\Services;

use App\Enums\InvitationStatus;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Invitation;
use App\Models\User;

class InvitationService
{
    /**
     * Issue a new invitation. Returns the invitation and the one-time raw token.
     * Only a SHA-256 hash is stored for verification; the raw token is kept
     * encrypted solely so organizers can re-share the link later.
     *
     * @return array{0: Invitation, 1: string}
     */
    public function issue(Event $event, array $data, User $issuedBy): array
    {
        $token = bin2hex(random_bytes(32));

        $invitation = Invitation::create([
            'event_id' => $event->id,
            'guest_id' => $data['guest_id'] ?? null,
            'label' => $data['label'] ?? null,
            'entitlement_count' => max(1, (int) ($data['entitlement_count'] ?? 1)),
            'status' => InvitationStatus::Issued->value,
            'token_hash' => hash('sha256', $token),
            'token_encrypted' => $token,
            'issued_by' => $issuedBy->id,
        ]);

        AuditService::record($issuedBy, $event, 'invitation.issued', $invitation, [
            'entitlement_count' => $invitation->entitlement_count,
            'guest_id' => $invitation->guest_id,
        ]);

        return [$invitation, $token];
    }

    public function revoke(Invitation $invitation, User $actor, ?string $ip = null): Invitation
    {
        $invitation->update([
            'status' => InvitationStatus::Revoked->value,
            'revoked_at' => now(),
            'revoked_by' => $actor->id,
        ]);

        AuditService::record($actor, $invitation->event, 'invitation.revoked', $invitation, [], $ip);

        return $invitation;
    }

    /**
     * Replace an invitation: the old token stops authorizing entry immediately.
     *
     * @return array{0: Invitation, 1: string} new invitation and raw token
     */
    public function replace(Invitation $old, User $actor, ?string $ip = null): array
    {
        $token = bin2hex(random_bytes(32));

        $new = Invitation::create([
            'event_id' => $old->event_id,
            'guest_id' => $old->guest_id,
            'label' => $old->label,
            'entitlement_count' => $old->entitlement_count,
            'status' => InvitationStatus::Issued->value,
            'token_hash' => hash('sha256', $token),
            'token_encrypted' => $token,
            'issued_by' => $actor->id,
        ]);

        $old->update([
            'status' => InvitationStatus::Replaced->value,
            'replaced_by_id' => $new->id,
            'revoked_at' => now(),
            'revoked_by' => $actor->id,
        ]);

        AuditService::record($actor, $old->event, 'invitation.replaced', $old, [
            'replaced_by' => $new->id,
        ], $ip);

        return [$new, $token];
    }

    /** Public guest-facing URL for a raw token. */
    public function urlForToken(string $token): string
    {
        return url('/i/'.$token);
    }
}
