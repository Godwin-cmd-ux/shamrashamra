<?php

namespace App\Models;

use App\Enums\InvitationStatus;
use Database\Factories\InvitationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'event_id', 'guest_id', 'label', 'entitlement_count', 'status',
    'token_hash', 'token_encrypted', 'issued_by',
    'revoked_at', 'revoked_by', 'replaced_by_id',
    'rsvp_status', 'rsvp_guest_count', 'rsvp_responded_at', 'rsvp_note',
])]
class Invitation extends Model
{
    /** @use HasFactory<InvitationFactory> */
    use HasFactory;

    protected $hidden = ['token_hash', 'token_encrypted'];

    protected static function booted(): void
    {
        static::creating(function (Invitation $invitation) {
            $invitation->public_id ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'token_encrypted' => 'encrypted',
            'entitlement_count' => 'integer',
            'revoked_at' => 'datetime',
            'rsvp_responded_at' => 'datetime',
            'rsvp_guest_count' => 'integer',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function attendanceRecord()
    {
        return $this->hasOne(AttendanceRecord::class);
    }

    public function rsvpHistory()
    {
        return $this->hasMany(Rsvp::class)->latest('responded_at');
    }

    public function scopeActive($query)
    {
        return $query->where('status', InvitationStatus::Issued->value);
    }

    public function isIssued(): bool
    {
        return $this->status === InvitationStatus::Issued->value;
    }

    public function displayLabel(): string
    {
        return $this->label
            ?? $this->guest?->name
            ?? __('invitations.unnamed');
    }

    public function isUsed(): bool
    {
        return $this->attendanceRecord()->exists();
    }
}
