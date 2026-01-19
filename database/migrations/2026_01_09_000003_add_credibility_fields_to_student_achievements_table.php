<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            if (!Schema::hasColumn('student_achievements', 'credibility_score')) {
                $table->decimal('credibility_score', 5, 2)->default(0)->after('validation_status');
            }
            if (!Schema::hasColumn('student_achievements', 'requires_extra_review')) {
                $table->boolean('requires_extra_review')->default(false)->after('credibility_score');
            }
            if (!Schema::hasColumn('student_achievements', 'approval_level')) {
                $table->string('approval_level')->default('standard')->after('requires_extra_review');
            }
            if (!Schema::hasColumn('student_achievements', 'current_approver_id')) {
                $table->foreignId('current_approver_id')->nullable()->after('approval_level')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('student_achievements', 'ranking')) {
                $table->string('ranking')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->dropForeign(['current_approver_id']);
            $table->dropColumn([
                'credibility_score',
                'requires_extra_review',
                'approval_level',
                'current_approver_id',
                'ranking'
            ]);
        });
    }
};
