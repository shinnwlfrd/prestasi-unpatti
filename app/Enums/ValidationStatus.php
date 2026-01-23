<?php

namespace App\Enums;

enum ValidationStatus: string
{
    case PENDING = 'Menunggu';
    case APPROVED = 'Disetujui';
    case REJECTED = 'Ditolak';
    case REVISION = 'Revisi';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Validasi',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::REVISION => 'Perlu Revisi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::APPROVED => 'green',
            self::REJECTED => 'red',
            self::REVISION => 'orange',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
            self::APPROVED => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
            self::REJECTED => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
            self::REVISION => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
        };
    }

    public static function options(): array
    {
        return array_column(self::cases(), 'value');
    }
}
