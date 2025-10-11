<?php

namespace App\Enums;

enum UserStatus: int
{
    case ACTIVE = 1;
    case SUSPENDED = 2;
    case BANNED = 3;

    /**
     * Get the label for the user status.
     */
    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
            self::BANNED => 'Banned',
        };
    }

    /**
     * Check if the status is active.
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Get the color for display purposes.
     */
    public function color(): string
    {
        return match($this) {
            self::ACTIVE => 'green',
            self::SUSPENDED => 'yellow',
            self::BANNED => 'red',
        };
    }

    /**
     * Get all available statuses.
     */
    public static function toArray(): array
    {
        return [
            self::ACTIVE->value => self::ACTIVE->label(),
            self::SUSPENDED->value => self::SUSPENDED->label(),
            self::BANNED->value => self::BANNED->label(),
        ];
    }
}

