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
        // 1. Drop the table if exists
        Schema::dropIfExists('achievement_appeals');

        // 2. Drop columns from student_achievements
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->dropColumn(['is_appeal', 'appeal_reason', 'appealed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->boolean('is_appeal')->default(false);
            $table->text('appeal_reason')->nullable();
            $table->timestamp('appealed_at')->nullable();
        });

        Schema::create('achievement_appeals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_id')->constrained('student_achievements', 'sa_id')->onDelete('cascade');
            $table->string('student_id');
            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
            $table->text('reason');
            $table->text('appeal_reason')->nullable();
            $table->string('publication_link')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }
};
