<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add indexes to improve dashboard query performance
     */
    public function up(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            // Index for validation_status queries (most common filter)
            $table->index('validation_status', 'idx_sa_validation_status');
            
            // Index for academic_period_id queries
            $table->index('academic_period_id', 'idx_sa_academic_period');
            
            // Index for submitted_at queries (used in trends and SLA checks)
            $table->index('submitted_at', 'idx_sa_submitted_at');
            
            // Index for level queries (distribution charts)
            $table->index('level', 'idx_sa_level');
            
            // Composite index for common query pattern
            $table->index(['academic_period_id', 'validation_status'], 'idx_sa_period_status');
        });

        Schema::table('students', function (Blueprint $table) {
            // Index for faculty queries (faculty comparison)
            $table->index('faculty', 'idx_students_faculty');
            
            // Index for program_study queries (program study ranking)
            $table->index('program_study', 'idx_students_program_study');
            
            // Composite index for common join pattern
            $table->index(['faculty', 'program_study'], 'idx_students_faculty_prodi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->dropIndex('idx_sa_validation_status');
            $table->dropIndex('idx_sa_academic_period');
            $table->dropIndex('idx_sa_submitted_at');
            $table->dropIndex('idx_sa_level');
            $table->dropIndex('idx_sa_period_status');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_students_faculty');
            $table->dropIndex('idx_students_program_study');
            $table->dropIndex('idx_students_faculty_prodi');
        });
    }
};
