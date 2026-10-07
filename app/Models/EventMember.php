<?php

namespace App\Models;

use App\Enums\EventPermission;
use App\Enums\MemberRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['event_id', 'user_id', 'role', 'status', 'invited_by'])]
class EventMember extends Model
{
    protected function casts(): array
    {
        return [
            'role' => MemberRole::class,
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function permissions()
    {
        return $this->hasMany(PermissionGrant::class);
    }

    public function isOwner(): bool
    {
        return $this->role === MemberRole::Owner
            || $this->event?->organizer_id === $this->user_id;
    }

    /**
     * Least-privilege check: owners hold everything, attendants only check-in,
     * committee members hold exactly their granted permissions.
     */
    public function hasPermission(EventPermission|string $permission): bool
    {
        $key = $permission instanceof EventPermission ? $permission->value : $permission;

        if ($this->isOwner()) {
            return true;
        }

        if ($this->status !== 'active') {
            return false;
        }

        if ($this->role === MemberRole::Attendant) {
            return $key === EventPermission::CheckinUse->value;
        }

        $granted = $this->relationLoaded('permissions')
            ? $this->permissions->pluck('permission')
            : $this->permissions()->pluck('permission');

        return $granted->contains($key);
    }
}
