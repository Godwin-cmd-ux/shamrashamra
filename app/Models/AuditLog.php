<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Append-only audit trail. Updates and deletes from application code are blocked.
 */
#[Fillable(['event_id', 'actor_id', 'action', 'subject_type', 'subject_id', 'metadata', 'ip_hash', 'created_at'])]
class AuditLog extends Model
{
    public $timestamps = false;

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \RuntimeException('Audit logs are append-only.');
        });

        static::deleting(function () {
            throw new \RuntimeException('Audit logs are append-only.');
        });
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
