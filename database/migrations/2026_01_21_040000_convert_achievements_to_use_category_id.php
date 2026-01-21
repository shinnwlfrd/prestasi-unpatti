<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Step 1: Add category_id column (nullable first)
        Schema::table('achievements', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')->constrained('achievement_categories')->onDelete('cascade');
        });

        // Step 2: Migrate existing data
        // Map 'Akademik' to category_id 1, 'Non-Akademik' to category_id 2
        DB::table('achievements')->where('category', 'Akademik')->update(['category_id' => 1]);
        DB::table('achievements')->where('category', 'Non-Akademik')->update(['category_id' => 2]);

        // Step 3: Make category_id required and drop old category column
        Schema::table('achievements', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('achievements', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        // Restore old structure
        Schema::table('achievements', function (Blueprint $table) {
            $table->enum('category', ['Akademik', 'Non-Akademik'])->after('id');
        });

        // Migrate data back
        DB::table('achievements')->where('category_id', 1)->update(['category' => 'Akademik']);
        DB::table('achievements')->where('category_id', 2)->update(['category' => 'Non-Akademik']);

        Schema::table('achievements', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
