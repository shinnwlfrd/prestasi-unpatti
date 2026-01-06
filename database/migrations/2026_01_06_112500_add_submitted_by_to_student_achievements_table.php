<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->enum('submitted_by', ['student', 'validator'])->default('student')->after('validator_id');
        });
    }

    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->dropColumn('submitted_by');
        });
    }
};
