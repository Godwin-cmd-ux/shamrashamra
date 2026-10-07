<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Central audit writer. Never pass raw tokens, passwords, or payment secrets.
 */
class AuditService
{
    public static function record(
        ?User $actor,
        ?Event $event,
        string $action,
        ?Model $subject = null,
        array $metadata = [],
        ?string $ip = null,
    ): AuditLog {
        return AuditLog::create([
            'event_id' => $event?->id,
            'actor_id' => $actor?->id,
            'action' => $action,
            'subject_type' => $subject !== null ? $subject::class : null,
            'subject_id' => $subject?->getKey() !== null ? (string) $subject->getKey() : null,
            'metadata' => $metadata ?: null,
            'ip_hash' => $ip ? hash_hmac('sha256', $ip, (string) config('app.key')) : null,
            'created_at' => now(),
        ]);
    }
}
