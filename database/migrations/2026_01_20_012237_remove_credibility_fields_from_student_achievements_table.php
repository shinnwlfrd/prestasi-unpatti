<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            // Drop foreign key first if exists
            if (Schema::hasColumn('student_achievements', 'current_approver_id')) {
                $table->dropForeign(['current_approver_id']);
            }
            
            // Drop credibility-related columns
            $columns = ['credibility_score', 'requires_extra_review', 'approval_level', 'current_approver_id'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('student_achievements', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->decimal('credibility_score', 5, 2)->default(0)->after('validation_status');
            $table->boolean('requires_extra_review')->default(false)->after('credibility_score');
            $table->string('approval_level')->default('standard')->after('requires_extra_review');
            $table->foreignId('current_approver_id')->nullable()->after('approval_level')->constrained('users')->nullOnDelete();
        });
    }
};
