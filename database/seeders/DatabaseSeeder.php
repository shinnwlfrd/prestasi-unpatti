<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Superadmin User
        $superadmin = User::create([
            'name' => 'Super Admin Demo',
            'email' => 'demo@demo.test',
            'password' => Hash::make('password'),
            'role' => 'Admin', // Legacy role field
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $superadmin = User::create([
            'name' => 'Super Admin Glori',
            'email' => '202351041@student.unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin', // Legacy role field
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Assign super_admin role in user_roles table
        UserRole::create([
            'user_id' => $superadmin->id,
            'role' => 'super_admin',
            'level' => 'university',
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $this->command->info('Superadmin created: demo@demo.test / password');
        $this->command->info('Superadmin created: 202351041@student.unpatti.ac.id / password');
    }
}
