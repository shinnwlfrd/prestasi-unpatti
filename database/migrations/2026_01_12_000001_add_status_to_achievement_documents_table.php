<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('achievement_documents', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('external_link');
            $table->text('revision_notes')->nullable()->after('status');
            $table->foreignId('verified_by')->nullable()->after('revision_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
        });
    }

    public function down(): void
    {
        Schema::table('achievement_documents', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['status', 'revision_notes', 'verified_by', 'verified_at']);
        });
    }
};
