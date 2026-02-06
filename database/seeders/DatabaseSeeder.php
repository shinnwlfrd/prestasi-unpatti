<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AchievementLevel;
use App\Models\SKDocument;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\UserRole;
use App\Models\ValidationLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    private array $faculties = [];
    private array $departments = [];
    private array $programs = [];
    private array $users = [];
    private array $students = [];
    private array $achievements = [];
    private array $skDocuments = [];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Complete System Seeder...');
        $this->command->info('');

        // 1. Load SIGAP hierarchy
        $this->loadSigapHierarchy();

        // 2. Seed master data
        $this->seedAcademicPeriods();
        $this->seedAchievementCategories();
        $this->seedAchievementLevels();
        $this->seedAchievements();

        // 3. Seed users (admin, operators, pimpinan)
        $this->seedUsers();

        // 4. Seed students (banyak mahasiswa per fakultas)
        $this->seedStudents();

        // 5. Seed SK Documents
        $this->seedSKDocuments();

        // 6. Seed student achievements dengan 2-stage validation
        $this->seedStudentAchievements();

        $this->command->info('');
        $this->command->info('✅ Complete System Seeder finished successfully!');
        $this->printSummary();
    }

    /**
     * Load SIGAP hierarchy from JSON
     */
    private function loadSigapHierarchy(): void
    {
        $this->command->info('📚 Loading SIGAP hierarchy...');

        $jsonPath = database_path('seeders/data/sigap_hierarchy.json');
        if (!file_exists($jsonPath)) {
            $this->command->error('❌ SIGAP hierarchy file not found!');
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);

        foreach ($data as $faculty) {
            $facultyName = $faculty['nama'];
            $facultyId = $faculty['id'];
            $this->faculties[$facultyName] = [
                'id' => $facultyId,
                'name' => $facultyName,
            ];

            foreach ($faculty['departments'] as $department) {
                $deptName = $department['nama'];
                $deptId = $department['id'];
                $this->departments[$facultyName][$deptName] = [
                    'id' => $deptId,
                    'name' => $deptName,
                ];

                foreach ($department['study_programs'] as $program) {
                    $this->programs[$facultyName][$deptName][] = [
                        'id' => $program['id'],
                        'name' => $program['nama'],
                        'code' => $program['kode'],
                    ];
                }
            }
        }

        $this->command->info("   ✓ Loaded ".count($this->faculties)." faculties");
    }

    /**
     * Seed academic periods
     */
    private function seedAcademicPeriods(): void
    {
        $this->command->info('📅 Seeding academic periods...');

        $periods = [
            ['code' => '20231', 'name' => '2023/2024 Ganjil', 'semester' => 'Ganjil', 'year' => '2023/2024', 'start_date' => '2023-09-01', 'end_date' => '2024-01-31', 'is_active' => false],
            ['code' => '20232', 'name' => '2023/2024 Genap', 'semester' => 'Genap', 'year' => '2023/2024', 'start_date' => '2024-02-01', 'end_date' => '2024-07-31', 'is_active' => false],
            ['code' => '20241', 'name' => '2024/2025 Ganjil', 'semester' => 'Ganjil', 'year' => '2024/2025', 'start_date' => '2024-09-01', 'end_date' => '2025-01-31', 'is_active' => false],
            ['code' => '20242', 'name' => '2024/2025 Genap', 'semester' => 'Genap', 'year' => '2024/2025', 'start_date' => '2025-02-01', 'end_date' => '2025-07-31', 'is_active' => false],
            ['code' => '20251', 'name' => '2025/2026 Ganjil', 'semester' => 'Ganjil', 'year' => '2025/2026', 'start_date' => '2025-09-01', 'end_date' => '2026-01-31', 'is_active' => true],
        ];

        foreach ($periods as $period) {
            AcademicPeriod::create($period);
        }

        $this->command->info('   ✓ Created '.count($periods).' academic periods');
    }

    /**
     * Seed achievement categories
     */
    private function seedAchievementCategories(): void
    {
        $this->command->info('🏆 Seeding achievement categories...');

        $categories = [
            ['name' => 'Akademik', 'description' => 'Prestasi di bidang akademik seperti olimpiade, penelitian, dan kompetisi ilmiah', 'is_active' => true],
            ['name' => 'Seni & Budaya', 'description' => 'Prestasi di bidang seni, musik, tari, teater, dan budaya', 'is_active' => true],
            ['name' => 'Olahraga', 'description' => 'Prestasi di bidang olahraga dan kompetisi atletik', 'is_active' => true],
            ['name' => 'Teknologi & Inovasi', 'description' => 'Prestasi di bidang teknologi, inovasi, dan kewirausahaan digital', 'is_active' => true],
            ['name' => 'Kewirausahaan', 'description' => 'Prestasi di bidang bisnis, startup, dan entrepreneurship', 'is_active' => true],
            ['name' => 'Kepemimpinan & Organisasi', 'description' => 'Prestasi di bidang kepemimpinan, organisasi, dan pengabdian masyarakat', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            AchievementCategory::create($category);
        }

        $this->command->info('   ✓ Created '.count($categories).' categories');
    }

    /**
     * Seed achievement levels
     */
    private function seedAchievementLevels(): void
    {
        $this->command->info('📊 Seeding achievement levels...');

        $levels = [
            ['name' => 'Universitas', 'points' => 10, 'description' => 'Prestasi tingkat universitas atau internal kampus', 'is_active' => true],
            ['name' => 'Nasional', 'points' => 20, 'description' => 'Prestasi tingkat nasional atau antar universitas', 'is_active' => true],
            ['name' => 'Internasional', 'points' => 30, 'description' => 'Prestasi tingkat internasional atau global', 'is_active' => true],
        ];

        foreach ($levels as $level) {
            AchievementLevel::create($level);
        }

        $this->command->info('   ✓ Created '.count($levels).' levels');
    }

    /**
     * Seed achievements
     */
    private function seedAchievements(): void
    {
        $this->command->info('🎯 Seeding achievements...');

        $categories = AchievementCategory::all();
        $achievementData = [
            'Akademik' => [
                'Juara Olimpiade Sains',
                'Best Paper Award',
                'Penelitian Terbaik',
                'Mahasiswa Berprestasi',
                'Juara Debat Ilmiah',
                'Best Presenter Conference',
            ],
            'Seni & Budaya' => [
                'Juara Lomba Seni',
                'Penampilan Terbaik',
                'Karya Seni Terbaik',
                'Juara Festival Musik',
                'Best Choreography',
            ],
            'Olahraga' => [
                'Juara Kompetisi Olahraga',
                'Atlet Terbaik',
                'MVP Tournament',
                'Medali Emas',
                'Best Team Performance',
            ],
            'Teknologi & Inovasi' => [
                'Hackathon Winner',
                'Innovation Award',
                'Best Startup',
                'Best Mobile App',
                'IoT Competition Winner',
            ],
            'Kewirausahaan' => [
                'Business Plan Winner',
                'Best Entrepreneur',
                'Startup Competition',
                'Best Pitch',
                'Social Enterprise Award',
            ],
            'Kepemimpinan & Organisasi' => [
                'Best Leader',
                'Outstanding Organization',
                'Community Service Award',
                'Best Volunteer',
                'Leadership Excellence',
            ],
        ];

        foreach ($categories as $category) {
            if (isset($achievementData[$category->name])) {
                foreach ($achievementData[$category->name] as $name) {
                    Achievement::create([
                        'category_id' => $category->id,
                        'name' => $name,
                        'description' => "Prestasi {$name} dalam kategori {$category->name}",
                    ]);
                }
            }
        }

        $this->command->info('   ✓ Created '.Achievement::count().' achievements');
    }

    /**
     * Seed users (admin, operators, pimpinan)
     */
    private function seedUsers(): void
    {
        $this->command->info('👥 Seeding users...');

        // Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);
        UserRole::create([
            'user_id' => $superAdmin->id,
            'role' => 'super_admin',
            'level' => 'university',
            'is_active' => true,
        ]);
        $this->users['super_admin'] = $superAdmin;

        // Admin
        $admin = User::create([
            'name' => 'Admin Universitas',
            'email' => 'admin@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);
        UserRole::create([
            'user_id' => $admin->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
        ]);
        $this->users['admin'] = $admin;

        // Rektor
        $rektor = User::create([
            'name' => 'Prof. Dr. Rektor Unpatti',
            'email' => 'rektor@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Pimpinan',
        ]);
        UserRole::create([
            'user_id' => $rektor->id,
            'role' => 'pimpinan',
            'level' => 'university',
            'position' => 'rektor',
            'is_active' => true,
        ]);
        $this->users['rektor'] = $rektor;

        // Wakil Rektor I (Bidang Akademik)
        $warek1 = User::create([
            'name' => 'Prof. Dr. Wakil Rektor I',
            'email' => 'warek1@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Pimpinan',
        ]);
        UserRole::create([
            'user_id' => $warek1->id,
            'role' => 'pimpinan',
            'level' => 'university',
            'position' => 'wakil_rektor_1',
            'is_active' => true,
        ]);
        $this->users['warek1'] = $warek1;

        // Wakil Rektor II (Bidang Umum dan Keuangan)
        $warek2 = User::create([
            'name' => 'Dr. Wakil Rektor II',
            'email' => 'warek2@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Pimpinan',
        ]);
        UserRole::create([
            'user_id' => $warek2->id,
            'role' => 'pimpinan',
            'level' => 'university',
            'position' => 'wakil_rektor_2',
            'is_active' => true,
        ]);
        $this->users['warek2'] = $warek2;

        // Wakil Rektor III (Bidang Kemahasiswaan)
        $warek3 = User::create([
            'name' => 'Dr. Wakil Rektor III',
            'email' => 'warek3@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Pimpinan',
        ]);
        UserRole::create([
            'user_id' => $warek3->id,
            'role' => 'pimpinan',
            'level' => 'university',
            'position' => 'wakil_rektor_3',
            'is_active' => true,
        ]);
        $this->users['warek3'] = $warek3;

        // Direktur Pascasarjana
        $dirpps = User::create([
            'name' => 'Prof. Dr. Direktur Pascasarjana',
            'email' => 'direktur.pps@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Pimpinan',
        ]);
        UserRole::create([
            'user_id' => $dirpps->id,
            'role' => 'pimpinan',
            'level' => 'university',
            'position' => 'direktur_pps',
            'is_active' => true,
        ]);
        $this->users['direktur_pps'] = $dirpps;

        // Kepala Biro Kemahasiswaan
        $kabiro = User::create([
            'name' => 'Kepala Biro Kemahasiswaan',
            'email' => 'kabiro.kemahasiswaan@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Pimpinan',
        ]);
        UserRole::create([
            'user_id' => $kabiro->id,
            'role' => 'pimpinan',
            'level' => 'university',
            'position' => 'kepala_biro_kemahasiswaan',
            'is_active' => true,
        ]);
        $this->users['kabiro_kemahasiswaan'] = $kabiro;

        // Create operators and pimpinan for each faculty
        $globalProgCounter = 1; // Global counter for all programs across faculties
        foreach ($this->faculties as $facultyName => $facultyData) {
            $facultyCode = $this->getFacultyCode($facultyName);

            // Operator Fakultas
            $operator = User::create([
                'name' => "Operator {$facultyName}",
                'email' => "operator.{$facultyCode}@unpatti.ac.id",
                'password' => Hash::make('password'),
                'role' => 'Validator',
                'faculty' => $facultyName,
                'faculty_id' => $facultyData['id'],
            ]);
            UserRole::create([
                'user_id' => $operator->id,
                'role' => 'operator',
                'level' => 'faculty',
                'faculty_id' => $facultyData['id'],
                'faculty_name' => $facultyName,
                'is_active' => true,
            ]);
            $this->users["operator_{$facultyCode}"] = $operator;

            // Pimpinan Fakultas (Dekan)
            $pimpinan = User::create([
                'name' => "Dekan {$facultyName}",
                'email' => "dekan.{$facultyCode}@unpatti.ac.id",
                'password' => Hash::make('password'),
                'role' => 'Pimpinan',
                'faculty' => $facultyName,
                'faculty_id' => $facultyData['id'],
            ]);
            UserRole::create([
                'user_id' => $pimpinan->id,
                'role' => 'pimpinan',
                'level' => 'faculty',
                'faculty_id' => $facultyData['id'],
                'faculty_name' => $facultyName,
                'position' => 'dekan',
                'is_active' => true,
            ]);
            $this->users["pimpinan_{$facultyCode}"] = $pimpinan;

            // Create Ketua Jurusan and Kaprodi for each department
            if (isset($this->departments[$facultyName])) {
                $deptCounter = 1;
                foreach ($this->departments[$facultyName] as $deptName => $deptData) {
                    $deptCode = strtolower(str_replace([' ', '-', '.'], '', substr($deptName, 0, 3)));
                    $uniqueDeptCode = "{$deptCode}{$deptCounter}";
                    
                    // Ketua Jurusan
                    $kajur = User::create([
                        'name' => "Ketua Jurusan {$deptName}",
                        'email' => "kajur.{$uniqueDeptCode}.{$facultyCode}@unpatti.ac.id",
                        'password' => Hash::make('password'),
                        'role' => 'Pimpinan',
                        'faculty' => $facultyName,
                        'faculty_id' => $facultyData['id'],
                    ]);
                    UserRole::create([
                        'user_id' => $kajur->id,
                        'role' => 'pimpinan',
                        'level' => 'department',
                        'faculty_id' => $facultyData['id'],
                        'faculty_name' => $facultyName,
                        'department_id' => $deptData['id'],
                        'department_name' => $deptName,
                        'position' => 'ketua_jurusan',
                        'is_active' => true,
                    ]);
                    $this->users["kajur_{$facultyCode}_{$uniqueDeptCode}"] = $kajur;

                    // Create Kaprodi for each program in this department
                    if (isset($this->programs[$facultyName][$deptName])) {
                        foreach ($this->programs[$facultyName][$deptName] as $program) {
                            $progCode = strtolower(str_replace([' ', '-', '.'], '', substr($program['name'], 0, 5)));
                            
                            $kaprodi = User::create([
                                'name' => "Kaprodi {$program['name']}",
                                'email' => "kaprodi.{$progCode}{$globalProgCounter}@unpatti.ac.id",
                                'password' => Hash::make('password'),
                                'role' => 'Pimpinan',
                                'faculty' => $facultyName,
                                'faculty_id' => $facultyData['id'],
                            ]);
                            UserRole::create([
                                'user_id' => $kaprodi->id,
                                'role' => 'pimpinan',
                                'level' => 'program_study',
                                'faculty_id' => $facultyData['id'],
                                'faculty_name' => $facultyName,
                                'department_id' => $deptData['id'],
                                'department_name' => $deptName,
                                'program_study_id' => $program['id'],
                                'program_study_name' => $program['name'],
                                'position' => 'kaprodi',
                                'is_active' => true,
                            ]);
                            $this->users["kaprodi_{$progCode}{$globalProgCounter}"] = $kaprodi;
                            $globalProgCounter++;
                        }
                    }
                    $deptCounter++;
                }
            }
        }

        $this->command->info('   ✓ Created '.User::count().' users');
    }

    /**
     * Seed students (banyak mahasiswa per fakultas)
     */
    private function seedStudents(): void
    {
        $this->command->info('🎓 Seeding students (this may take a while)...');

        $studentsPerFaculty = 50; // 50 mahasiswa per fakultas
        $angkatanRange = [2021, 2022, 2023, 2024, 2025];
        $studentCount = 0;
        $facultyCounter = 1; // Start from 1 for consistent faculty codes

        foreach ($this->faculties as $facultyName => $facultyData) {
            // Skip if faculty has no departments
            if (!isset($this->departments[$facultyName]) || empty($this->departments[$facultyName])) {
                continue;
            }

            $facultyCode = str_pad($facultyCounter, 2, '0', STR_PAD_LEFT);
            $deptCounter = 1; // Reset department counter for each faculty

            foreach ($this->departments[$facultyName] as $deptName => $deptData) {
                if (!isset($this->programs[$facultyName][$deptName])) {
                    continue;
                }

                $deptCode = str_pad($deptCounter, 2, '0', STR_PAD_LEFT);
                $progCounter = 1; // Reset program counter for each department

                foreach ($this->programs[$facultyName][$deptName] as $program) {
                    $progCode = str_pad($progCounter, 2, '0', STR_PAD_LEFT);

                    // Create students for this program
                    $studentsInProgram = (int) ($studentsPerFaculty / count($this->programs[$facultyName][$deptName]));

                    for ($i = 1; $i <= $studentsInProgram; $i++) {
                        $angkatan = $angkatanRange[array_rand($angkatanRange)];
                        $studentNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
                        $studentId = "{$facultyCode}{$deptCode}{$progCode}{$studentNumber}";

                        $student = Student::create([
                            'student_id' => $studentId,
                            'name' => "Mahasiswa {$program['name']} {$i}",
                            'email' => "student.{$studentId}@students.unpatti.ac.id",
                            'faculty' => $facultyName,
                            'faculty_id' => $facultyData['id'],
                            'department' => $deptName,
                            'department_id' => $deptData['id'],
                            'program' => $program['name'],
                            'program_study' => $program['name'],
                            'program_study_id' => $program['id'],
                            'program_study_code' => $program['code'],
                            'angkatan' => $angkatan,
                        ]);

                        $this->students[] = $student;
                        $studentCount++;
                    }
                    
                    $progCounter++; // Increment program counter
                }
                
                $deptCounter++; // Increment department counter
            }
            
            $facultyCounter++; // Increment faculty counter only for faculties with departments
        }

        $this->command->info("   ✓ Created {$studentCount} students");
    }

    /**
     * Seed SK Documents
     */
    private function seedSKDocuments(): void
    {
        $this->command->info('📄 Seeding SK documents...');

        $index = 0;
        foreach ($this->faculties as $facultyName => $facultyData) {
            // SK per fakultas
            for ($i = 1; $i <= 3; $i++) {
                $year = 2025;
                $number = str_pad(($index * 10) + $i, 3, '0', STR_PAD_LEFT);

                $sk = SKDocument::create([
                    'sk_number' => "SK/{$number}/UN13.{$index}/{$year}",
                    'title' => "SK Prestasi Mahasiswa {$facultyName} Periode {$i}",
                    'issued_date' => now()->subDays(rand(30, 90)),
                    'file_path' => "sk_documents/SK_{$number}_{$year}.pdf",
                    'issued_by' => 'Rektor Universitas Pattimura',
                    'created_by' => $this->users['admin']->id,
                ]);

                $this->skDocuments[$facultyName][] = $sk;
            }
            $index++;
        }

        $this->command->info('   ✓ Created '.SKDocument::count().' SK documents');
    }

    /**
     * Seed student achievements dengan 2-stage validation
     */
    private function seedStudentAchievements(): void
    {
        $this->command->info('🏅 Seeding student achievements with 2-stage validation...');

        $achievements = Achievement::all();
        $levels = ['Universitas', 'Nasional', 'Internasional'];
        $statuses = [
            'Menunggu' => 30,              // 30% pending
            'faculty_approved' => 25,      // 25% approved by faculty
            'faculty_rejected' => 10,      // 10% rejected by faculty
            'university_approved' => 25,   // 25% fully approved
            'university_rejected' => 10,   // 10% rejected by university
        ];

        $achievementCount = 0;
        $studentsToProcess = array_slice($this->students, 0, 200); // Process 200 students

        foreach ($studentsToProcess as $student) {
            // Each student gets 1-3 achievements
            $numAchievements = rand(1, 3);

            for ($i = 0; $i < $numAchievements; $i++) {
                $achievement = $achievements->random();
                $level = $levels[array_rand($levels)];
                $status = $this->getRandomStatus($statuses);

                // Get faculty operator and pimpinan
                $facultyCode = $this->getFacultyCode($student->faculty);
                $operator = $this->users["operator_{$facultyCode}"] ?? null;
                $pimpinan = $this->users["pimpinan_{$facultyCode}"] ?? null;

                // Create student achievement
                $sa = StudentAchievement::create([
                    'student_id' => $student->student_id,
                    'achievement_id' => $achievement->id,
                    'event_name' => $achievement->name.' '.date('Y'),
                    'level' => $level,
                    'organizer' => $this->getRandomOrganizer($level),
                    'event_date' => now()->subDays(rand(30, 365)),
                    'ranking' => $this->getRandomRanking(),
                    'description' => "Prestasi {$achievement->name} tingkat {$level}",
                    'certificate' => "certificates/cert_{$student->student_id}_{$i}.pdf",
                    'validation_status' => $status,
                    'submitted_by' => rand(0, 1) ? 'student' : 'validator',
                    'submitted_at' => now()->subDays(rand(1, 60)),
                    'sk_required' => $level !== 'Universitas',
                ]);

                // Add validation logs based on status
                $this->addValidationLogs($sa, $status, $operator, $pimpinan);

                // Assign SK if fully approved
                if ($status === 'university_approved' && $sa->sk_required) {
                    $this->assignSK($sa, $student->faculty);
                }

                $achievementCount++;
            }
        }

        $this->command->info("   ✓ Created {$achievementCount} student achievements");
    }

    /**
     * Add validation logs based on status
     */
    private function addValidationLogs($sa, $status, $operator, $pimpinan): void
    {
        switch ($status) {
            case 'faculty_approved':
                // Faculty approval
                if ($operator) {
                    ValidationLog::create([
                        'sa_id' => $sa->sa_id,
                        'validator_id' => $operator->id,
                        'validation_level' => 'faculty',
                        'old_status' => 'Menunggu',
                        'new_status' => 'faculty_approved',
                        'notes' => 'Disetujui oleh operator fakultas',
                        'validated_at' => now()->subDays(rand(1, 30)),
                    ]);
                }
                break;

            case 'faculty_rejected':
                // Faculty rejection
                if ($operator) {
                    ValidationLog::create([
                        'sa_id' => $sa->sa_id,
                        'validator_id' => $operator->id,
                        'validation_level' => 'faculty',
                        'old_status' => 'Menunggu',
                        'new_status' => 'faculty_rejected',
                        'notes' => 'Dokumen tidak lengkap atau tidak sesuai',
                        'validated_at' => now()->subDays(rand(1, 30)),
                    ]);
                }
                break;

            case 'university_approved':
                // Faculty approval
                if ($operator) {
                    ValidationLog::create([
                        'sa_id' => $sa->sa_id,
                        'validator_id' => $operator->id,
                        'validation_level' => 'faculty',
                        'old_status' => 'Menunggu',
                        'new_status' => 'faculty_approved',
                        'notes' => 'Disetujui oleh operator fakultas',
                        'validated_at' => now()->subDays(rand(15, 45)),
                    ]);
                }
                // University approval
                ValidationLog::create([
                    'sa_id' => $sa->sa_id,
                    'validator_id' => $this->users['admin']->id,
                    'validation_level' => 'university',
                    'old_status' => 'faculty_approved',
                    'new_status' => 'university_approved',
                    'notes' => 'Disetujui oleh admin universitas',
                    'validated_at' => now()->subDays(rand(1, 14)),
                ]);
                break;

            case 'university_rejected':
                // Faculty approval
                if ($operator) {
                    ValidationLog::create([
                        'sa_id' => $sa->sa_id,
                        'validator_id' => $operator->id,
                        'validation_level' => 'faculty',
                        'old_status' => 'Menunggu',
                        'new_status' => 'faculty_approved',
                        'notes' => 'Disetujui oleh operator fakultas',
                        'validated_at' => now()->subDays(rand(15, 45)),
                    ]);
                }
                // University rejection
                ValidationLog::create([
                    'sa_id' => $sa->sa_id,
                    'validator_id' => $this->users['admin']->id,
                    'validation_level' => 'university',
                    'old_status' => 'faculty_approved',
                    'new_status' => 'university_rejected',
                    'notes' => 'Tidak memenuhi kriteria universitas',
                    'validated_at' => now()->subDays(rand(1, 14)),
                ]);
                break;
        }
    }

    /**
     * Assign SK to achievement
     */
    private function assignSK($sa, $faculty): void
    {
        if (isset($this->skDocuments[$faculty]) && count($this->skDocuments[$faculty]) > 0) {
            $sk = $this->skDocuments[$faculty][array_rand($this->skDocuments[$faculty])];

            \App\Models\SKAssignment::create([
                'sk_id' => $sk->id,
                'sa_id' => $sa->sa_id,
                'assigned_by' => $this->users['admin']->id,
                'assigned_at' => now()->subDays(rand(1, 14)),
            ]);
        }
    }

    /**
     * Get random status based on distribution
     */
    private function getRandomStatus(array $statuses): string
    {
        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($statuses as $status => $percentage) {
            $cumulative += $percentage;
            if ($rand <= $cumulative) {
                return $status;
            }
        }

        return 'Menunggu';
    }

    /**
     * Get random organizer based on level
     */
    private function getRandomOrganizer(string $level): string
    {
        $organizers = [
            'Universitas' => ['Universitas Pattimura', 'BEM Unpatti', 'Himpunan Mahasiswa'],
            'Nasional' => ['Kementerian Pendidikan', 'Dikti', 'RISTEKDIKTI', 'Kemendikbud'],
            'Internasional' => ['UNESCO', 'IEEE', 'ACM', 'International Committee'],
        ];

        $list = $organizers[$level] ?? $organizers['Universitas'];
        return $list[array_rand($list)];
    }

    /**
     * Get random ranking
     */
    private function getRandomRanking(): ?string
    {
        $rankings = ['Juara 1', 'Juara 2', 'Juara 3', 'Finalis', 'Best Presenter', null];
        return $rankings[array_rand($rankings)];
    }

    /**
     * Get faculty code
     */
    private function getFacultyCode(string $faculty): string
    {
        $codes = [
            'Fakultas Teknik' => 'ft',
            'Fakultas Ekonomi dan Bisnis' => 'feb',
            'Fakultas Hukum' => 'fh',
            'Fakultas Kedokteran' => 'fk',
            'Fakultas Pertanian' => 'fp',
            'Fakultas Perikanan dan Ilmu Kelautan' => 'fpik',
            'Fakultas Keguruan dan Ilmu Pendidikan' => 'fkip',
            'Fakultas Matematika dan Ilmu Pengetahuan Alam' => 'fmipa',
            'Fakultas Ilmu Sosial dan Ilmu Politik' => 'fisip',
        ];

        return $codes[$faculty] ?? strtolower(Str::slug($faculty));
    }

    /**
     * Print summary
     */
    private function printSummary(): void
    {
        $this->command->info("\n".str_repeat('=', 70));
        $this->command->info('📊 SEEDING SUMMARY');
        $this->command->info(str_repeat('=', 70));
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Faculties', count($this->faculties)],
                ['Academic Periods', AcademicPeriod::count()],
                ['Achievement Categories', AchievementCategory::count()],
                ['Achievement Levels', AchievementLevel::count()],
                ['Achievement Types', Achievement::count()],
                ['Users (Admin, Operator, Pimpinan)', User::count()],
                ['Students', Student::count()],
                ['SK Documents', SKDocument::count()],
                ['Student Achievements', StudentAchievement::count()],
                ['Validation Logs', ValidationLog::count()],
            ]
        );

        $this->command->info("\n🔑 LOGIN CREDENTIALS:");
        $this->command->info("   Super Admin : superadmin@unpatti.ac.id / password");
        $this->command->info("   Admin       : admin@unpatti.ac.id / password");
        $this->command->info("   Operator FT : operator.ft@unpatti.ac.id / password");
        $this->command->info("   Dekan FT    : dekan.ft@unpatti.ac.id / password");
        $this->command->info("   Pattern     : operator.{faculty_code}@unpatti.ac.id");
        $this->command->info(str_repeat('=', 70)."\n");
    }
}
