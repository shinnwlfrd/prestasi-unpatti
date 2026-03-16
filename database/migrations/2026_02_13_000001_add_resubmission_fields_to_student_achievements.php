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
            // Add resubmission tracking fields
            $table->boolean('is_resubmission')->default(false)->after('is_appeal');
            $table->integer('resubmission_count')->default(0)->after('is_resubmission');
            $table->timestamp('last_resubmitted_at')->nullable()->after('resubmission_count');
            $table->text('resubmission_reason')->nullable()->after('last_resubmitted_at');
            
            // Add index for performance
            $table->index('is_resubmission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->dropIndex(['is_resubmission']);
            $table->dropColumn([
                'is_resubmission',
                'resubmission_count',
                'last_resubmitted_at',
                'resubmission_reason'
            ]);
        });
    }
};
