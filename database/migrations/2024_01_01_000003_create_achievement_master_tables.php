<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Achievement Categories
        Schema::create('achievement_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon', 500)->nullable(); // Increased size for icon
            $table->string('color')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('order');
        });

        // Achievement Levels
        Schema::create('achievement_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('points')->default(0);
            $table->string('icon', 500)->nullable(); // Increased size for icon
            $table->string('color')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('points');
            $table->index('order');
        });

        // Achievements (Templates)
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('achievement_categories')->onDelete('cascade');
            $table->string('name')->nullable(); // Made nullable for seeder compatibility
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['category_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('achievement_levels');
        Schema::dropIfExists('achievement_categories');
    }
};
