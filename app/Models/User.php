<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'phone', 'locale', 'timezone'])]
#[Hidden(['password', 'remember_token', 'token_encrypted'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->platform_role === 'admin';
    }

    public function isSuspended(): bool
    {
        return ($this->status ?? 'active') !== 'active';
    }

    /** Events this user organizes directly. */
    public function organizedEvents()
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    /** Event memberships where this user is a member. */
    public function memberships()
    {
        return $this->hasMany(EventMember::class);
    }

    /**
     * Events the user may access (as organizer or active member).
     *
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Event>
     */
    public function authorizedEvents()
    {
        return Event::query()
            ->where('organizer_id', $this->id)
            ->orWhereHas('memberships', function ($q) {
                $q->where('user_id', $this->id)->where('status', 'active');
            });
    }
}
