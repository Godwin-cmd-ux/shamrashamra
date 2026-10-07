<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'event_id', 'pledge_id', 'amount_minor', 'currency', 'received_at',
    'method', 'reference', 'note', 'recorded_by', 'reverses_id',
    'reversed_at', 'reversed_by', 'reversal_reason',
])]
class Contribution extends Model
{
    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
            'received_at' => 'date',
            'reversed_at' => 'datetime',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function pledge()
    {
        return $this->belongsTo(Pledge::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function reversal()
    {
        return $this->hasOne(self::class, 'reverses_id');
    }

    public function isReversal(): bool
    {
        return $this->reverses_id !== null;
    }

    public function isReversed(): bool
    {
        return $this->reversed_at !== null;
    }
}
