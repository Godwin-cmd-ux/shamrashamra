<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'event_id', 'invitation_id', 'attendant_id',
    'guest_count', 'method', 'checked_in_at',
])]
class AttendanceRecord extends Model
{
    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
            'guest_count' => 'integer',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
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
