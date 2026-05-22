<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('academic_periods', function (Blueprint $table) {
            $table->datetime('submission_deadline')->nullable()->after('end_date');
            $table->datetime('validation_deadline')->nullable()->after('submission_deadline');
        });
    }

    public function down(): void
    {
        Schema::table('academic_periods', function (Blueprint $table) {
            $table->dropColumn(['submission_deadline', 'validation_deadline']);
        });
    }
};
