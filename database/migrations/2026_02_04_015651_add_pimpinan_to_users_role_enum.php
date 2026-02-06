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
        // For PostgreSQL, we need to alter the enum type
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(50)");
        
        // Add back the check constraint with Pimpinan included
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('Admin', 'Validator', 'Pimpinan'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('Admin', 'Validator'))");
    }
};
