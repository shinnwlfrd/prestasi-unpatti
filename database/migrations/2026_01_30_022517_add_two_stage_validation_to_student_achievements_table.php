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
        Schema::table('student_achievements', function (Blueprint $table) {
            // Add validation stage tracking
            $table->string('validation_stage', 20)->default('faculty')->after('validation_status');
            $table->string('current_stage', 20)->default('faculty')->after('validation_stage');
            
            // Faculty validation tracking
            $table->unsignedBigInteger('faculty_validator_id')->nullable()->after('current_stage');
            $table->timestamp('faculty_validated_at')->nullable()->after('faculty_validator_id');
            $table->text('faculty_notes')->nullable()->after('faculty_validated_at');
            
            // University validation tracking
            $table->unsignedBigInteger('university_validator_id')->nullable()->after('faculty_notes');
            $table->timestamp('university_validated_at')->nullable()->after('university_validator_id');
            $table->text('university_notes')->nullable()->after('university_validated_at');
            
            // Add foreign keys
            $table->foreign('faculty_validator_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('university_validator_id')->references('id')->on('users')->onDelete('set null');
            
            // Add indexes for performance
            $table->index('validation_stage');
            $table->index('current_stage');
            $table->index('faculty_validator_id');
            $table->index('university_validator_id');
        });
        
        // Update validation_logs table
        Schema::table('validation_logs', function (Blueprint $table) {
            $table->string('validation_stage', 20)->default('faculty')->after('validation_type');
            $table->string('stage_action', 50)->nullable()->after('validation_stage');
            $table->boolean('is_stage_transition')->default(false)->after('stage_action');
            
            // Add indexes
            $table->index('validation_stage');
            $table->index('is_stage_transition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['faculty_validator_id']);
            $table->dropForeign(['university_validator_id']);
            
            // Drop indexes
            $table->dropIndex(['validation_stage']);
            $table->dropIndex(['current_stage']);
            $table->dropIndex(['faculty_validator_id']);
            $table->dropIndex(['university_validator_id']);
            
            // Drop columns
            $table->dropColumn([
                'validation_stage',
                'current_stage',
                'faculty_validator_id',
                'faculty_validated_at',
                'faculty_notes',
                'university_validator_id',
                'university_validated_at',
                'university_notes',
            ]);
        });
        
        Schema::table('validation_logs', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['validation_stage']);
            $table->dropIndex(['is_stage_transition']);
            
            // Drop columns
            $table->dropColumn([
                'validation_stage',
                'stage_action',
                'is_stage_transition',
            ]);
        });
    }
};
