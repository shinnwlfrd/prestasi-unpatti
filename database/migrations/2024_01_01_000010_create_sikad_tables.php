<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sikad_credentials', function (Blueprint $table) {
            $table->string('student_id')->primary();
            $table->string('username')->nullable();
            $table->string('password_hash');
            $table->timestamp('last_sync')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
            
            $table->index('is_active');
            $table->index('last_sync');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sikad_credentials');
    }
};
