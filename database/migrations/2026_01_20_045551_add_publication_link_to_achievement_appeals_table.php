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
        Schema::table('achievement_appeals', function (Blueprint $table) {
            $table->text('publication_link')->nullable()->after('appeal_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('achievement_appeals', function (Blueprint $table) {
            $table->dropColumn('publication_link');
        });
    }
};
