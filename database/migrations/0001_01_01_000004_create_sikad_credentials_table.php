<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for sikad_credentials table.
 * (No changes needed - kept as is)
 */
return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('sikad_credentials', function (Blueprint $table) {
            $table->string('student_id')->primary();
            $table->string('password_hash');
            $table->dateTime('last_login')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('student_id')
                ->references('student_id')
                ->on('students')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sikad_credentials');
    }
};
