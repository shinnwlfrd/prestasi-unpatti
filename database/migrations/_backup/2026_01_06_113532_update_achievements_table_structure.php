<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Untuk SQLite, kita perlu recreate table
        if (DB::getDriverName() === 'sqlite') {
            // Backup data
            $achievements = DB::table('achievements')->get();
            
            // Drop table
            Schema::dropIfExists('achievements');
            
            // Recreate dengan enum
            Schema::create('achievements', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('category', ['Akademik', 'Non-Akademik']);
                $table->timestamps();
            });
            
            // Restore data dengan normalisasi
            foreach ($achievements as $ach) {
                $category = str_contains(strtolower($ach->category), 'akademik') ? 'Akademik' : 'Non-Akademik';
                DB::table('achievements')->insert([
                    'id' => $ach->id,
                    'name' => $ach->name,
                    'category' => $category,
                    'created_at' => $ach->created_at,
                    'updated_at' => $ach->updated_at,
                ]);
            }
        } else {
            Schema::table('achievements', function (Blueprint $table) {
                $table->enum('category', ['Akademik', 'Non-Akademik'])->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            $table->string('category')->change();
        });
    }
};
