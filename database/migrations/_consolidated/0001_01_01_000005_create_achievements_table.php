<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for achievements table.
 * Merged from:
 * - 2025_12_09_052534_create_achievements_table.php
 * - 2026_01_06_113532_update_achievements_table_structure.php
 * - 2026_01_06_114452_simplify_achievements_table.php
 */
return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['Akademik', 'Non-Akademik']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
