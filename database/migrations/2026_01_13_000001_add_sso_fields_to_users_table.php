<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Make password nullable for SSO-only users
            $table->string('password')->nullable()->change();

            // SSO Provider Info
            $table->string('provider', 50)->nullable()->after('password');
            $table->string('provider_id')->nullable()->after('provider');

            // SSO Tokens (encrypted)
            $table->text('provider_token')->nullable()->after('provider_id');
            $table->text('provider_refresh_token')->nullable()->after('provider_token');
            $table->timestamp('provider_token_expires_at')->nullable()->after('provider_refresh_token');
            $table->json('provider_data')->nullable()->after('provider_token_expires_at');

            // Linking & Auth tracking
            $table->timestamp('linked_at')->nullable()->after('provider_data');
            $table->string('primary_auth', 20)->default('local')->after('linked_at');
            $table->timestamp('last_login_at')->nullable()->after('primary_auth');
            $table->string('last_login_method', 20)->nullable()->after('last_login_at');
            $table->boolean('is_active')->default(true)->after('last_login_method');

            // Indexes
            $table->index(['provider', 'provider_id'], 'idx_provider');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_provider');
            $table->dropColumn([
                'provider', 'provider_id', 'provider_token', 'provider_refresh_token',
                'provider_token_expires_at', 'provider_data', 'linked_at',
                'primary_auth', 'last_login_at', 'last_login_method', 'is_active',
            ]);
        });
    }
};
