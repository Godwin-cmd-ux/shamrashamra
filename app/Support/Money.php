<?php

namespace App\Support;

/** Money helpers. Amounts are integer minor units (×100) — never floats. */
class Money
{
    public static function toMinor(float|string|int $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    public static function toMajor(int $minor): float
    {
        return $minor / 100;
    }

    public static function format(int $minor, string $currency = 'TZS'): string
    {
        $formatted = number_format($minor / 100, 2);

        // Strip trailing .00 for whole amounts (TZS has no practical cents display).
        if (str_ends_with($formatted, '.00')) {
            $formatted = substr($formatted, 0, -3);
        }

        return match ($currency) {
            'TZS' => 'TZS '.$formatted,
            'USD' => '$'.$formatted,
            default => $currency.' '.$formatted,
        };
    }

    /** Parse a user-entered amount string into minor units (integer-safe). */
    public static function parse(string $input): int
    {
        $input = preg_replace('/[^0-9.\-]/', '', $input) ?? '';

        if ($input === '' || $input === '-') {
            throw new \InvalidArgumentException('Invalid amount.');
        }

        return self::toMinor($input);
    }
}
