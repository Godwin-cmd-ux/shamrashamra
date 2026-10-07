<?php

namespace App\Services;

use App\Enums\ScanOutcome;
use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\Invitation;
use App\Models\ScanAttempt;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Server-authoritative invitation verification and check-in.
 *
 * Duplicate prevention relies on the unique (event_id, invitation_id)
 * constraint, so concurrent requests can never create two successful
 * check-ins for the same invitation.
 */
class CheckInService
{
    /**
     * @return array{
     *     outcome: ScanOutcome,
     *     invitation: ?Invitation,
     *     attendance: ?AttendanceRecord,
     *     detail: string,
     * }
     */
    public function verify(Event $event, ?string $rawToken, User $attendant, string $method = 'qr', ?string $ip = null): array
    {
        $token = $this->normalizeToken($rawToken);
        $hint = $token !== null && $token !== '' ? substr($token, -6) : null;

        $invitation = null;

        if ($token !== null && $token !== '') {
            $invitation = Invitation::where('token_hash', hash('sha256', $token))->first();
        }

        if ($invitation === null) {
            $this->logAttempt($event, null, $attendant, ScanOutcome::Invalid, $hint, $ip);

            return ['outcome' => ScanOutcome::Invalid, 'invitation' => null, 'attendance' => null, 'detail' => 'checkin.messages.invalid'];
        }

        if ($invitation->event_id !== $event->id) {
            $this->logAttempt($event, $invitation, $attendant, ScanOutcome::WrongEvent, $hint, $ip);

            return ['outcome' => ScanOutcome::WrongEvent, 'invitation' => $invitation, 'attendance' => null, 'detail' => 'checkin.messages.wrong_event'];
        }

        if ($invitation->status !== 'issued') {
            $this->logAttempt($event, $invitation, $attendant, ScanOutcome::Revoked, $hint, $ip);

            return ['outcome' => ScanOutcome::Revoked, 'invitation' => $invitation, 'attendance' => null, 'detail' => 'checkin.messages.revoked'];
        }

        // Atomic check-in: transaction + unique constraint decide the winner.
        try {
            $attendance = DB::transaction(function () use ($event, $invitation, $attendant, $method) {
                $existing = AttendanceRecord::where('event_id', $event->id)
                    ->where('invitation_id', $invitation->id)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return ['record' => null, 'existing' => $existing];
                }

                $record = AttendanceRecord::create([
                    'event_id' => $event->id,
                    'invitation_id' => $invitation->id,
                    'attendant_id' => $attendant->id,
                    'guest_count' => $invitation->entitlement_count,
                    'method' => $method,
                    'checked_in_at' => now(),
                ]);

                return ['record' => $record, 'existing' => null];
            });
        } catch (UniqueConstraintViolationException) {
            $attendance = ['record' => null, 'existing' => AttendanceRecord::where('event_id', $event->id)
                ->where('invitation_id', $invitation->id)->first()];
        }

        if ($attendance['record'] === null) {
            $this->logAttempt($event, $invitation, $attendant, ScanOutcome::Duplicate, $hint, $ip);

            return [
                'outcome' => ScanOutcome::Duplicate,
                'invitation' => $invitation,
                'attendance' => $attendance['existing'],
                'detail' => 'checkin.messages.duplicate',
            ];
        }

        $this->logAttempt($event, $invitation, $attendant, ScanOutcome::Valid, $hint, $ip);

        AuditService::record($attendant, $event, 'checkin.success', $invitation, [
            'guest_count' => $attendance['record']->guest_count,
            'method' => $method,
        ], $ip);

        return [
            'outcome' => ScanOutcome::Valid,
            'invitation' => $invitation,
            'attendance' => $attendance['record'],
            'detail' => 'checkin.messages.valid',
        ];
    }

    /** Accepts a raw token or a full invitation URL. */
    private function normalizeToken(?string $input): ?string
    {
        $input = trim((string) $input);

        if ($input === '') {
            return null;
        }

        if (str_contains($input, '/i/')) {
            $parts = explode('/i/', $input);

            return trim(end($parts), '/');
        }

        return $input;
    }

    private function logAttempt(
        Event $event,
        ?Invitation $invitation,
        User $attendant,
        ScanOutcome $outcome,
        ?string $hint,
        ?string $ip,
    ): void {
        ScanAttempt::create([
            'event_id' => $event->id,
            'invitation_id' => $invitation?->id,
            'attendant_id' => $attendant->id,
            'outcome' => $outcome->value,
            'token_hint' => $hint,
            'ip_hash' => $ip ? hash_hmac('sha256', $ip, (string) config('app.key')) : null,
            'created_at' => now(),
        ]);
    }
}
