<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for students table.
 * Merged from:
 * - 2025_12_09_052509_create_students_table.php
 * - 2025_12_15_004908_add_photo_to_students_and_users_table.php (students part)
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->string('student_id')->primary(); // ID dari SIKAD
            $table->string('name');
            $table->string('faculty');
            $table->string('program_study');
            $table->integer('semester');
            $table->float('gpa')->nullable();
            $table->string('email')->unique();
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
