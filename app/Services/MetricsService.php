<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;

/**
 * All dashboard numbers are derived from the database.
 * Pledges and received contributions are always reported separately.
 */
class MetricsService
{
    /** @return array<string, int> */
    public function forUser(User $user): array
    {
        $events = Event::query()
            ->where('organizer_id', $user->id)
            ->orWhereHas('memberships', fn ($q) => $q->where('user_id', $user->id)->where('status', 'active'));

        $activeEvents = (clone $events)->where('status', 'published')->count();
        $upcoming = (clone $events)->where('status', 'published')->where('starts_at', '>=', now())->count();

        $eventIdQuery = (clone $events)->select('id');
        $allEvents = (clone $events)->get();

        $invited = (clone $events)->whereHas('invitations', fn ($q) => $q->where('status', 'issued'))->count();

        $rsvpConfirmed = 0;
        $rsvpDeclined = 0;
        $totalPledgedMinor = 0;
        $totalContributedMinor = 0;
        $outstandingMinor = 0;
        $plannedBudgetMinor = 0;
        $expensesMinor = 0;
        $checkedIn = 0;
        $invitationsIssued = 0;
        $attendanceGuests = 0;

        foreach ($allEvents as $event) {
            $invitations = $event->invitations()->where('status', 'issued');

            $rsvpConfirmed += (clone $invitations)->where('rsvp_status', 'confirmed')->count();
            $rsvpDeclined += (clone $invitations)->where('rsvp_status', 'declined')->count();
            $issued = (clone $invitations)->count();
            $invitationsIssued += $issued;

            $checkedIn += $event->attendanceRecords()->count();
            $attendanceGuests += (int) $event->attendanceRecords()->sum('guest_count');

            $eventPledged = (int) $event->pledges()->where('status', '!=', 'cancelled')->sum('amount_minor');
            $eventContributed = (int) $event->contributions()->whereNull('reversed_at')->sum('amount_minor');

            $totalPledgedMinor += $eventPledged;
            $totalContributedMinor += $eventContributed;
            // Outstanding is the aggregate commitment minus everything received
            // for the event, whether or not a payment was linked to a pledge.
            $outstandingMinor += max(0, $eventPledged - $eventContributed);

            $plannedBudgetMinor += (int) $event->budgetCategories()->sum('planned_amount_minor');
            $expensesMinor += (int) $event->expenses()->whereNull('reversed_at')->sum('amount_minor');
        }

        return [
            'active_events' => $activeEvents,
            'upcoming_events' => $upcoming,
            'invited_guests' => $invited,
            'rsvp_confirmed' => $rsvpConfirmed,
            'rsvp_declined' => $rsvpDeclined,
            'attendance_count' => $attendanceGuests,
            'checkin_records' => $checkedIn,
            'invitations_issued' => $invitationsIssued,
            'total_pledged_minor' => $totalPledgedMinor,
            'total_contributed_minor' => $totalContributedMinor,
            'outstanding_minor' => $outstandingMinor,
            'planned_budget_minor' => $plannedBudgetMinor,
            'expenses_minor' => $expensesMinor,
            'remaining_budget_minor' => $plannedBudgetMinor - $expensesMinor,
        ];
    }

    /** @return array<string, int|string> */
    public function forEvent(Event $event): array
    {
        $issued = $event->invitations()->where('status', 'issued');

        $invitationsIssued = (clone $issued)->count();
        $confirmed = (clone $issued)->where('rsvp_status', 'confirmed')->count();
        $declined = (clone $issued)->where('rsvp_status', 'declined')->count();
        $pending = (clone $issued)->where('rsvp_status', 'pending')->count();
        $checkedIn = $event->attendanceRecords()->count();
        $attendanceGuests = (int) $event->attendanceRecords()->sum('guest_count');
        $pledged = (int) $event->pledges()->where('status', '!=', 'cancelled')->sum('amount_minor');
        $contributed = (int) $event->contributions()->whereNull('reversed_at')->sum('amount_minor');
        $planned = (int) $event->budgetCategories()->sum('planned_amount_minor');
        $spent = (int) $event->expenses()->whereNull('reversed_at')->sum('amount_minor');

        return [
            'guests' => $event->guests()->count(),
            'invitations_issued' => $invitationsIssued,
            'rsvp_confirmed' => $confirmed,
            'rsvp_declined' => $declined,
            'rsvp_pending' => $pending,
            'checkin_records' => $checkedIn,
            'attendance_guests' => $attendanceGuests,
            'checkin_progress' => $invitationsIssued > 0 ? (int) round($checkedIn / $invitationsIssued * 100) : 0,
            'pledged_minor' => $pledged,
            'contributed_minor' => $contributed,
            'planned_budget_minor' => $planned,
            'expenses_minor' => $spent,
            'remaining_budget_minor' => $planned - $spent,
        ];
    }
}
