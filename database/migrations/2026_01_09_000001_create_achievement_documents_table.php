<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('achievement_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_id')->constrained('student_achievements', 'sa_id')->cascadeOnDelete();
            $table->enum('document_type', [
                'sk_resmi',
                'sertifikat',
                'foto_dokumentasi',
                'surat_keterangan',
                'link_publikasi',
            ]);
            $table->string('file_path')->nullable();
            $table->string('file_name');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('external_link')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_documents');
    }
};
