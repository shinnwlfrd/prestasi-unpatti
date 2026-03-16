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
        // Add soft deletes to all main tables
        $tables = [
            'users',
            'students',
            'achievements',
            'achievement_categories',
            'achievement_levels',
            'academic_periods',
            'student_achievements',
            'validation_logs',
            'achievement_documents',
            'document_revisions',
            'achievement_appeals',
            'validation_checklists',
            'sk_documents',
            'sk_assignments',
            'sikad_credentials',
            'validator_profiles',
            'user_roles',
            'auth_logs',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'users',
            'students',
            'achievements',
            'achievement_categories',
            'achievement_levels',
            'academic_periods',
            'student_achievements',
            'validation_logs',
            'achievement_documents',
            'document_revisions',
            'achievement_appeals',
            'validation_checklists',
            'sk_documents',
            'sk_assignments',
            'sikad_credentials',
            'validator_profiles',
            'user_roles',
            'auth_logs',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
