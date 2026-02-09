<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            // For PostgreSQL, we need to drop the constraint and recreate it
            // Drop existing constraint first
            DB::statement("ALTER TABLE student_achievements DROP CONSTRAINT IF EXISTS student_achievements_validation_status_check");

            // Change the column type to varchar to remove enum constraint
            DB::statement("ALTER TABLE student_achievements ALTER COLUMN validation_status TYPE VARCHAR(50)");

            // Add check constraint with new statuses
            DB::statement("
                ALTER TABLE student_achievements 
                ADD CONSTRAINT student_achievements_validation_status_check 
                CHECK (validation_status IN (
                    'draft',
                    'submitted',
                    'faculty_review',
                    'faculty_approved',
                    'faculty_rejected',
                    'faculty_revision',
                    'university_review',
                    'university_approved',
                    'university_rejected',
                    'appeal_submitted',
                    'appeal_approved',
                    'appeal_rejected',
                    'Menunggu',
                    'Disetujui',
                    'Ditolak',
                    'Revisi'
                ))
            ");
        } else {
            // For SQLite and other drivers, use Schema builder if possible
            Schema::table('student_achievements', function (Blueprint $table) {
                $table->string('validation_status', 50)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            // Drop the new constraint
            DB::statement("ALTER TABLE student_achievements DROP CONSTRAINT IF EXISTS student_achievements_validation_status_check");

            // Recreate old enum constraint
            DB::statement("
                ALTER TABLE student_achievements 
                ADD CONSTRAINT student_achievements_validation_status_check 
                CHECK (validation_status IN ('Menunggu', 'Disetujui', 'Ditolak', 'Revisi'))
            ");
        } else {
            Schema::table('student_achievements', function (Blueprint $table) {
                $table->string('validation_status', 50)->change();
            });
        }
    }
};
