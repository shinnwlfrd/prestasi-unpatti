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
        // Add SIGAP fields to students table
        Schema::table('students', function (Blueprint $table) {
            $table->string('program_study_id')->nullable()->after('program_study');
            $table->string('program_study_code')->nullable()->after('program_study_id');
            $table->string('faculty_id')->nullable()->after('faculty');
            $table->string('department_id')->nullable()->after('faculty_id');
            $table->string('department')->nullable()->after('department_id');
        });

        // Add SIGAP fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('faculty_id')->nullable()->after('faculty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['program_study_id', 'program_study_code', 'faculty_id', 'department_id', 'department']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('faculty_id');
        });
    }
};

