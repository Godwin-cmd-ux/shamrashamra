<?php

namespace App\Models;

use App\Enums\EventStatus;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'title', 'category_id', 'starts_at', 'timezone', 'venue_name', 'venue_address',
    'map_link', 'description', 'host_names', 'dress_code', 'image_path',
    'rsvp_deadline', 'guest_capacity', 'settings', 'status',
])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected $guarded = ['organizer_id'];

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            $event->public_id ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'rsvp_deadline' => 'datetime',
            'published_at' => 'datetime',
            'settings' => 'array',
            'guest_capacity' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /** Short filename-safe slug for exports. */
    public function slugForFile(): string
    {
        return Str::slug($this->title) ?: $this->public_id;
    }

    public function statusEnum(): EventStatus
    {
        return EventStatus::from($this->status);
    }

    /**
     * Apply a lifecycle transition or throw (never trust an arbitrary status string).
     */
    public function transitionTo(EventStatus $target): void
    {
        if (! $this->statusEnum()->canTransitionTo($target)) {
            throw new \InvalidArgumentException(
                "Event status cannot move from [{$this->status}] to [{$target->value}]."
            );
        }

        $this->status = $target->value;

        if ($target === EventStatus::Published && ! $this->published_at) {
            $this->published_at = now();
        }

        $this->save();
    }

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }

    public function memberships()
    {
        return $this->hasMany(EventMember::class);
    }

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function budgetCategories()
    {
        return $this->hasMany(BudgetCategory::class);
    }

    public function pledges()
    {
        return $this->hasMany(Pledge::class);
    }

    public function contributions()
    {
        return $this->hasMany(Contribution::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function outgoingNotifications()
    {
        return $this->hasMany(OutgoingNotification::class);
    }

    /** Membership row for a given user (null when not a member). */
    public function membershipFor(?User $user): ?EventMember
    {
        if (! $user) {
            return null;
        }

        if ($this->organizer_id === $user->id) {
            // Synthetic owner membership so authorization has one code path.
            $membership = $this->memberships()
                ->where('user_id', $user->id)
                ->first();

            if ($membership) {
                return $membership;
            }

            $membership = new EventMember([
                'event_id' => $this->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'status' => 'active',
            ]);
            $membership->setRelation('permissions', collect());

            return $membership;
        }

        return $this->memberships()
            ->with('permissions')
            ->where('user_id', $user->id)
            ->first();
    }

    public function isMember(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->organizer_id === $user->id) {
            return true;
        }

        return $this->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    /** @return array<string> */
    public function guestCategories(): array
    {
        $defaults = ['family', 'friends', 'colleagues', 'vip', 'committee', 'children'];

        return $this->settings['guest_categories'] ?? $defaults;
    }

    public function requireExpenseApproval(): bool
    {
        return (bool) ($this->settings['require_expense_approval'] ?? false);
    }
}
