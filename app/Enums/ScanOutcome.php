<?php

namespace App\Enums;

enum ScanOutcome: string
{
    case Valid = 'valid';
    case Invalid = 'invalid';
    case Revoked = 'revoked';
    case WrongEvent = 'wrong_event';
    case Duplicate = 'duplicate';

    public function label(): string
    {
        return __('checkin.outcome.'.$this->value);
    }
}
