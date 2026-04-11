<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Create students table with SIAKAD integration.
     * Students are only created when they submit their first achievement.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            // Primary key - NIM
            $table->string('student_id')->primary();
            
            // SIAKAD UUID
            $table->uuid('id_mahasiswa')->nullable()->index();
            
            // Basic info
            $table->string('name');
            $table->string('email')->unique();
            
            // Photo
            $table->string('foto_url')->nullable();
            
            // Academic info
            $table->decimal('ipk', 3, 2)->nullable();
            $table->year('angkatan')->nullable();
            $table->decimal('gpa', 3, 2)->nullable(); // Kept for backward compatibility
            
            // Fakultas (SIAKAD)
            $table->uuid('faculty_id')->nullable();
            $table->string('faculty')->nullable();
            
            // Jurusan (SIAKAD)
            $table->uuid('department_id')->nullable();
            $table->string('department')->nullable();
            
            // Program Studi (SIAKAD)
            $table->uuid('program_study_id')->nullable();
            $table->string('program_study')->nullable();
            
            // Legacy photo field (kept for backward compatibility)
            $table->string('photo')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes for performance
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
