<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['event_id', 'name', 'planned_amount_minor', 'currency'])]
class BudgetCategory extends Model
{
    protected function casts(): array
    {
        return [
            'planned_amount_minor' => 'integer',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /** Total active (non-reversed) expenses charged to this budget line. */
    public function spentMinor(): int
    {
        return (int) $this->expenses()
            ->whereNull('reversed_at')
            ->sum('amount_minor');
    }
}
