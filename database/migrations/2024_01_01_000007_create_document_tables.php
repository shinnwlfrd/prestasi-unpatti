<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Achievement Documents
        Schema::create('achievement_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_id')->constrained('student_achievements', 'sa_id')->onDelete('cascade');
            $table->string('document_type'); // sertifikat, foto_dokumentasi, sk_resmi, etc
            $table->string('file_path')->nullable();
            $table->string('file_name');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('external_link')->nullable();
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'revision'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('revision_notes')->nullable();
            $table->timestamps();
            
            $table->index(['sa_id', 'status']);
            $table->index('document_type');
            $table->index('verified_by');
        });

        // Document Revisions
        Schema::create('document_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('achievement_documents')->onDelete('cascade');
            $table->string('action'); // uploaded, replaced, approved, rejected, etc
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('external_link')->nullable();
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['document_id', 'created_at']);
            $table->index('performed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_revisions');
        Schema::dropIfExists('achievement_documents');
    }
};
