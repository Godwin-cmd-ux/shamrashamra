<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['notification_id', 'attempt', 'status', 'detail', 'created_at'])]
class NotificationAttempt extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function notification()
    {
        return $this->belongsTo(OutgoingNotification::class, 'notification_id');
    }
}
