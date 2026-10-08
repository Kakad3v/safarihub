<?php

namespace App\Enums;

enum CancellationPolicy: string
{
    case Flexible = 'flexible';
    case Moderate = 'moderate';
    case Strict = 'strict';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function description(): string
    {
        return match ($this) {
            self::Flexible => 'Full refund up to 7 days before.',
            self::Moderate => 'Full refund up to 14 days, half up to 7 days.',
            self::Strict => 'Half refund up to 14 days, none after.',
        };
    }
}
