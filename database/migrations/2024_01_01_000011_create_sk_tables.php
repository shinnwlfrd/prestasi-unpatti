<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SK Documents Table
        Schema::create('sk_documents', function (Blueprint $table) {
            $table->id();
            $table->string('sk_number')->unique();
            $table->string('title');
            $table->string('file_path')->nullable();
            $table->string('external_link')->nullable();
            $table->date('issued_date');
            $table->string('issued_by');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('sk_number');
            $table->index('issued_date');
            $table->index('created_by');
        });

        // SK Assignments Table
        Schema::create('sk_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sk_id')->constrained('sk_documents')->onDelete('cascade');
            $table->foreignId('sa_id')->constrained('student_achievements', 'sa_id')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('assigned_at');
            $table->enum('assignment_type', ['individual', 'batch'])->default('individual');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['sk_id', 'sa_id']);
            $table->index('sk_id');
            $table->index('sa_id');
            $table->index('assigned_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sk_assignments');
        Schema::dropIfExists('sk_documents');
    }
};
