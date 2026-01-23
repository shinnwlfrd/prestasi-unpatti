<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for validation_logs table.
 * Merged from:
 * - 2025_12_09_052559_create_validation_logs_table.php
 * - 2026_01_06_101223_add_sk_document_to_validation_logs_table.php
 * - 2026_01_06_111658_add_metadata_to_validation_logs_table.php
 */
return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('validation_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->foreignId('sa_id');
            $table->foreignId('validator_id');
            $table->string('old_status');
            $table->string('new_status');
            $table->text('notes')->nullable();
            $table->string('sk_document')->nullable();
            $table->string('validation_type')->default('SK Resmi');
            $table->json('metadata')->nullable();
            $table->dateTime('validated_at');
            $table->timestamps();

            // Foreign keys
            $table->foreign('sa_id')
                ->references('sa_id')
                ->on('student_achievements')
                ->onDelete('cascade');

            $table->foreign('validator_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_logs');
    }
};
