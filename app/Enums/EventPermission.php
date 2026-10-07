<?php

namespace App\Enums;

/**
 * Granular, event-scoped permissions granted to committee members.
 * Owners implicitly hold every permission; attendants only get check-in access.
 */
enum EventPermission: string
{
    case GuestsManage = 'guests.manage';
    case GuestsView = 'guests.view';
    case InvitationsManage = 'invitations.manage';
    case FinanceView = 'finance.view';
    case ContributionsRecord = 'contributions.record';
    case ExpensesRecord = 'expenses.record';
    case BudgetManage = 'budget.manage';
    case VendorsManage = 'vendors.manage';
    case ReportsView = 'reports.view';
    case MembersManage = 'members.manage';
    case AuditView = 'audit.view';
    case CheckinUse = 'checkin.use';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return __('events.permissions.'.$this->value);
    }

    public function description(): string
    {
        return __('events.permission_descriptions.'.$this->value);
    }

    /** Permissions selectable when inviting a committee member. */
    /** @return array<string> */
    public static function grantable(): array
    {
        return [
            self::GuestsManage->value,
            self::InvitationsManage->value,
            self::FinanceView->value,
            self::ContributionsRecord->value,
            self::ExpensesRecord->value,
            self::BudgetManage->value,
            self::VendorsManage->value,
            self::ReportsView->value,
            self::MembersManage->value,
            self::AuditView->value,
            self::CheckinUse->value,
        ];
    }
}
