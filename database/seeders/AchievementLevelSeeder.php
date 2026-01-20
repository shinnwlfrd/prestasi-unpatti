<?php

namespace Database\Seeders;

use App\Models\AchievementLevel;
use Illuminate\Database\Seeder;

class AchievementLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            [
                'name' => 'Universitas',
                'description' => 'Prestasi tingkat universitas/kampus',
                'points' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Nasional',
                'description' => 'Prestasi tingkat nasional/Indonesia',
                'points' => 25,
                'is_active' => true,
            ],
            [
                'name' => 'Internasional',
                'description' => 'Prestasi tingkat internasional/dunia',
                'points' => 50,
                'is_active' => true,
            ],
        ];

        foreach ($levels as $level) {
            AchievementLevel::updateOrCreate(
                ['name' => $level['name']],
                $level
            );
        }
    }
}
