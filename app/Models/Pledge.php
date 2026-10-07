<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'event_id', 'contributor_name', 'contributor_contact', 'amount_minor',
    'currency', 'due_date', 'status', 'notes', 'recorded_by',
])]
class Pledge extends Model
{
    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
            'due_date' => 'date',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function contributions()
    {
        return $this->hasMany(Contribution::class);
    }

    public function receivedMinor(): int
    {
        return (int) $this->contributions()->whereNull('reversed_at')->sum('amount_minor');
    }

    public function outstandingMinor(): int
    {
        return max(0, $this->amount_minor - $this->receivedMinor());
    }

    public function refreshStatus(): void
    {
        $received = $this->receivedMinor();

        $this->status = match (true) {
            $received <= 0 => 'open',
            $received >= $this->amount_minor => 'settled',
            default => 'partially_paid',
        };

        $this->save();
    }
}
