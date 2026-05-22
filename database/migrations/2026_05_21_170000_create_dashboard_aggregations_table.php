<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_aggregations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('academic_period_id')->nullable()->index();
            $table->string('validation_status')->index();
            $table->uuid('faculty_id')->nullable()->index();
            $table->uuid('department_id')->nullable()->index();
            $table->uuid('program_study_id')->nullable()->index();
            $table->string('level')->nullable()->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->integer('total_count')->default(0);
            $table->timestamps();

            // Foreign keys if necessary, but we might keep it simple for speed and fallback.
            // Let's add constraints to be robust.
            $table->foreign('academic_period_id')->references('id')->on('academic_periods')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('achievement_categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_aggregations');
    }
};
