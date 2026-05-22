<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->json('student_snapshot')->nullable()->after('academic_period_id');
        });

        Schema::table('validation_logs', function (Blueprint $table) {
            $table->foreignId('validator_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('validation_logs', function (Blueprint $table) {
            $table->foreignId('validator_id')->nullable(false)->change();
        });

        Schema::table('student_achievements', function (Blueprint $table) {
            $table->dropColumn('student_snapshot');
        });
    }
};
