<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DistributeCategoryDataSeeder extends Seeder
{
    private $eventNames = [
        'Akademik' => [
            'Olimpiade Matematika Nasional',
            'Kompetisi Karya Tulis Ilmiah',
            'Lomba Debat Bahasa Inggris',
            'Kompetisi Cerdas Cermat',
            'Olimpiade Fisika',
            'Lomba Esai Nasional',
        ],
        'Olahraga' => [
            'Kejuaraan Bulu Tangkis',
            'Turnamen Sepak Bola',
            'Kompetisi Renang',
            'Kejuaraan Basket',
            'Lomba Lari Marathon',
            'Turnamen Voli',
        ],
        'Seni & Budaya' => [
            'Festival Tari Tradisional',
            'Lomba Musik Akustik',
            'Kompetisi Fotografi',
            'Festival Film Pendek',
            'Lomba Desain Grafis',
            'Pameran Seni Rupa',
        ],
        'Teknologi & Inovasi' => [
            'Hackathon Nasional',
            'Kompetisi Robot',
            'Lomba Aplikasi Mobile',
            'Innovation Challenge',
            'IoT Competition',
            'Data Science Competition',
        ],
        'Kepemimpinan & Organisasi' => [
            'Leadership Camp',
            'Model United Nations',
            'Youth Leadership Summit',
            'Student Organization Award',
        ],
        'Penelitian & Karya Ilmiah' => [
            'Pekan Ilmiah Mahasiswa Nasional',
            'Research Competition',
            'Scientific Paper Competition',
            'Innovation Research Award',
        ],
        'Kewirausahaan' => [
            'Business Plan Competition',
            'Startup Pitch Competition',
            'Entrepreneurship Award',
            'Social Business Challenge',
        ],
        'Pengabdian Masyarakat' => [
            'Program Desa Binaan',
            'Volunteer Award',
            'Community Service Excellence',
            'Social Impact Program',
        ],
    ];

    private $organizers = [
        'Kementerian Pendidikan dan Kebudayaan',
        'Universitas Indonesia',
        'Institut Teknologi Bandung',
        'Universitas Gadjah Mada',
        'Kementerian Pemuda dan Olahraga',
        'KEMENRISTEK DIKTI',
        'IEEE Indonesia',
        'ASEAN Youth Forum',
        'Himpunan Mahasiswa Indonesia',
        'Universitas Pattimura',
    ];

    private $levels = ['Universitas', 'Nasional', 'Internasional'];

    public function run(): void
    {
        $this->command->info('🚀 Distributing data across all categories...');

        // Get all students and achievements
        $students = Student::all();
        $achievements = Achievement::with('category')->get();
        $periods = \App\Models\AcademicPeriod::all();

        if ($students->isEmpty()) {
            $this->command->error('❌ No students found. Please run LargeDataSeeder first.');

            return;
        }

        // Count current distribution
        $this->command->info('📊 Current distribution:');
        $currentDistribution = [];
        foreach ($achievements->groupBy('category.name') as $categoryName => $categoryAchievements) {
            $count = StudentAchievement::whereIn('achievement_id', $categoryAchievements->pluck('id'))->count();
            $currentDistribution[$categoryName] = $count;
            $this->command->line("  - {$categoryName}: {$count} prestasi");
        }

        // Calculate how many to add to each category (target: ~20 per category)
        $targetPerCategory = 20;
        $toAdd = [];

        foreach ($currentDistribution as $category => $count) {
            if ($count < $targetPerCategory) {
                $toAdd[$category] = $targetPerCategory - $count;
            }
        }

        if (empty($toAdd)) {
            $this->command->info('✅ All categories already have sufficient data!');

            return;
        }

        $this->command->info('📝 Adding achievements to categories:');
        foreach ($toAdd as $category => $count) {
            $this->command->line("  - {$category}: +{$count} prestasi");
        }

        // Add achievements
        $statuses = ['Menunggu', 'Disetujui', 'Ditolak', 'Revisi'];
        $statusWeights = [20, 60, 10, 10];

        $totalToAdd = array_sum($toAdd);
        $bar = $this->command->getOutput()->createProgressBar($totalToAdd);
        $bar->start();

        foreach ($toAdd as $categoryName => $count) {
            $categoryAchievements = $achievements->where('category.name', $categoryName);

            for ($i = 0; $i < $count; $i++) {
                $student = $students->random();
                $achievement = $categoryAchievements->random();

                $eventList = $this->eventNames[$categoryName] ?? ['Kompetisi Umum'];
                $eventName = $eventList[array_rand($eventList)];

                $level = $this->levels[array_rand($this->levels)];
                $organizer = $this->organizers[array_rand($this->organizers)];
                $status = $this->weightedRandom($statuses, $statusWeights);

                // Get validator for student's faculty
                $validator = User::where('role', 'Validator')
                    ->where('faculty', $student->faculty)
                    ->where('is_active', true)
                    ->first();

                $validatorId = ($status !== 'Menunggu' && $validator) ? $validator->id : null;

                // Assign to random period
                $assignedPeriod = $periods->random();
                $periodStart = $assignedPeriod->start_date->copy();
                $periodEnd = $assignedPeriod->end_date->copy();
                $daysDiff = $periodStart->diffInDays($periodEnd);
                $randomDays = rand(0, max(1, $daysDiff));
                $submittedAt = $periodStart->copy()->addDays($randomDays);
                $eventDate = $submittedAt->copy()->subDays(rand(7, 90));

                // Create certificate
                $certificatePath = $this->createDummyDocument($student, $eventName);

                StudentAchievement::create([
                    'student_id' => $student->student_id,
                    'achievement_id' => $achievement->id,
                    'event_name' => $eventName,
                    'level' => $level,
                    'organizer' => $organizer,
                    'event_date' => $eventDate,
                    'description' => "Berhasil meraih prestasi pada {$eventName} tingkat {$level}.",
                    'certificate' => $certificatePath,
                    'validation_status' => $status,
                    'validator_id' => $validatorId,
                    'submitted_by' => rand(1, 100) > 10 ? 'student' : 'admin',
                    'submitted_at' => $submittedAt,
                    'academic_period_id' => $assignedPeriod->id,
                    'sk_required' => true,
                ]);

                $bar->advance();
            }
        }

        $bar->finish();
        $this->command->newLine();

        // Show final distribution
        $this->command->info('📊 Final distribution:');
        foreach ($achievements->groupBy('category.name') as $categoryName => $categoryAchievements) {
            $count = StudentAchievement::whereIn('achievement_id', $categoryAchievements->pluck('id'))->count();
            $this->command->line("  - {$categoryName}: {$count} prestasi");
        }

        $this->command->info('✅ Data distribution completed!');
    }

    private function weightedRandom(array $values, array $weights): mixed
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);

        $currentWeight = 0;
        foreach ($values as $index => $value) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $value;
            }
        }

        return $values[0];
    }

    private function createDummyDocument(Student $student, string $eventName): string
    {
        $fileName = "certificate_{$student->student_id}_".time().'_'.rand(1000, 9999).'.pdf';
        $filePath = "certificates/{$fileName}";

        $pdf = Pdf::loadView('pdf.dummy-certificate', [
            'student' => $student,
            'eventName' => $eventName,
            'type' => 'certificate',
        ]);

        Storage::disk('public')->put($filePath, $pdf->output());

        return $filePath;
    }
}
