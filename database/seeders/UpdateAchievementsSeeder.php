<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\AchievementCategory;
use Illuminate\Database\Seeder;

class UpdateAchievementsSeeder extends Seeder
{
    public function run(): void
    {
        // Get all active categories
        $categories = AchievementCategory::active()->get();

        foreach ($categories as $category) {
            // Create or update achievement for each category
            Achievement::updateOrCreate(
                ['category_id' => $category->id],
                ['category_id' => $category->id]
            );
        }

        $this->command->info('Achievement records created for all active categories!');
        $this->command->info('Total achievements: ' . Achievement::count());
    }
}
