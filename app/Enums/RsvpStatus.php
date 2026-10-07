<?php

namespace App\Enums;

enum RsvpStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Declined = 'declined';
    case Expired = 'expired';

    public function label(): string
    {
        return __('invitations.rsvp.'.$this->value);
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-50 text-amber-700 ring-amber-200',
            self::Confirmed => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            self::Declined => 'bg-rose-50 text-rose-700 ring-rose-200',
            self::Expired => 'bg-stone-100 text-stone-600 ring-stone-200',
        };
    }
}
