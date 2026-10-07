<?php

namespace App\Policies;

use App\Enums\EventPermission;
use App\Models\Event;
use App\Models\User;

/**
 * Server-side authorization for every event-scoped resource.
 * Hiding buttons in Blade is never the control — this policy is.
 */
class EventPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        // Platform administrators do NOT automatically get access to private
        // event data; they manage the platform only.
        return null;
    }

    public function view(User $user, Event $event): bool
    {
        return $event->isMember($user);
    }

    public function update(User $user, Event $event): bool
    {
        return $event->organizer_id === $user->id;
    }

    public function delete(User $user, Event $event): bool
    {
        return $event->organizer_id === $user->id;
    }

    public function manageMembers(User $user, Event $event): bool
    {
        return $this->granted($user, $event, EventPermission::MembersManage)
            || $event->organizer_id === $user->id;
    }

    public function viewGuests(User $user, Event $event): bool
    {
        return $this->granted($user, $event, EventPermission::GuestsManage)
            || $this->granted($user, $event, EventPermission::GuestsView)
            || $this->granted($user, $event, EventPermission::InvitationsManage);
    }

    public function manageGuests(User $user, Event $event): bool
    {
        return $this->granted($user, $event, EventPermission::GuestsManage);
    }

    public function manageInvitations(User $user, Event $event): bool
    {
        return $this->granted($user, $event, EventPermission::InvitationsManage);
    }

    public function viewFinance(User $user, Event $event): bool
    {
        return $this->granted($user, $event, EventPermission::FinanceView)
            || $this->granted($user, $event, EventPermission::ContributionsRecord)
            || $this->granted($user, $event, EventPermission::ExpensesRecord)
            || $this->granted($user, $event, EventPermission::BudgetManage);
    }

    public function recordContributions(User $user, Event $event): bool
    {
        return $this->granted($user, $event, EventPermission::ContributionsRecord);
    }

    public function recordExpenses(User $user, Event $event): bool
    {
        return $this->granted($user, $event, EventPermission::ExpensesRecord);
    }

    public function manageBudget(User $user, Event $event): bool
    {
        return $this->granted($user, $event, EventPermission::BudgetManage);
    }

    public function manageVendors(User $user, Event $event): bool
    {
        return $this->granted($user, $event, EventPermission::VendorsManage);
    }

    public function checkIn(User $user, Event $event): bool
    {
        return $this->granted($user, $event, EventPermission::CheckinUse);
    }

    public function viewReports(User $user, Event $event): bool
    {
        return $this->granted($user, $event, EventPermission::ReportsView);
    }

    public function viewAudit(User $user, Event $event): bool
    {
        return $this->granted($user, $event, EventPermission::AuditView);
    }

    private function granted(User $user, Event $event, EventPermission $permission): bool
    {
        if ($event->organizer_id === $user->id) {
            return true;
        }

        $membership = $event->membershipFor($user);

        return $membership !== null && $membership->hasPermission($permission);
    }
}
