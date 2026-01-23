<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Users table with all fields
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['Admin', 'Validator'])->default('Validator');
            $table->string('faculty')->nullable();
            $table->boolean('is_active')->default(true);
            
            // SSO fields
            $table->string('sso_id')->nullable()->unique();
            $table->string('sso_provider')->nullable();
            $table->text('sso_token')->nullable();
            $table->timestamp('sso_last_login')->nullable();
            
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['role', 'is_active']);
            $table->index('faculty');
            $table->index('sso_id');
        });

        // Password reset tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Auth logs
        Schema::create('auth_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('email');
            $table->enum('role', ['Admin', 'Validator', 'Student']);
            $table->enum('action', ['login', 'logout', 'failed_login']);
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');
            
            $table->index(['user_id', 'created_at']);
            $table->index(['email', 'action']);
        });

        // Validator profiles
        Schema::create('validator_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nip')->nullable()->unique(); // Made nullable for seeder compatibility
            $table->string('name')->nullable(); // For seeder compatibility
            $table->string('email')->nullable(); // For seeder compatibility
            $table->string('faculty')->nullable(); // Made nullable
            $table->string('department')->nullable();
            $table->string('phone')->nullable();
            $table->text('specialization')->nullable();
            $table->string('sk_number')->nullable();
            $table->date('sk_date')->nullable();
            $table->string('sk_file')->nullable();
            $table->timestamps();
            
            $table->index('faculty');
            $table->index('nip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validator_profiles');
        Schema::dropIfExists('auth_logs');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
