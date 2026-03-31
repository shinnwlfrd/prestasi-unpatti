<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Rename role 'Validator' to 'Operator' in users table.
     */
    public function up(): void
    {
        // Step 1: Drop existing check constraint (PostgreSQL)
        try {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        } catch (\Exception $e) {
            Log::warning('Could not drop users_role_check constraint: ' . $e->getMessage());
        }

        // Step 2: Update all existing 'Validator' roles to 'Operator'
        $updated = DB::table('users')
            ->where('role', 'Validator')
            ->update(['role' => 'Operator']);

        Log::info("Migration: Updated {$updated} users from role 'Validator' to 'Operator'");

        // Step 3: Also update 'Mahasiswa' to 'Student' for consistency (if any exist from role switch)
        // This is a safety measure - Mahasiswa role was set during role switching
        // Keep as-is since it's handled differently

        // Step 4: Re-create check constraint with new role values
        try {
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('Admin', 'Operator', 'Pimpinan', 'Student', 'Mahasiswa'))");
        } catch (\Exception $e) {
            Log::warning('Could not add users_role_check constraint: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Drop constraint
        try {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        } catch (\Exception $e) {
            Log::warning('Could not drop users_role_check constraint: ' . $e->getMessage());
        }

        // Step 2: Revert 'Operator' back to 'Validator'
        $updated = DB::table('users')
            ->where('role', 'Operator')
            ->update(['role' => 'Validator']);

        Log::info("Migration rollback: Reverted {$updated} users from role 'Operator' to 'Validator'");

        // Step 3: Re-create check constraint with old role values
        try {
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('Admin', 'Validator', 'Pimpinan', 'Student', 'Mahasiswa'))");
        } catch (\Exception $e) {
            Log::warning('Could not add users_role_check constraint: ' . $e->getMessage());
        }
    }
};
