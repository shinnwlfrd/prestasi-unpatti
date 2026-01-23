<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_period_id')->nullable()->after('achievement_id');
            $table->foreign('academic_period_id')->references('id')->on('academic_periods')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->dropForeign(['academic_period_id']);
            $table->dropColumn('academic_period_id');
        });
    }
};
