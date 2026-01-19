<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validation_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_id')->constrained('student_achievements', 'sa_id')->cascadeOnDelete();
            $table->foreignId('validator_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('nama_peserta_valid')->default(false);
            $table->text('nama_peserta_notes')->nullable();
            $table->boolean('nama_lomba_valid')->default(false);
            $table->text('nama_lomba_notes')->nullable();
            $table->boolean('tanggal_valid')->default(false);
            $table->text('tanggal_notes')->nullable();
            $table->boolean('peringkat_valid')->default(false);
            $table->text('peringkat_notes')->nullable();
            $table->boolean('penyelenggara_valid')->default(false);
            $table->text('penyelenggara_notes')->nullable();
            $table->boolean('keaslian_dokumen_valid')->default(false);
            $table->text('keaslian_dokumen_notes')->nullable();
            $table->text('overall_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_checklists');
    }
};
