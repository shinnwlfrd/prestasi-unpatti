<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up()
    {
        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id('sa_id');
            $table->foreignId('student_id'); // FK ke students.student_id
            $table->foreignId('achievement_id'); // FK ke achievements.id
            $table->string('event_name');
            $table->Enum('level', ['Universitas', 'Nasional', 'Internasional']); // 'Universitas', 'Nasional', 'Internasional'
            $table->string('organizer');
            $table->date('event_date');
            $table->text('description')->nullable();
            $table->string('certificate_path'); // Path ke file bukti
            $table->Enum('validation_status', ['Menunggu', 'Disetujui', 'Ditolak'])->default('Pending'); // Pending/Approved/Rejected
            $table->foreignId('validator_id')->nullable(); // FK ke users.id

            $table->timestamps();

            $table->foreign('student_id')
                ->references('student_id')
                ->on('students')
                ->onDelete('cascade');

            $table->foreign('achievement_id')
                ->references('id')
                ->on('achievements')
                ->onDelete('cascade');

            $table->foreign('validator_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_achievements');
    }
};
