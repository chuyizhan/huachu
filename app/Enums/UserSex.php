<?php

namespace App\Enums;

enum UserSex: int
{
    case MALE = 1;
    case FEMALE = 2;
    case OTHER = 3;

    /**
     * Get the label for the user sex.
     */
    public function label(): string
    {
        return match($this) {
            self::MALE => 'Male',
            self::FEMALE => 'Female',
            self::OTHER => 'Other',
        };
    }

    /**
     * Get all available options.
     */
    public static function toArray(): array
    {
        return [
            self::MALE->value => self::MALE->label(),
            self::FEMALE->value => self::FEMALE->label(),
            self::OTHER->value => self::OTHER->label(),
        ];
    }
}

