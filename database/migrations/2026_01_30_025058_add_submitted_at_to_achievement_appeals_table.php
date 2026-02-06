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
            // Add submitted_at column if it doesn't exist
            if (!Schema::hasColumn('achievement_appeals', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('additional_notes');
            }
        });

        // Update existing records: set submitted_at = created_at for existing appeals
        DB::statement('UPDATE achievement_appeals SET submitted_at = created_at WHERE submitted_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('achievement_appeals', function (Blueprint $table) {
            if (Schema::hasColumn('achievement_appeals', 'submitted_at')) {
                $table->dropColumn('submitted_at');
            }
        });
    }
};
