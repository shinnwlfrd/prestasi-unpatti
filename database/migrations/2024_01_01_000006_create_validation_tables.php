<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Validation Logs
        Schema::create('validation_logs', function (Blueprint $table) {
            $table->id('log_id'); // Changed from id() to match model
            $table->foreignId('sa_id')->constrained('student_achievements', 'sa_id')->onDelete('cascade');
            $table->foreignId('validator_id')->constrained('users')->onDelete('cascade');
            $table->string('old_status');
            $table->string('new_status');
            $table->text('notes')->nullable();
            $table->string('sk_document')->nullable();
            $table->string('validation_type')->nullable(); // manual, auto, appeal, etc
            
            // Multi-role system fields
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null'); // Who uploaded the achievement
            $table->enum('validation_level', ['faculty', 'university'])->nullable(); // Which level validated
            
            $table->json('metadata')->nullable();
            $table->timestamp('validated_at');
            $table->timestamps();
            
            $table->index(['sa_id', 'validated_at']);
            $table->index(['validator_id', 'validated_at']);
            $table->index('new_status');
            $table->index('validation_type');
            $table->index('uploaded_by');
            $table->index('validation_level');
        });

        // Validation Checklists
        Schema::create('validation_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_id')->constrained('student_achievements', 'sa_id')->onDelete('cascade');
            $table->foreignId('validator_id')->constrained('users')->onDelete('cascade');
            
            // Checklist Items
            $table->boolean('certificate_valid')->default(false);
            $table->boolean('event_date_valid')->default(false);
            $table->boolean('organizer_valid')->default(false);
            $table->boolean('level_appropriate')->default(false);
            $table->boolean('documents_complete')->default(false);
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique('sa_id');
            $table->index('validator_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_checklists');
        Schema::dropIfExists('validation_logs');
    }
};
