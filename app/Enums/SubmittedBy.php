<?php

namespace App\Enums;

enum SubmittedBy: string
{
    case STUDENT = 'student';
    case VALIDATOR = 'validator';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::STUDENT => 'Mahasiswa',
            self::VALIDATOR => 'Validator',
            self::ADMIN => 'Admin',
        };
    }
}
