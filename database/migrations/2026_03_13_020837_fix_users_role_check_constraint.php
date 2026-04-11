<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL, we need to drop the existing check constraint and recreate it
        // The constraint name is 'users_role_check' based on the error message
        if (config('database.default') === 'pgsql') {
            \DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            \DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('Admin', 'Validator', 'Pimpinan', 'Student'))");
        } else {
            Schema::table('users', function (Blueprint $table) {
                // For other databases like MySQL, we can use change()
                $table->string('role')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') === 'pgsql') {
            \DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            \DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('Admin', 'Validator', 'Pimpinan'))");
        }
    }
};
