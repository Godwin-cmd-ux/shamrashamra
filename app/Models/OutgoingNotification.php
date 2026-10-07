<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'event_id', 'invitation_id', 'channel', 'recipient',
    'subject', 'status', 'provider', 'provider_reference',
    'error', 'sent_at', 'created_by',
])]
class OutgoingNotification extends Model
{
    protected $table = 'notification_outbox';

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
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

    public function attempts()
    {
        return $this->hasMany(NotificationAttempt::class, 'notification_id');
    }
}
