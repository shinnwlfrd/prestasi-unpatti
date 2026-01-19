<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('validation_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->foreignId('sa_id'); // FK ke student_achievements.sa_id
            $table->foreignId('validator_id'); // FK ke users.id
            $table->string('old_status');
            $table->string('new_status');
            $table->text('notes')->nullable();
            $table->dateTime('validated_at');

            $table->timestamps();

            $table->foreign('sa_id')
                  ->references('sa_id')
                  ->on('student_achievements')
                  ->onDelete('cascade');

            $table->foreign('validator_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('validation_logs');
    }
};