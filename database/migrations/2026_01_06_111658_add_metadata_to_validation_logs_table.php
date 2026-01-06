<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('validation_logs', function (Blueprint $table) {
            $table->string('validation_type')->default('SK Resmi')->after('sk_document'); // SK Resmi atau Dokumen Internal
            $table->json('metadata')->nullable()->after('validation_type'); // IP, User Agent, etc
            $table->timestamp('created_at')->useCurrent()->change();
            $table->timestamp('updated_at')->useCurrent()->change();
        });
    }

    public function down(): void
    {
        Schema::table('validation_logs', function (Blueprint $table) {
            $table->dropColumn(['validation_type', 'metadata']);
        });
    }
};
