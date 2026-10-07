<?php

namespace App\Models;

use Database\Factories\GuestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['event_id', 'name', 'phone', 'email', 'category', 'notes', 'created_by'])]
class Guest extends Model
{
    /** @use HasFactory<GuestFactory> */
    use HasFactory;

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function latestInvitation()
    {
        return $this->hasOne(Invitation::class)->latestOfMany();
    }
}
