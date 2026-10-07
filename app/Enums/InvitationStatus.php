<?php

namespace App\Enums;

enum InvitationStatus: string
{
    case Issued = 'issued';
    case Revoked = 'revoked';
    case Replaced = 'replaced';

    public function label(): string
    {
        return __('invitations.status.'.$this->value);
    }
}
