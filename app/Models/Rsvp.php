<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'event_id', 'invitation_id', 'status', 'guest_count',
    'note', 'source', 'ip_hash', 'responded_at',
])]
class Rsvp extends Model
{
    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
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
}
