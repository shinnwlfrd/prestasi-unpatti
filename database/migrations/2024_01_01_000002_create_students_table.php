<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->string('student_id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('faculty')->nullable();
            $table->string('program')->nullable(); // program studi
            $table->string('program_study')->nullable(); // alias for backward compatibility
            $table->integer('semester')->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
            
            $table->index('faculty');
            $table->index('program');
            $table->index(['faculty', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
