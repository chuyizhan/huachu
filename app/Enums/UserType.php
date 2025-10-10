<?php

namespace App\Enums;

enum UserType: int
{
    case REGULAR = 1;
    case PREMIUM = 2;

    /**
     * Get the label for the user type.
     */
    public function label(): string
    {
        return match($this) {
            self::REGULAR => 'Regular',
            self::PREMIUM => 'Premium',
        };
    }

    /**
     * Get all available types.
     */
    public static function toArray(): array
    {
        return [
            self::REGULAR->value => self::REGULAR->label(),
            self::PREMIUM->value => self::PREMIUM->label(),
        ];
    }
}

