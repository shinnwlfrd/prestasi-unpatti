<?php

namespace Database\Seeders;

use App\Models\AchievementCategory;
use Illuminate\Database\Seeder;

class AchievementCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Akademik',
                'description' => 'Prestasi yang berkaitan dengan kegiatan akademik seperti olimpiade sains, kompetisi matematika, dll.',
                'is_active' => true,
            ],
            [
                'name' => 'Non-Akademik',
                'description' => 'Prestasi yang berkaitan dengan kegiatan non-akademik seperti olahraga, seni, budaya, dll.',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            AchievementCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
