<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->boolean('sk_required')->default(true)->after('validation_status');
            $table->enum('sk_waiver_reason', [
                'tingkat_universitas',
                'sk_dalam_proses',
                'dokumen_alternatif',
                'lainnya'
            ])->nullable()->after('sk_required');
            $table->text('sk_waiver_notes')->nullable()->after('sk_waiver_reason');
            $table->string('alternative_document_path')->nullable()->after('sk_waiver_notes');
        });
    }

    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->dropColumn([
                'sk_required',
                'sk_waiver_reason',
                'sk_waiver_notes',
                'alternative_document_path'
            ]);
        });
    }
};
