<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->boolean('is_appeal')->default(false)->after('validation_status');
            $table->text('appeal_reason')->nullable()->after('is_appeal');
            $table->text('publication_link')->nullable()->after('appeal_reason');
            $table->timestamp('appealed_at')->nullable()->after('publication_link');
        });
    }

    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->dropColumn(['is_appeal', 'appeal_reason', 'publication_link', 'appealed_at']);
        });
    }
};
