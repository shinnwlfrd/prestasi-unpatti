<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('integration_health_checks', function (Blueprint $table) {
            $table->id();
            $table->string('service', 50);
            $table->string('status', 20);
            $table->string('base_url')->nullable();
            $table->text('message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();

            $table->index(['service', 'checked_at']);
            $table->index(['status', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_health_checks');
    }
};
