<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Multi-role system for users
     */
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Role type
            $table->enum('role', [
                'super_admin',      // Full system access
                'admin',            // University admin
                'operator',         // Faculty operator
                'pimpinan',         // Leadership (read-only)
                'mahasiswa'         // Student
            ]);
            
            // Hierarchical level for operators & pimpinan
            $table->enum('level', [
                'university',       // Rektor, Wakil Rektor, Pimpinan Kemahasiswaan
                'faculty',          // Dekan, Operator Fakultas
                'department',       // Ketua Jurusan
                'program_study'     // Kepala Program Studi
            ])->nullable();
            
            // SIGAP hierarchy IDs
            $table->string('faculty_id')->nullable();
            $table->string('faculty_name')->nullable();
            $table->string('department_id')->nullable();
            $table->string('department_name')->nullable();
            $table->string('program_study_id')->nullable();
            $table->string('program_study_name')->nullable();
            
            // Position for pimpinan role
            $table->string('position')->nullable(); // 'rektor', 'wakil_rektor', 'dekan', 'ketua_jurusan', 'kaprodi', 'pimpinan_kemahasiswaan'
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'role', 'is_active']);
            $table->index(['role', 'level']);
            $table->index('faculty_id');
            $table->index('department_id');
            $table->index('program_study_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
