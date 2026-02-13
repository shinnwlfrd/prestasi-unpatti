<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create student_achievements table with two-stage validation support.
     */
    public function up(): void
    {
        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id('sa_id');
            $table->string('student_id');
            $table->foreignId('achievement_id')->constrained('achievements')->onDelete('cascade');
            $table->foreignId('academic_period_id')->nullable()->constrained('academic_periods')->onDelete('set null');
            
            // Achievement Details
            $table->string('event_name');
            $table->string('level'); // Internasional, Nasional, Universitas
            $table->string('organizer');
            $table->date('event_date');
            $table->string('ranking')->nullable();
            $table->text('description')->nullable();
            $table->string('certificate')->nullable();
            $table->string('publication_link')->nullable();
            
            // Two-Stage Validation System
            $table->enum('validation_status', ['Menunggu', 'Disetujui', 'Ditolak', 'Revisi'])->default('Menunggu');
            $table->string('validation_stage', 20)->default('faculty');
            $table->string('current_stage', 20)->default('faculty');
            
            // Faculty validation tracking
            $table->unsignedBigInteger('faculty_validator_id')->nullable();
            $table->timestamp('faculty_validated_at')->nullable();
            $table->text('faculty_notes')->nullable();
            
            // University validation tracking
            $table->unsignedBigInteger('university_validator_id')->nullable();
            $table->timestamp('university_validated_at')->nullable();
            $table->text('university_notes')->nullable();
            
            // Legacy validator field (for backward compatibility)
            $table->foreignId('validator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('validated_at')->nullable();
            
            // Submission Info
            $table->enum('submitted_by', ['student', 'validator', 'admin'])->default('student');
            $table->timestamp('submitted_at')->nullable();
            
            // Appeal Fields
            $table->boolean('is_appeal')->default(false);
            $table->text('appeal_reason')->nullable();
            $table->timestamp('appealed_at')->nullable();
            
            // SK Waiver Fields
            $table->boolean('sk_required')->default(true);
            $table->string('sk_waiver_reason')->nullable();
            $table->text('sk_waiver_notes')->nullable();
            $table->string('alternative_document_path')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
            $table->foreign('faculty_validator_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('university_validator_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for performance
            $table->index(['student_id', 'validation_status']);
            $table->index(['validation_status', 'submitted_at']);
            $table->index(['validator_id', 'validation_status']);
            $table->index('academic_period_id');
            $table->index('is_appeal');
            $table->index('level');
            $table->index('validation_stage');
            $table->index('current_stage');
            $table->index('faculty_validator_id');
            $table->index('university_validator_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_achievements');
    }
};
