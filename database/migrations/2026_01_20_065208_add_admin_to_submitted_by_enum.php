<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we need to alter the enum to add 'admin'
        DB::statement("ALTER TABLE student_achievements MODIFY COLUMN submitted_by ENUM('student', 'validator', 'admin') DEFAULT 'student'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE student_achievements MODIFY COLUMN submitted_by ENUM('student', 'validator') DEFAULT 'student'");
    }
};
