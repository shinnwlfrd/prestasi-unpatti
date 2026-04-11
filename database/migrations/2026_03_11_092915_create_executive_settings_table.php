<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('executive_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('value');
            $table->string('unit')->nullable();
            $table->string('input_type')->default('number');
            $table->string('category')->default('executive_panel');
            $table->timestamps();
        });

        // Insert default settings
        DB::table('executive_settings')->insert([
            [
                'key' => 'min_national_achievements',
                'label' => 'Target Prestasi Nasional/Internasional',
                'description' => 'Target jumlah minimum prestasi tingkat Nasional/Internasional per unit per periode.',
                'value' => '1',
                'unit' => 'prestasi',
                'input_type' => 'number',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'key' => 'growth_drop_threshold',
                'label' => 'Ambang Batas Penurunan Pertumbuhan',
                'description' => 'Persentase penurunan kuantitas prestasi dibandingkan periode sebelumnya yang dianggap berisiko tinggi (danger). Masukkan nilai positif (misal: 20 mendeteksi penurunan > 20%).',
                'value' => '20',
                'unit' => '%',
                'input_type' => 'number',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'key' => 'sla_validation_days',
                'label' => 'Batas Waktu Validasi (SLA)',
                'description' => 'Batas waktu (hari) proses validasi dari submit hingga selesai divalidasi oleh Fakultas/Universitas sebelum dianggap terlambat.',
                'value' => '7',
                'unit' => 'hari',
                'input_type' => 'number',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'key' => 'unit_participation_target',
                'label' => 'Target Partisipasi Unit Aktif',
                'description' => 'Persentase minimum unit aktif berprestasi terhadap total unit di tingkat tersebut.',
                'value' => '30',
                'unit' => '%',
                'input_type' => 'number',
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('executive_settings');
    }
};
