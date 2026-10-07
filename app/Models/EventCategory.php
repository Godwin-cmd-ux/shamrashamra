<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'name_en', 'name_sw', 'sort_order', 'is_active'])]
class EventCategory extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function name(): string
    {
        return app()->getLocale() === 'sw' ? $this->name_sw : $this->name_en;
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'category_id');
    }
}
