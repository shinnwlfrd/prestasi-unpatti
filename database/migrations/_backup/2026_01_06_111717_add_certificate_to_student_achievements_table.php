<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->string('certificate')->nullable()->after('description');
            $table->timestamp('submitted_at')->nullable()->after('certificate');
        });
    }

    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->dropColumn(['certificate', 'submitted_at']);
        });
    }
};
