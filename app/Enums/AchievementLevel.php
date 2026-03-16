<?php

namespace App\Enums;

enum AchievementLevel: string
{
    case UNIVERSITY = 'Universitas';
    case NATIONAL = 'Nasional';
    case INTERNATIONAL = 'Internasional';

    public function label(): string
    {
        return $this->value;
    }

    public function points(): int
    {
        return match ($this) {
            self::UNIVERSITY => 10,
            self::NATIONAL => 20,
            self::INTERNATIONAL => 30,
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::UNIVERSITY => 'blue',
            self::NATIONAL => 'purple',
            self::INTERNATIONAL => 'red',
        };
    }

    public static function options(): array
    {
        return array_column(self::cases(), 'value');
    }
}
