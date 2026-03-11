<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\UserRole;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Upgrade all validators (operators) to university level (Super Validator)
        UserRole::where('role', 'operator')
            ->where('level', '!=', 'university')
            ->update(['level' => 'university']);

        // Set pimpinan levels based on their positions
        UserRole::where('role', 'pimpinan')->each(function($role) {
            $level = match ($role->position) {
                'rektor', 'wakil_rektor_1', 'wakil_rektor_2', 'wakil_rektor_3', 'kepala_biro_kemahasiswaan', 'super_admin' => 'university',
                'dekan', 'direktur_pps' => 'faculty',
                'ketua_jurusan' => 'department',
                'kaprodi' => 'program_study',
                default => $role->level ?? 'faculty',
            };
            $role->update(['level' => $level]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy way to reverse this as we don't know the previous levels/positions
        // But if needed, we could set them back to faculty/dekan but it won't be accurate
    }
};
