<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            // MySQL syntax
            DB::statement("ALTER TABLE student_achievements MODIFY COLUMN submitted_by ENUM('student', 'validator', 'admin') DEFAULT 'student'");
        } elseif ($driver === 'pgsql') {
            // PostgreSQL syntax - need to alter the type
            // First, drop existing constraint if it exists
            DB::statement('ALTER TABLE student_achievements DROP CONSTRAINT IF EXISTS student_achievements_submitted_by_check');

            DB::statement('ALTER TABLE student_achievements ALTER COLUMN submitted_by DROP DEFAULT');
            DB::statement('ALTER TABLE student_achievements ALTER COLUMN submitted_by TYPE VARCHAR(20)');
            DB::statement("ALTER TABLE student_achievements ALTER COLUMN submitted_by SET DEFAULT 'student'");

            // Add check constraint for enum-like behavior with new values
            DB::statement("ALTER TABLE student_achievements ADD CONSTRAINT student_achievements_submitted_by_check CHECK (submitted_by IN ('student', 'validator', 'admin'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            // Revert back to original enum values
            DB::statement("ALTER TABLE student_achievements MODIFY COLUMN submitted_by ENUM('student', 'validator') DEFAULT 'student'");
        } elseif ($driver === 'pgsql') {
            // PostgreSQL - drop constraint and revert
            DB::statement('ALTER TABLE student_achievements DROP CONSTRAINT IF EXISTS student_achievements_submitted_by_check');
            DB::statement('ALTER TABLE student_achievements ALTER COLUMN submitted_by DROP DEFAULT');
            DB::statement('ALTER TABLE student_achievements ALTER COLUMN submitted_by TYPE VARCHAR(20)');
            DB::statement("ALTER TABLE student_achievements ALTER COLUMN submitted_by SET DEFAULT 'student'");

            // Add back old constraint
            DB::statement("ALTER TABLE student_achievements ADD CONSTRAINT student_achievements_submitted_by_check CHECK (submitted_by IN ('student', 'validator'))");
        }
    }
};
