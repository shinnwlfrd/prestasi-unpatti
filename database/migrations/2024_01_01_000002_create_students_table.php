<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Create students table with optimized structure.
     * Includes SIGAP integration fields and removes unused columns.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            // Primary key
            $table->string('student_id')->primary();
            
            // Basic info
            $table->string('name');
            $table->string('email')->unique();
            
            // Academic info (text fields for display)
            $table->string('faculty')->nullable();
            $table->string('program_study')->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            
            // SIGAP integration fields (IDs for filtering/relations)
            $table->string('faculty_id')->nullable();
            $table->string('department_id')->nullable();
            $table->string('department')->nullable();
            $table->string('program_study_id')->nullable();
            $table->integer('angkatan')->nullable();
            
            // Media
            $table->string('photo')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes for performance
            $table->index('faculty');
            $table->index('faculty_id');
            $table->index('department_id');
            $table->index('program_study_id');
            $table->index('angkatan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
