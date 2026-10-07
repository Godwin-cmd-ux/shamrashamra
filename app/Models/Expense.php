<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'event_id', 'budget_category_id', 'vendor_id', 'description', 'amount_minor',
    'currency', 'incurred_at', 'payment_status', 'approval_status',
    'approved_by', 'receipt_path', 'recorded_by', 'reverses_id',
    'reversed_at', 'reversed_by', 'reversal_reason',
])]
class Expense extends Model
{
    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
            'incurred_at' => 'date',
            'reversed_at' => 'datetime',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function budgetCategory()
    {
        return $this->belongsTo(BudgetCategory::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
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
