<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'event_id', 'invitation_id', 'attendant_id',
    'outcome', 'token_hint', 'ip_hash', 'created_at',
])]
class ScanAttempt extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }

    public function attendant()
    {
        return $this->belongsTo(User::class, 'attendant_id');
    }
}
