<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'event_id', 'name', 'type', 'contact_name', 'phone', 'email',
    'services', 'agreed_amount_minor', 'deposit_amount_minor',
    'contract_path', 'status', 'notes',
])]
class Vendor extends Model
{
    protected function casts(): array
    {
        return [
            'agreed_amount_minor' => 'integer',
            'deposit_amount_minor' => 'integer',
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
}
