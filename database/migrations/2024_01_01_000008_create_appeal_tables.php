<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievement_appeals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_id')->constrained('student_achievements', 'sa_id')->onDelete('cascade');
            $table->text('reason');
            $table->string('publication_link')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            $table->index(['sa_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_appeals');
    }
};
