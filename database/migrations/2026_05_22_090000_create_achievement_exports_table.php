<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('achievement_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_for_role_id')->nullable()->constrained('user_roles')->nullOnDelete();
            $table->string('context')->default('achievements');
            $table->string('source')->default('dashboard');
            $table->string('format', 10);
            $table->string('status', 20)->default('queued');
            $table->json('filters')->nullable();
            $table->json('scope_snapshot')->nullable();
            $table->string('disk', 50)->default('local');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('row_count')->default(0);
            $table->unsignedBigInteger('file_size')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'context', 'created_at']);
            $table->index(['requested_for_role_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_exports');
    }
};
