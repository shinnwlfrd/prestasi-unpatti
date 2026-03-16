<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'Admin';
    case VALIDATOR = 'Validator';
    case STUDENT = 'Student';

    public function label(): string
    {
        return $this->value;
    }

    public function can(string $permission): bool
    {
        return match ($this) {
            self::ADMIN => true,
            self::VALIDATOR => in_array($permission, ['validate', 'view', 'upload_document']),
            self::STUDENT => in_array($permission, ['submit', 'view_own', 'appeal']),
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::ADMIN => 'admin.dashboard',
            self::VALIDATOR => 'validator.dashboard',
            self::STUDENT => 'student.dashboard',
        };
    }
}
