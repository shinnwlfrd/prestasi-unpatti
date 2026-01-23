<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('achievement_levels', function (Blueprint $table) {
            $table->string('icon')->nullable()->after('name'); // SVG path or icon class
            $table->string('color')->default('#6366f1')->after('icon'); // Hex color or Tailwind class
            $table->integer('order')->default(0)->after('color'); // Display order
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('achievement_levels', function (Blueprint $table) {
            $table->dropColumn(['icon', 'color', 'order']);
        });
    }
};
