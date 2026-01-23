<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up()
    {
        Schema::create('sikad_credentials', function (Blueprint $table) {
            $table->string('student_id')->primary(); // Relasi satu-satu ke students
            $table->string('password_hash'); // Bisa juga diganti token jika pakai SSO
            $table->dateTime('last_login')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('student_id')
                ->references('student_id')
                ->on('students')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sikad_credentials');
    }
};
