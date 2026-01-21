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
                'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                'color' => '#6366f1',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Nasional',
                'description' => 'Prestasi tingkat nasional/Indonesia',
                'points' => 25,
                'icon' => 'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9',
                'color' => '#f59e0b',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Internasional',
                'description' => 'Prestasi tingkat internasional/dunia',
                'points' => 50,
                'icon' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'color' => '#ef4444',
                'order' => 3,
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
