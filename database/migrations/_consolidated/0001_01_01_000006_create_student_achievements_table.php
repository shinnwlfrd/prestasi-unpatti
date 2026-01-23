<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for student_achievements table.
 * Merged from:
 * - 2025_12_09_052539_create_student_achievements_table.php
 * - 2026_01_06_111717_add_certificate_to_student_achievements_table.php
 * - 2026_01_06_112500_add_submitted_by_to_student_achievements_table.php
 */
return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id('sa_id');
            $table->string('student_id');
            $table->foreignId('achievement_id');
            $table->string('event_name');
            $table->enum('level', ['Universitas', 'Nasional', 'Internasional']);
            $table->string('organizer');
            $table->date('event_date');
            $table->text('description')->nullable();
            $table->string('certificate')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->enum('validation_status', ['Menunggu', 'Disetujui', 'Ditolak'])->default('Menunggu');
            $table->foreignId('validator_id')->nullable();
            $table->enum('submitted_by', ['student', 'validator'])->default('student');
            $table->timestamps();

            // Foreign keys
            $table->foreign('student_id')
                ->references('student_id')
                ->on('students')
                ->onDelete('cascade');

            $table->foreign('achievement_id')
                ->references('id')
                ->on('achievements')
                ->onDelete('cascade');

            $table->foreign('validator_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_achievements');
    }
};
