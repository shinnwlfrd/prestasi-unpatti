<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AchievementLevel;
use App\Models\AuthLog;
use App\Models\SKAssignment;
use App\Models\SKDocument;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\UserRole;
use App\Models\ValidationLog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DashboardDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Dashboard Demo Seeder...');

        // 1. Academic Periods
        $this->seedAcademicPeriods();

        // 2. Master Data: Achievement Categories & Levels
        $this->seedMasterData();

        // 3. Users & Students
        $this->seedUsersAndStudents();

        // 4. Student Achievements & Validations
        $this->seedStudentAchievements();

        // 5. SK Documents
        $this->seedSKDocuments();

        // 6. Auth Logs
        $this->seedAuthLogs();

        $this->command->info('Dashboard Demo Seeder completed!');
    }

    private function seedAcademicPeriods()
    {
        $this->command->info('Seeding Academic Periods...');

        AcademicPeriod::create([
            'name' => '2023/2024 Ganjil',
            'code' => '20231',
            'year' => 2023,
            'semester' => 'Ganjil',
            'start_date' => Carbon::create(2023, 9, 1),
            'end_date' => Carbon::create(2024, 2, 28),
            'is_active' => false,
        ]);

        AcademicPeriod::create([
            'name' => '2023/2024 Genap',
            'code' => '20232',
            'year' => 2023,
            'semester' => 'Genap',
            'start_date' => Carbon::create(2024, 3, 1),
            'end_date' => Carbon::create(2024, 8, 31),
            'is_active' => false,
        ]);

        AcademicPeriod::create([
            'name' => '2024/2025 Ganjil',
            'code' => '20241',
            'year' => 2024,
            'semester' => 'Ganjil',
            'start_date' => Carbon::create(2024, 9, 1),
            'end_date' => Carbon::create(2025, 2, 28),
            'is_active' => true,
        ]);
    }

    private function seedMasterData()
    {
        $this->command->info('Seeding Achievement Categories & Levels...');

        $categories = [
            ['name' => 'Akademik', 'icon' => 'book', 'color' => '#3b82f6', 'order' => 1],
            ['name' => 'Olahraga', 'icon' => 'trophy', 'color' => '#ef4444', 'order' => 2],
            ['name' => 'Seni & Budaya', 'icon' => 'music', 'color' => '#f59e0b', 'order' => 3],
            ['name' => 'Kemanusiaan', 'icon' => 'heart', 'color' => '#ec4899', 'order' => 4],
            ['name' => 'Kewirausahaan', 'icon' => 'briefcase', 'color' => '#10b981', 'order' => 5],
            ['name' => 'Sains & Inovasi', 'icon' => 'lightbulb', 'color' => '#8b5cf6', 'order' => 6],
        ];

        foreach ($categories as $cat) {
            $category = AchievementCategory::create($cat + ['is_active' => true]);
            Achievement::create([
                'category_id' => $category->id,
                'name' => 'Template '.$category->name,
                'description' => 'Template prestasi untuk kategori '.$category->name,
                'is_active' => true,
            ]);
        }

        $levels = [
            ['name' => 'Universitas', 'points' => 10, 'order' => 1],
            ['name' => 'Nasional', 'points' => 30, 'order' => 2],
            ['name' => 'Internasional', 'points' => 100, 'order' => 3],
        ];

        foreach ($levels as $lvl) {
            AchievementLevel::create($lvl + ['is_active' => true]);
        }
    }

    private function seedUsersAndStudents()
    {
        $this->command->info('Seeding Users & Students...');

        $faculties = [
            'Fakultas Teknik' => ['Teknik Sipil', 'Teknik Mesin', 'Teknik Elektro', 'Teknik Perkapalan'],
            'Fakultas Hukum' => ['Ilmu Hukum'],
            'Fakultas Ekonomi dan Bisnis' => ['Ekonomi Pembangunan', 'Manajemen', 'Akuntansi'],
            'Fakultas Kedokteran' => ['Pendidikan Dokter'],
            'Fakultas Ilmu Sosial dan Ilmu Politik' => ['Ilmu Administrasi Negara', 'Ilmu Pemerintahan', 'Sosiologi', 'Ilmu Komunikasi'],
            'Fakultas Perikanan dan Ilmu Kelautan' => ['Manajemen Sumberdaya Perairan', 'Budidaya Perairan', 'Pemanfaatan Sumberdaya Perikanan'],
            'Fakultas MIPA' => ['Matematika', 'Fisika', 'Kimia', 'Biologi'],
            'Fakultas Keguruan dan Ilmu Pendidikan' => ['Pendidikan Matematika', 'Pendidikan Biologi', 'Pendidikan Bahasa Inggris'],
            'Fakultas Pertanian' => ['Agribisnis', 'Agroteknologi', 'Peternakan'],
        ];

        // Create Faculty Operators
        foreach ($faculties as $facultyName => $prodis) {
            $shortName = $this->getShortName($facultyName);
            $email = strtolower($shortName).'@operator.test';

            $user = User::create([
                'name' => 'Operator '.$facultyName,
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'Operator',
                'is_active' => true,
            ]);

            UserRole::create([
                'user_id' => $user->id,
                'role' => 'operator',
                'level' => 'faculty',
                'faculty_name' => $facultyName,
                'is_active' => true,
                'activated_at' => now(),
            ]);
        }

        // Create Students (50 students)
        $pId = 2021;
        for ($i = 1; $i <= 50; $i++) {
            $facultyName = array_rand($faculties);
            $prodi = $faculties[$facultyName][array_rand($faculties[$facultyName])];
            $studentId = $pId.str_pad($i, 5, '0', STR_PAD_LEFT);
            $name = 'Mahasiswa Demo '.$i;
            $email = $studentId.'@student.unpatti.ac.id';

            $student = Student::create([
                'student_id' => $studentId,
                'name' => $name,
                'faculty' => $facultyName,
                'program_study' => $prodi,
                'angkatan' => 2021 + ($i % 4),
                'gpa' => 3.0 + (rand(0, 100) / 100),
                'email' => $email,
            ]);

            // Create User for Student (some students only)
            if ($i <= 10) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role' => 'Student',
                    'is_active' => true,
                ]);

                UserRole::create([
                    'user_id' => $user->id,
                    'role' => 'mahasiswa',
                    'level' => 'university',
                    'is_active' => true,
                    'activated_at' => now(),
                ]);
            }

            if ($i % 10 == 0) {
                $pId++;
            }
        }
    }

    private function seedStudentAchievements()
    {
        $this->command->info('Seeding Student Achievements...');

        $students = Student::all();
        $periods = AcademicPeriod::all();
        $categories = AchievementCategory::all();
        $levels = ['Universitas', 'Nasional', 'Internasional'];
        $statuses = [
            // Stage 1
            'submitted', 'faculty_review', 'faculty_approved', 'faculty_rejected', 'faculty_revision',
            // Stage 2
            'university_review', 'university_approved', 'university_rejected',
            // Appeal
            'appeal_submitted', 'appeal_approved', 'appeal_rejected',
            // Legacy
            'Menunggu', 'Disetujui', 'Ditolak',
        ];

        $validators = User::where('role', 'Operator')->get();
        $admins = User::where('role', 'Admin')->get();

        foreach ($students as $student) {
            // Each student has 1-5 achievements
            $achievementCount = rand(1, 5);
            for ($j = 0; $j < $achievementCount; $j++) {
                $period = $periods->random();
                $category = $categories->random();
                $level = $levels[array_rand($levels)];
                $status = $statuses[array_rand($statuses)];

                // Adjust status based on period (if inactive, most should be approved or rejected)
                if (! $period->is_active && rand(0, 10) > 2) {
                    $status = rand(0, 1) ? 'university_approved' : 'university_rejected';
                }

                $createdAt = Carbon::parse($period->start_date)->addDays(rand(1, 150));
                if ($createdAt->isFuture()) {
                    $createdAt = now()->subDays(rand(1, 30));
                }

                $submittedAt = (clone $createdAt)->addHours(rand(1, 48));

                // Achievement Title Example
                $achTitles = [
                    'Juara 1 Lomba Karya Tulis Ilmiah',
                    'Peserta Olimpiade Sains',
                    'Medali Emas Kejuaraan Karate',
                    'Finalis Putera Puteri Kampus',
                    'Pemenang Hibah Program Kreativitas Mahasiswa',
                    'Juara 3 Kompetisi Debat',
                    'Delegasi Pertukaran Mahasiswa',
                    'Penulis Artikel Jurnal Terakreditasi',
                ];
                $title = $achTitles[array_rand($achTitles)].' '.$level;

                $achievementTemplate = Achievement::where('category_id', $category->id)->first();

                $sa = StudentAchievement::create([
                    'student_id' => $student->student_id,
                    'achievement_id' => $achievementTemplate->id,
                    'academic_period_id' => $period->id,
                    'event_name' => $title,
                    'level' => $level,
                    'organizer' => 'Penyelenggara Demo '.rand(1, 10),
                    'event_date' => (clone $createdAt)->subDays(rand(7, 30)),
                    'ranking' => rand(1, 10),
                    'validation_status' => $status,
                    'submitted_at' => $submittedAt,
                    'created_at' => $createdAt,
                    'updated_at' => (clone $submittedAt)->addDays(rand(1, 14)),
                ]);

                // Create Validation Logs for some
                if (! in_array($status, ['submitted', 'Menunggu'])) {
                    $valAction = 'validate';
                    $valStage = 'faculty';

                    // Match operator by faculty name if possible
                    $validator = $validators->filter(function ($v) use ($student) {
                        return $v->roles()->where('role', 'operator')->where('faculty_name', $student->faculty)->exists();
                    })->first() ?? $validators->random();

                    if (str_contains($status, 'university')) {
                        $valStage = 'university';
                        $validator = $admins->random();
                    }

                    ValidationLog::create([
                        'sa_id' => $sa->sa_id,
                        'validator_id' => $validator->id,
                        'old_status' => 'submitted',
                        'new_status' => $status,
                        'notes' => 'Validasi demo sistem',
                        'validation_type' => $valAction,
                        'validation_stage' => $valStage,
                        'validated_at' => (clone $submittedAt)->addDays(rand(1, 5)),
                    ]);

                    if (str_contains($status, 'approved') || str_contains($status, 'rejected')) {
                        if ($valStage === 'faculty') {
                            $sa->faculty_validator_id = $validator->id;
                            $sa->faculty_validated_at = $sa->updated_at;
                        } else {
                            $sa->university_validator_id = $validator->id;
                            $sa->university_validated_at = $sa->updated_at;
                        }
                        $sa->save();
                    }
                }

                // Add resubmission data for some
                if (rand(0, 10) > 8) {
                    $sa->is_resubmission = true;
                    $sa->resubmission_count = rand(1, 3);
                    $sa->last_resubmitted_at = now()->subDays(rand(1, 5));
                    $sa->resubmission_reason = 'Perbaikan dokumen pendukung';
                    $sa->save();
                }
            }
        }
    }

    private function seedSKDocuments()
    {
        $this->command->info('Seeding SK Documents...');

        $period = AcademicPeriod::where('is_active', true)->first();
        if (! $period) {
            return;
        }

        for ($i = 1; $i <= 5; $i++) {
            $sk = SKDocument::create([
                'sk_number' => 'SK/UNPATTI/'.date('Y').'/'.str_pad($i, 3, '0', STR_PAD_LEFT),
                'issued_date' => now()->subMonths($i),
                'title' => 'SK Penetapan Prestasi Mahasiswa Periode '.$i,
                'issued_by' => 'Rektor Universitas Pattimura',
                'created_by' => User::where('role', 'Admin')->first()->id,
            ]);

            // Assign some achievements to this SK
            $achievements = StudentAchievement::where('academic_period_id', $period->id)
                ->where('validation_status', 'university_approved')
                ->limit(rand(5, 10))
                ->get();

            foreach ($achievements as $ach) {
                SKAssignment::create([
                    'sk_id' => $sk->id,
                    'sa_id' => $ach->sa_id,
                    'assigned_at' => now(),
                    'assigned_by' => $sk->created_by,
                ]);
            }
        }
    }

    private function seedAuthLogs()
    {
        $this->command->info('Seeding Auth Logs...');

        $users = User::limit(20)->get();
        foreach ($users as $user) {
            for ($i = 0; $i < 5; $i++) {
                AuthLog::create([
                    'user_id' => $user->id,
                    'action' => rand(0, 1) ? AuthLog::ACTION_LOGIN : AuthLog::ACTION_LOGOUT,
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
                    'method' => 'SSO',
                    'created_at' => now()->subHours(rand(1, 500)),
                ]);
            }
        }
    }

    private function getShortName($faculty)
    {
        $mapping = [
            'Fakultas Teknik' => 'FT',
            'Fakultas Hukum' => 'FH',
            'Fakultas Ekonomi dan Bisnis' => 'FEB',
            'Fakultas Kedokteran' => 'FK',
            'Fakultas Ilmu Sosial dan Ilmu Politik' => 'FISIP',
            'Fakultas Perikanan dan Ilmu Kelautan' => 'FPIK',
            'Fakultas MIPA' => 'FMIPA',
            'Fakultas Keguruan dan Ilmu Pendidikan' => 'FKIP',
            'Fakultas Pertanian' => 'FP',
        ];

        return $mapping[$faculty] ?? 'UNP';
    }
}
