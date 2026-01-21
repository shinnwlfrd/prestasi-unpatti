<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicPeriod;
use App\Models\StudentAchievement;

class AcademicPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🎓 Creating Academic Periods...');

        // Delete old periods (set academic_period_id to null first)
        $this->command->info('🗑️  Cleaning old data...');
        \App\Models\StudentAchievement::whereNotNull('academic_period_id')->update(['academic_period_id' => null]);
        \App\Models\AcademicPeriod::query()->delete();

        // Create 3 academic periods
        $periods = [
            [
                'name' => 'Semester Ganjil 2024/2025',
                'code' => '20241',
                'year' => '2024/2025',
                'semester' => 'Ganjil',
                'start_date' => '2024-09-01',
                'end_date' => '2025-01-31',
                'is_active' => true,
            ],
            [
                'name' => 'Semester Genap 2024/2025',
                'code' => '20242',
                'year' => '2024/2025',
                'semester' => 'Genap',
                'start_date' => '2025-02-01',
                'end_date' => '2025-07-31',
                'is_active' => false,
            ],
            [
                'name' => 'Semester Ganjil 2025/2026',
                'code' => '20251',
                'year' => '2025/2026',
                'semester' => 'Ganjil',
                'start_date' => '2025-09-01',
                'end_date' => '2026-01-31',
                'is_active' => false,
            ],
        ];

        $createdPeriods = [];
        foreach ($periods as $periodData) {
            $period = AcademicPeriod::create($periodData);
            $createdPeriods[] = $period;
            $this->command->info("✅ Created: {$period->name}");
        }

        // Distribute existing achievements across periods
        $this->command->info('📊 Distributing achievements across periods...');
        
        $achievements = StudentAchievement::whereNull('academic_period_id')->get();
        $totalAchievements = $achievements->count();
        
        if ($totalAchievements > 0) {
            // Distribute: 30% period 1, 35% period 2, 35% period 3
            $distribution = [
                $createdPeriods[0]->id => 0.30,
                $createdPeriods[1]->id => 0.35,
                $createdPeriods[2]->id => 0.35,
            ];

            $currentIndex = 0;
            foreach ($distribution as $periodId => $percentage) {
                $count = (int) ($totalAchievements * $percentage);
                $periodAchievements = $achievements->slice($currentIndex, $count);
                
                foreach ($periodAchievements as $achievement) {
                    $achievement->update(['academic_period_id' => $periodId]);
                }
                
                $currentIndex += $count;
                $period = AcademicPeriod::find($periodId);
                $this->command->info("  → {$count} achievements assigned to {$period->name}");
            }

            // Assign remaining to last period
            $remaining = $achievements->slice($currentIndex);
            foreach ($remaining as $achievement) {
                $achievement->update(['academic_period_id' => $createdPeriods[2]->id]);
            }
            
            if ($remaining->count() > 0) {
                $this->command->info("  → {$remaining->count()} remaining achievements assigned to {$createdPeriods[2]->name}");
            }
        }

        $this->command->newLine();
        $this->command->info('📊 Summary:');
        $this->command->table(
            ['Period', 'Achievements'],
            AcademicPeriod::withCount('achievements')->get()->map(function($period) {
                return [
                    $period->name . ($period->is_active ? ' (Active)' : ''),
                    $period->achievements_count
                ];
            })
        );

        $this->command->info('✅ Academic Period Seeder completed!');
    }
}
