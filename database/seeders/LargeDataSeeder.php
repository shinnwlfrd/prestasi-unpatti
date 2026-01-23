<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\AchievementLevel;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\ValidationLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class LargeDataSeeder extends Seeder
{
    private $faculties = [
        'Fakultas Teknik',
        'Fakultas Ekonomi dan Bisnis',
        'Fakultas Hukum',
        'Fakultas Ilmu Sosial dan Ilmu Politik',
        'Fakultas Pertanian',
        'Fakultas Kedokteran',
        'Fakultas Keguruan dan Ilmu Pendidikan',
        'Fakultas Perikanan dan Ilmu Kelautan',
        'Fakultas MIPA',
    ];

    private $studyPrograms = [
        'Fakultas Teknik' => ['Teknik Informatika', 'Teknik Sipil', 'Teknik Elektro', 'Teknik Mesin', 'Arsitektur'],
        'Fakultas Ekonomi dan Bisnis' => ['Manajemen', 'Akuntansi', 'Ekonomi Pembangunan'],
        'Fakultas Hukum' => ['Ilmu Hukum'],
        'Fakultas Ilmu Sosial dan Ilmu Politik' => ['Ilmu Administrasi Negara', 'Ilmu Komunikasi', 'Sosiologi'],
        'Fakultas Pertanian' => ['Agroteknologi', 'Agribisnis', 'Teknologi Hasil Pertanian'],
        'Fakultas Kedokteran' => ['Pendidikan Dokter', 'Keperawatan', 'Kesehatan Masyarakat'],
        'Fakultas Keguruan dan Ilmu Pendidikan' => ['Pendidikan Matematika', 'Pendidikan Bahasa Inggris', 'Pendidikan Biologi', 'PGSD'],
        'Fakultas Perikanan dan Ilmu Kelautan' => ['Budidaya Perairan', 'Teknologi Hasil Perikanan', 'Ilmu Kelautan'],
        'Fakultas MIPA' => ['Matematika', 'Fisika', 'Kimia', 'Biologi'],
    ];

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

    private $rankings = [
        'Juara 1', 'Juara 2', 'Juara 3',
        'Juara Harapan 1', 'Juara Harapan 2',
        'Finalis', 'Best Presenter', 'Best Innovation',
        'Gold Medal', 'Silver Medal', 'Bronze Medal',
    ];

    private $levels = ['Universitas', 'Nasional', 'Internasional'];

    public function run(): void
    {
        $this->command->info('🚀 Starting Large Data Seeder...');

        // Get all achievements and levels
        $achievements = Achievement::with('category')->get();
        $achievementLevels = AchievementLevel::all();

        if ($achievements->isEmpty()) {
            $this->command->error('❌ No achievements found. Please run AchievementCategorySeeder first.');

            return;
        }

        // Create students (150 students)
        $this->command->info('👥 Creating 150 students...');
        $students = [];
        $studentIds = [];

        for ($i = 1; $i <= 150; $i++) {
            $faculty = $this->faculties[array_rand($this->faculties)];
            $studyProgram = $this->studyPrograms[$faculty][array_rand($this->studyPrograms[$faculty])];
            $year = rand(2020, 2024);

            $student = Student::create([
                'student_id' => sprintf('%d%04d', $year, $i),
                'name' => $this->generateName(),
                'email' => sprintf('student%d@unpatti.ac.id', $i),
                'faculty' => $faculty,
                'program_study' => $studyProgram,
                'semester' => rand(1, 8),
                'gpa' => round(rand(250, 400) / 100, 2),
            ]);

            $students[] = $student;
            $studentIds[] = $student->student_id;
        }

        $this->command->info('✅ Created '.count($students).' students');

        // Create validators for each faculty
        $this->command->info('👨‍💼 Creating validators for each faculty...');
        $validators = [];

        foreach ($this->faculties as $faculty) {
            $validator = User::create([
                'name' => 'Validator '.$faculty,
                'email' => strtolower(str_replace(' ', '.', $faculty)).'@unpatti.ac.id',
                'password' => bcrypt('password'),
                'role' => 'Validator',
                'faculty' => $faculty,
                'is_active' => true,
            ]);
            $validators[$faculty] = $validator;
        }

        $this->command->info('✅ Created '.count($validators).' validators');

        // Create 150 achievements
        $this->command->info('🏆 Creating 150 student achievements...');
        $statuses = ['Menunggu', 'Disetujui', 'Ditolak', 'Revisi'];
        $statusWeights = [30, 50, 10, 10]; // 30% pending, 50% approved, 10% rejected, 10% revision

        $createdCount = 0;
        $bar = $this->command->getOutput()->createProgressBar(150);
        $bar->start();

        for ($i = 0; $i < 150; $i++) {
            $student = $students[array_rand($students)];
            $achievement = $achievements->random();
            $categoryName = $achievement->category->name;

            // Get event names for this category
            $eventList = $this->eventNames[$categoryName] ?? ['Kompetisi Umum'];
            $eventName = $eventList[array_rand($eventList)];

            $level = $this->levels[array_rand($this->levels)];
            $organizer = $this->organizers[array_rand($this->organizers)];

            // Weighted random status
            $status = $this->weightedRandom($statuses, $statusWeights);

            // Create certificate file
            $certificatePath = $this->createDummyDocument($student, $eventName, 'certificate');

            // Determine validator and validation data
            $validator = $validators[$student->faculty] ?? null;
            $validatorId = null;
            $skRequired = true;
            $skWaiverReason = null;

            if ($status !== 'Menunggu') {
                $validatorId = $validator?->id;

                // 20% chance of SK waiver for approved achievements
                if ($status === 'Disetujui' && rand(1, 100) <= 20) {
                    $skRequired = false;
                    $skWaiverReason = ['tingkat_universitas', 'sk_dalam_proses', 'dokumen_alternatif'][array_rand(['tingkat_universitas', 'sk_dalam_proses', 'dokumen_alternatif'])];
                }
            }

            // Assign to academic period and generate date within period range
            $periods = \App\Models\AcademicPeriod::all();
            $assignedPeriod = $periods->random();

            // Generate submitted_at within the period's date range
            $periodStart = $assignedPeriod->start_date->copy();
            $periodEnd = $assignedPeriod->end_date->copy();
            $daysDiff = $periodStart->diffInDays($periodEnd);
            $randomDays = rand(0, $daysDiff);
            $submittedAt = $periodStart->copy()->addDays($randomDays);

            // Event date should be before or around submitted date
            $eventDate = $submittedAt->copy()->subDays(rand(7, 90));

            $studentAchievement = StudentAchievement::create([
                'student_id' => $student->student_id,
                'achievement_id' => $achievement->id,
                'event_name' => $eventName,
                'level' => $level,
                'organizer' => $organizer,
                'event_date' => $eventDate,
                'description' => "Berhasil meraih prestasi pada {$eventName} tingkat {$level} yang diselenggarakan oleh {$organizer}.",
                'certificate' => $certificatePath,
                'validation_status' => $status,
                'validator_id' => $validatorId,
                'submitted_by' => rand(1, 100) > 10 ? 'student' : 'admin',
                'submitted_at' => $submittedAt,
                'academic_period_id' => $assignedPeriod->id,
                'sk_required' => $skRequired,
                'sk_waiver_reason' => $skWaiverReason,
            ]);

            // Create validation log if not pending
            if ($status !== 'Menunggu' && $validator) {
                $this->createValidationLog($studentAchievement, $validator, $status);
            }

            $createdCount++;
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('✅ Created '.$createdCount.' student achievements');

        // Summary
        $this->command->newLine();
        $this->command->info('📊 Summary:');
        $this->command->table(
            ['Item', 'Count'],
            [
                ['Students', Student::count()],
                ['Validators', User::where('role', 'Validator')->count()],
                ['Student Achievements', StudentAchievement::count()],
                ['Pending', StudentAchievement::where('validation_status', 'Menunggu')->count()],
                ['Approved', StudentAchievement::where('validation_status', 'Disetujui')->count()],
                ['Rejected', StudentAchievement::where('validation_status', 'Ditolak')->count()],
                ['Revision', StudentAchievement::where('validation_status', 'Revisi')->count()],
                ['Validation Logs', ValidationLog::count()],
            ]
        );

        $this->command->info('✅ Large Data Seeder completed successfully!');
    }

    private function generateName(): string
    {
        $firstNames = [
            'Ahmad', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fitri', 'Gita', 'Hadi',
            'Indah', 'Joko', 'Kartika', 'Lina', 'Made', 'Nanda', 'Omar', 'Putri',
            'Rina', 'Sari', 'Tono', 'Umar', 'Vina', 'Wati', 'Yudi', 'Zahra',
            'Agus', 'Bayu', 'Dian', 'Eka', 'Fajar', 'Gilang', 'Hendra', 'Irfan',
        ];

        $lastNames = [
            'Pratama', 'Wijaya', 'Kusuma', 'Santoso', 'Permana', 'Saputra', 'Lestari',
            'Wibowo', 'Setiawan', 'Hidayat', 'Nugroho', 'Rahayu', 'Suharto', 'Purnomo',
            'Utomo', 'Susanto', 'Kurniawan', 'Firmansyah', 'Hakim', 'Ramadhan',
        ];

        return $firstNames[array_rand($firstNames)].' '.$lastNames[array_rand($lastNames)];
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

    private function createDummyDocument(Student $student, string $eventName, string $type): string
    {
        $fileName = "{$type}_{$student->student_id}_".time().'_'.rand(1000, 9999).'.pdf';
        $filePath = "certificates/{$fileName}";

        // Create simple PDF
        $pdf = Pdf::loadView('pdf.dummy-certificate', [
            'student' => $student,
            'eventName' => $eventName,
            'type' => $type,
        ]);

        Storage::disk('public')->put($filePath, $pdf->output());

        return $filePath;
    }

    private function createValidationLog(StudentAchievement $achievement, User $validator, string $status): void
    {
        $oldStatus = 'pending';
        $newStatus = match ($status) {
            'Disetujui' => 'approved',
            'Ditolak' => 'rejected',
            'Revisi' => 'revision_requested',
            default => 'pending',
        };

        $notes = match ($status) {
            'Disetujui' => 'Prestasi telah diverifikasi dan disetujui.',
            'Ditolak' => 'Dokumen tidak memenuhi persyaratan.',
            'Revisi' => 'Mohon perbaiki dokumen sesuai catatan.',
            default => null,
        };

        ValidationLog::create([
            'sa_id' => $achievement->sa_id,
            'validator_id' => $validator->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'validated_at' => now()->subDays(rand(1, 30)),
        ]);
    }
}
