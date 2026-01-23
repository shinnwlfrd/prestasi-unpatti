<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        // Untuk SQLite, recreate table dengan struktur baru
        if (DB::getDriverName() === 'sqlite') {
            // Backup data
            $achievements = DB::table('achievements')->get();

            // Drop table
            Schema::dropIfExists('achievements');

            // Recreate dengan struktur baru (hanya 2 kategori)
            Schema::create('achievements', function (Blueprint $table) {
                $table->id();
                $table->enum('category', ['Akademik', 'Non-Akademik']);
                $table->timestamps();
            });
        } else {
            // Untuk database lain, drop kolom name
            Schema::table('achievements', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('achievements', function (Blueprint $table) {
                $table->string('name')->after('id');
            });
        }
    }
};
