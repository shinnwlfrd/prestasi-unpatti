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
            $table->string('password')->nullable();
            $table->enum('role', ['Admin', 'Validator'])->default('Validator');
            $table->string('faculty')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('photo')->nullable();
            
            // OAuth/SSO Provider fields
            $table->string('provider')->nullable(); // google, microsoft, etc
            $table->string('provider_id')->nullable();
            $table->text('provider_token')->nullable();
            $table->text('provider_refresh_token')->nullable();
            $table->timestamp('provider_token_expires_at')->nullable();
            $table->json('provider_data')->nullable();
            $table->timestamp('linked_at')->nullable();
            $table->string('primary_auth')->nullable(); // local, sso
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_method')->nullable();
            
            // Legacy SSO fields (for backward compatibility)
            $table->string('sso_id')->nullable()->unique();
            $table->string('sso_provider')->nullable();
            $table->text('sso_token')->nullable();
            $table->timestamp('sso_last_login')->nullable();
            
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['role', 'is_active']);
            $table->index('faculty');
            $table->index('sso_id');
            $table->index(['provider', 'provider_id']);
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
            $table->string('email')->nullable();
            $table->enum('role', ['Admin', 'Validator', 'Student'])->nullable();
            $table->string('action'); // login, logout, failed_login, sso_link, register, password_reset
            $table->string('method')->nullable(); // local, google, microsoft, etc
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at');
            
            $table->index(['user_id', 'created_at']);
            $table->index(['email', 'action']);
            $table->index('action');
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
