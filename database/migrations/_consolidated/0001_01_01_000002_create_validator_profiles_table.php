<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for validator_profiles table.
 * (No changes needed - kept as is)
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('validator_profiles', function (Blueprint $table) {
            $table->foreignId('user_id')->primary();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('department')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validator_profiles');
    }
};
