<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['scope', 'event_id', 'category', 'name', 'style', 'config', 'is_active'])]
class InvitationTemplate extends Model
{
    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function scopePlatform($query)
    {
        return $query->where('scope', 'platform')->where('is_active', true);
    }
}
