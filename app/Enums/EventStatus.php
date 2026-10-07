<?php

namespace App\Enums;

enum EventStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Archived = 'archived';

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Valid state transitions (single source of truth for lifecycle). */
    /** @return array<string> */
    public static function transitions(): array
    {
        return [
            self::Draft->value => [self::Published->value, self::Cancelled->value],
            self::Published->value => [self::Completed->value, self::Cancelled->value],
            self::Completed->value => [self::Archived->value],
            self::Cancelled->value => [self::Archived->value],
            self::Archived->value => [],
        ];
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target->value, self::transitions()[$this->value] ?? [], true);
    }

    public function label(): string
    {
        return __('events.status.'.$this->value);
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-slate-100 text-slate-700 ring-slate-200',
            self::Published => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            self::Completed => 'bg-sky-50 text-sky-700 ring-sky-200',
            self::Cancelled => 'bg-rose-50 text-rose-700 ring-rose-200',
            self::Archived => 'bg-stone-100 text-stone-600 ring-stone-200',
        };
    }
}
