<?php

namespace App\Enums;

enum MemberRole: string
{
    case Owner = 'owner';
    case Committee = 'committee';
    case Attendant = 'attendant';

    public function label(): string
    {
        return __('events.roles.'.$this->value);
    }
}
