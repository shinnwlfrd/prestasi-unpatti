<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AchievementDocument;
use App\Models\AchievementLevel;
use App\Models\SKAssignment;
use App\Models\SKDocument;
use App\Models\SikadCredential;
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
    private array $skDocuments = [];
    private array $operatorsByFaculty = [];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Complete System Seeder...');
        $this->command->info('');

        // Disable foreign key checks for PostgreSQL
        if (config('database.default') === 'pgsql') {
            \DB::statement("SET session_replication_role = 'replica';");
        }

        // 1. Load SIGAP hierarchy
        $this->loadSigapHierarchy();

        // 2. Seed master data
        $this->seedAcademicPeriods();
        $this->seedAchievementCategories();
        $this->seedAchievementLevels();
        $this->seedAchievements();

        // Re-enable foreign key checks for PostgreSQL
        if (config('database.default') === 'pgsql') {
            \DB::statement("SET session_replication_role = 'origin';");
        }

        // 3. Seed users (admin, operators, pimpinan)
        $this->seedUsers();

        // 4. Seed students
        $this->seedStudents();
        $this->seedTestStudent();

        // 5. Seed SK Documents
        $this->seedSKDocuments();

        // 6. Seed student achievements with full 2-stage validation data
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

        $this->command->info("   ✓ Loaded " . count($this->faculties) . " faculties");
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
            AcademicPeriod::updateOrCreate(['code' => $period['code']], $period);
        }

        $this->command->info('   ✓ Created ' . count($periods) . ' academic periods');
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
            AchievementCategory::updateOrCreate(['name' => $category['name']], $category);
        }

        $this->command->info('   ✓ Created ' . count($categories) . ' categories');
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
            AchievementLevel::updateOrCreate(['name' => $level['name']], $level);
        }

        $this->command->info('   ✓ Created ' . count($levels) . ' levels');
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
                    Achievement::updateOrCreate(
                        ['name' => $name, 'category_id' => $category->id],
                        ['description' => "Prestasi {$name} dalam kategori {$category->name}"]
                    );
                }
            }
        }

        $this->command->info('   ✓ Created ' . Achievement::count() . ' achievements');
    }

    /**
     * Seed users (admin, operators, pimpinan) - OPTIMIZED
     */
    private function seedUsers(): void
    {
        $this->command->info('👥 Seeding users (optimized)...');

        // Pre-hash password once for all users
        $passwordHash = Hash::make('password');

        // Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@unpatti.ac.id'],
            [
                'name' => 'Super Admin',
                'password' => $passwordHash,
                'role' => 'Admin',
            ]
        );
        UserRole::updateOrCreate(
            ['user_id' => $superAdmin->id, 'role' => 'super_admin'],
            [
                'level' => 'university',
                'is_active' => true,
            ]
        );
        $this->users['super_admin'] = $superAdmin;

        // Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@unpatti.ac.id'],
            [
                'name' => 'Admin Universitas',
                'password' => $passwordHash,
                'role' => 'Admin',
            ]
        );
        UserRole::updateOrCreate(
            ['user_id' => $admin->id, 'role' => 'admin'],
            [
                'level' => 'university',
                'is_active' => true,
            ]
        );
        $this->users['admin'] = $admin;

        // Rektor
        $rektor = User::updateOrCreate(
            ['email' => 'rektor@unpatti.ac.id'],
            [
                'name' => 'Prof. Dr. Rektor Unpatti',
                'password' => $passwordHash,
                'role' => 'Pimpinan',
            ]
        );
        UserRole::updateOrCreate(
            ['user_id' => $rektor->id, 'role' => 'pimpinan'],
            [
                'level' => 'university',
                'position' => 'rektor',
                'is_active' => true,
            ]
        );
        $this->users['rektor'] = $rektor;

        // Wakil Rektor I (Bidang Akademik)
        $warek1 = User::updateOrCreate(
            ['email' => 'warek1@unpatti.ac.id'],
            [
                'name' => 'Prof. Dr. Wakil Rektor I',
                'password' => $passwordHash,
                'role' => 'Pimpinan',
            ]
        );
        UserRole::updateOrCreate(
            ['user_id' => $warek1->id, 'role' => 'pimpinan'],
            [
                'level' => 'university',
                'position' => 'wakil_rektor_1',
                'is_active' => true,
            ]
        );
        $this->users['warek1'] = $warek1;

        // Wakil Rektor II (Bidang Umum dan Keuangan)
        $warek2 = User::updateOrCreate(
            ['email' => 'warek2@unpatti.ac.id'],
            [
                'name' => 'Dr. Wakil Rektor II',
                'password' => $passwordHash,
                'role' => 'Pimpinan',
            ]
        );
        UserRole::updateOrCreate(
            ['user_id' => $warek2->id, 'role' => 'pimpinan'],
            [
                'level' => 'university',
                'position' => 'wakil_rektor_2',
                'is_active' => true,
            ]
        );
        $this->users['warek2'] = $warek2;

        // Wakil Rektor III (Bidang Kemahasiswaan)
        $warek3 = User::updateOrCreate(
            ['email' => 'warek3@unpatti.ac.id'],
            [
                'name' => 'Dr. Wakil Rektor III',
                'password' => $passwordHash,
                'role' => 'Pimpinan',
            ]
        );
        UserRole::updateOrCreate(
            ['user_id' => $warek3->id, 'role' => 'pimpinan'],
            [
                'level' => 'university',
                'position' => 'wakil_rektor_3',
                'is_active' => true,
            ]
        );
        $this->users['warek3'] = $warek3;

        // Direktur Pascasarjana
        $dirpps = User::updateOrCreate(
            ['email' => 'direktur.pps@unpatti.ac.id'],
            [
                'name' => 'Prof. Dr. Direktur Pascasarjana',
                'password' => $passwordHash,
                'role' => 'Pimpinan',
            ]
        );
        UserRole::updateOrCreate(
            ['user_id' => $dirpps->id, 'role' => 'pimpinan'],
            [
                'level' => 'university',
                'position' => 'direktur_pps',
                'is_active' => true,
            ]
        );
        $this->users['direktur_pps'] = $dirpps;

        // Kepala Biro Kemahasiswaan
        $kabiro = User::updateOrCreate(
            ['email' => 'kabiro.kemahasiswaan@unpatti.ac.id'],
            [
                'name' => 'Kepala Biro Kemahasiswaan',
                'password' => $passwordHash,
                'role' => 'Pimpinan',
            ]
        );
        UserRole::updateOrCreate(
            ['user_id' => $kabiro->id, 'role' => 'pimpinan'],
            [
                'level' => 'university',
                'position' => 'kepala_biro_kemahasiswaan',
                'is_active' => true,
            ]
        );
        $this->users['kabiro_kemahasiswaan'] = $kabiro;

        // Create operators and pimpinan for each faculty
        $globalProgCounter = 1;
        foreach ($this->faculties as $facultyName => $facultyData) {
            $facultyCode = $this->getFacultyCode($facultyName);

            // Operator Fakultas
            $operator = User::updateOrCreate(
                ['email' => "operator.{$facultyCode}@unpatti.ac.id"],
                [
                    'name' => "Operator {$facultyName}",
                    'password' => $passwordHash,
                    'role' => 'Validator',
                    'faculty' => $facultyName,
                    'faculty_id' => $facultyData['id'],
                ]
            );
            UserRole::updateOrCreate(
                ['user_id' => $operator->id, 'role' => 'operator'],
                [
                    'level' => 'faculty',
                    'faculty_id' => $facultyData['id'],
                    'faculty_name' => $facultyName,
                    'is_active' => true,
                ]
            );
            $this->users["operator_{$facultyCode}"] = $operator;
            $this->operatorsByFaculty[$facultyName] = $operator;

            // Pimpinan Fakultas (Dekan)
            $pimpinan = User::updateOrCreate(
                ['email' => "dekan.{$facultyCode}@unpatti.ac.id"],
                [
                    'name' => "Dekan {$facultyName}",
                    'password' => $passwordHash,
                    'role' => 'Pimpinan',
                    'faculty' => $facultyName,
                    'faculty_id' => $facultyData['id'],
                ]
            );
            UserRole::updateOrCreate(
                ['user_id' => $pimpinan->id, 'role' => 'pimpinan'],
                [
                    'level' => 'faculty',
                    'faculty_id' => $facultyData['id'],
                    'faculty_name' => $facultyName,
                    'position' => 'dekan',
                    'is_active' => true,
                ]
            );
            $this->users["pimpinan_{$facultyCode}"] = $pimpinan;

            // Create Ketua Jurusan and Kaprodi for each department
            if (isset($this->departments[$facultyName])) {
                $deptCounter = 1;
                foreach ($this->departments[$facultyName] as $deptName => $deptData) {
                    $deptCode = strtolower(str_replace([' ', '-', '.'], '', substr($deptName, 0, 3)));
                    $uniqueDeptCode = "{$deptCode}{$deptCounter}";

                    // Ketua Jurusan
                    $kajur = User::updateOrCreate(
                        ['email' => "kajur.{$uniqueDeptCode}.{$facultyCode}@unpatti.ac.id"],
                        [
                            'name' => "Ketua Jurusan {$deptName}",
                            'password' => $passwordHash,
                            'role' => 'Pimpinan',
                            'faculty' => $facultyName,
                            'faculty_id' => $facultyData['id'],
                        ]
                    );
                    UserRole::updateOrCreate(
                        ['user_id' => $kajur->id, 'role' => 'pimpinan', 'department_id' => $deptData['id']],
                        [
                            'level' => 'department',
                            'faculty_id' => $facultyData['id'],
                            'faculty_name' => $facultyName,
                            'department_name' => $deptName,
                            'position' => 'ketua_jurusan',
                            'is_active' => true,
                        ]
                    );
                    $this->users["kajur_{$facultyCode}_{$uniqueDeptCode}"] = $kajur;

                    // Create Kaprodi for each program in this department
                    if (isset($this->programs[$facultyName][$deptName])) {
                        foreach ($this->programs[$facultyName][$deptName] as $program) {
                            $progCode = strtolower(str_replace([' ', '-', '.'], '', substr($program['name'], 0, 5)));

                            $kaprodi = User::updateOrCreate(
                                ['email' => "kaprodi.{$progCode}{$globalProgCounter}@unpatti.ac.id"],
                                [
                                    'name' => "Kepala {$program['name']}",
                                    'password' => $passwordHash,
                                    'role' => 'Pimpinan',
                                    'faculty' => $facultyName,
                                    'faculty_id' => $facultyData['id'],
                                ]
                            );
                            UserRole::updateOrCreate(
                                ['user_id' => $kaprodi->id, 'role' => 'pimpinan', 'program_study_id' => $program['id']],
                                [
                                    'level' => 'program_study',
                                    'faculty_id' => $facultyData['id'],
                                    'faculty_name' => $facultyName,
                                    'department_id' => $deptData['id'],
                                    'department_name' => $deptName,
                                    'program_study_name' => $program['name'],
                                    'position' => 'kaprodi',
                                    'is_active' => true,
                                ]
                            );
                            $this->users["kaprodi_{$progCode}{$globalProgCounter}"] = $kaprodi;
                            $globalProgCounter++;
                        }
                    }
                    $deptCounter++;
                }
            }
        }

        $this->command->info('   ✓ Created ' . User::count() . ' users');
    }

    /**
     * Seed students (batch insert for performance)
     */
    private function seedStudents(): void
    {
        $this->command->info('🎓 Seeding students (optimized batch insert)...');

        $studentsPerFaculty = 50;
        $angkatanRange = [2021, 2022, 2023, 2024, 2025];
        $studentCount = 0;
        $facultyCounter = 1;

        $passwordHash = Hash::make('password');
        $studentsBatch = [];
        $credentialsBatch = [];
        $batchSize = 100;

        foreach ($this->faculties as $facultyName => $facultyData) {
            if (!isset($this->departments[$facultyName]) || empty($this->departments[$facultyName])) {
                continue;
            }

            $facultyCode = str_pad($facultyCounter, 2, '0', STR_PAD_LEFT);
            $deptCounter = 1;

            foreach ($this->departments[$facultyName] as $deptName => $deptData) {
                if (!isset($this->programs[$facultyName][$deptName])) {
                    continue;
                }

                $deptCode = str_pad($deptCounter, 2, '0', STR_PAD_LEFT);
                $progCounter = 1;

                foreach ($this->programs[$facultyName][$deptName] as $program) {
                    $progCode = str_pad($progCounter, 2, '0', STR_PAD_LEFT);
                    $studentsInProgram = (int) ($studentsPerFaculty / count($this->programs[$facultyName][$deptName]));

                    for ($i = 1; $i <= $studentsInProgram; $i++) {
                        $angkatan = $angkatanRange[array_rand($angkatanRange)];
                        $studentNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
                        $studentId = "{$facultyCode}{$deptCode}{$progCode}{$studentNumber}";
                        $now = now();

                        $studentsBatch[] = [
                            'student_id' => $studentId,
                            'name' => "Mahasiswa {$program['name']} {$i}",
                            'email' => "student.{$studentId}@students.unpatti.ac.id",
                            'faculty' => $facultyName,
                            'faculty_id' => $facultyData['id'],
                            'department' => $deptName,
                            'department_id' => $deptData['id'],
                            'program_study' => $program['name'],
                            'program_study_id' => $program['id'],
                            'angkatan' => $angkatan,
                            'gpa' => number_format(rand(250, 400) / 100, 2),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        $credentialsBatch[] = [
                            'student_id' => $studentId,
                            'password_hash' => $passwordHash,
                            'is_active' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        $studentCount++;

                        if (count($studentsBatch) >= $batchSize) {
                            $this->insertStudentBatch($studentsBatch, $credentialsBatch);
                            $studentsBatch = [];
                            $credentialsBatch = [];
                        }
                    }

                    $progCounter++;
                }

                $deptCounter++;
            }

            $facultyCounter++;
        }

        if (!empty($studentsBatch)) {
            $this->insertStudentBatch($studentsBatch, $credentialsBatch);
        }

        $this->command->info("   ✓ Created {$studentCount} students");
    }

    /**
     * Insert student batch with upsert to handle duplicates
     */
    private function insertStudentBatch(array $students, array $credentials): void
    {
        Student::upsert(
            $students,
            ['student_id'],
            [
                'name',
                'email',
                'faculty',
                'faculty_id',
                'department',
                'department_id',
                'program_study',
                'program_study_id',
                'angkatan',
                'gpa',
                'updated_at'
            ]
        );

        SikadCredential::upsert(
            $credentials,
            ['student_id'],
            ['password_hash', 'is_active', 'updated_at']
        );

        foreach ($students as $studentData) {
            $this->students[] = (object) $studentData;
        }
    }

    /**
     * Seed a specific test student account
     */
    private function seedTestStudent(): void
    {
        $this->command->info('🧪 Seeding test student account...');

        $studentId = '20240101';
        $student = Student::updateOrCreate(
            ['student_id' => $studentId],
            [
                'name' => 'Mahasiswa Test Unpatti',
                'email' => 'mahasiswa@unpatti.ac.id',
                'faculty' => 'Fakultas Hukum',
                'faculty_id' => 'a4ed52fa-8dba-4f0b-a2d5-a93557e2ac62',
                'department' => 'Jurusan Ilmu Hukum',
                'department_id' => '7c40db5c-0765-49d5-9147-f57c16f8cfa2',
                'program_study' => 'Program Studi Ilmu Hukum',
                'program_study_id' => 'c8fa82ea-ce38-47f9-be29-c9ecca2e6ae0',
                'angkatan' => 2024,
                'gpa' => 3.75,
            ]
        );

        SikadCredential::updateOrCreate(
            ['student_id' => $studentId],
            [
                'password_hash' => Hash::make('password'),
                'is_active' => true
            ]
        );

        $this->students[] = $student;
        $this->command->info('   ✓ Test student created: NIM: 20240101, Password: password');
    }

    /**
     * Seed SK Documents
     */
    private function seedSKDocuments(): void
    {
        $this->command->info('📄 Seeding SK documents...');

        $index = 0;
        foreach ($this->faculties as $facultyName => $facultyData) {
            for ($i = 1; $i <= 3; $i++) {
                $year = 2025;
                $number = str_pad(($index * 10) + $i, 3, '0', STR_PAD_LEFT);

                $sk = SKDocument::updateOrCreate(
                    ['sk_number' => "SK/{$number}/UN13.{$index}/{$year}"],
                    [
                        'title' => "SK Prestasi Mahasiswa {$facultyName} Periode {$i}",
                        'issued_date' => now()->subDays(rand(30, 90)),
                        'file_path' => "sk_documents/SK_{$number}_{$year}.pdf",
                        'issued_by' => 'Rektor Universitas Pattimura',
                        'created_by' => $this->users['admin']->id,
                    ]
                );

                $this->skDocuments[$facultyName][] = $sk;
            }
            $index++;
        }

        $this->command->info('   ✓ Created ' . SKDocument::count() . ' SK documents');
    }

    /**
     * Seed student achievements with FULL 2-stage validation data.
     * Creates validation logs, SK assignments, documents, and anomaly data.
     */
    private function seedStudentAchievements(): void
    {
        $this->command->info('🏅 Seeding student achievements with full validation data...');

        $achievements = Achievement::all()->toArray();
        $levels = ['Universitas', 'Nasional', 'Internasional'];

        // Realistic status distribution (weights out of 100)
        $statusWeights = [
            'draft' => 5,
            'submitted' => 5,
            'Menunggu' => 10,
            'faculty_review' => 5,
            'faculty_approved' => 15,
            'faculty_rejected' => 7,
            'faculty_revision' => 3,
            'university_review' => 5,
            'university_approved' => 30,
            'university_rejected' => 5,
            'appeal_submitted' => 3,
            'appeal_approved' => 4,
            'appeal_rejected' => 3,
        ];

        $periods = AcademicPeriod::orderBy('start_date', 'desc')->get();
        if ($periods->isEmpty()) {
            $this->command->warn('   ⚠ No academic periods found. Skipping achievements.');
            return;
        }

        $activePeriod = $periods->firstWhere('is_active', true);
        $inactivePeriods = $periods->where('is_active', false);

        $achievementCount = 0;
        $studentsToProcess = array_slice($this->students, 0, 120);
        $adminUser = $this->users['admin'];

        // Track for duplicate anomalies (will create exact duplicates for 5 students)
        $duplicateStudents = array_slice($studentsToProcess, 0, 5);

        foreach ($studentsToProcess as $student) {
            $studentId = is_object($student) ? $student->student_id : $student['student_id'];
            $faculty = is_object($student) ? $student->faculty : $student['faculty'];
            $operator = $this->operatorsByFaculty[$faculty] ?? null;

            $numAchievements = rand(1, 3);

            for ($i = 0; $i < $numAchievements; $i++) {
                $achievement = $achievements[array_rand($achievements)];
                $level = $levels[array_rand($levels)];
                $status = $this->getRandomStatus($statusWeights);

                // Distribute to periods: 65% active, 35% inactive
                $selectedPeriod = null;
                if ($activePeriod && rand(1, 100) <= 65) {
                    $selectedPeriod = $activePeriod;
                } elseif ($inactivePeriods->isNotEmpty()) {
                    $selectedPeriod = $inactivePeriods->random();
                } else {
                    $selectedPeriod = $periods->first();
                }

                // Calculate realistic dates based on period
                $periodStart = \Carbon\Carbon::parse($selectedPeriod->start_date);
                $periodEnd = \Carbon\Carbon::parse($selectedPeriod->end_date);
                $eventDate = $periodStart->copy()->addDays(rand(0, max(0, $periodStart->diffInDays($periodEnd))));

                // Submitted date after event for non-draft statuses
                $submittedAt = null;
                if ($status !== 'draft') {
                    $submittedAt = $eventDate->copy()->addDays(rand(1, 14));
                }

                // Determine validation stage and current_stage based on status
                [$validationStage, $currentStage] = $this->getStageForStatus($status);

                // Determine validators and dates based on status
                $facultyValidatorId = null;
                $facultyValidatedAt = null;
                $facultyNotes = null;
                $universityValidatorId = null;
                $universityValidatedAt = null;
                $universityNotes = null;
                $skRequired = $level !== 'Universitas';

                if (in_array($status, ['faculty_approved', 'faculty_rejected', 'faculty_revision', 'university_review', 'university_approved', 'university_rejected', 'appeal_submitted', 'appeal_approved', 'appeal_rejected'])) {
                    $facultyValidatorId = $operator ? $operator->id : null;
                    $facultyValidatedAt = $submittedAt ? $submittedAt->copy()->addDays(rand(1, 5)) : null;
                    $facultyNotes = $this->getFacultyNotes($status);
                }

                if (in_array($status, ['university_approved', 'university_rejected', 'appeal_submitted', 'appeal_approved', 'appeal_rejected'])) {
                    $universityValidatorId = $adminUser->id;
                    $universityValidatedAt = $facultyValidatedAt ? $facultyValidatedAt->copy()->addDays(rand(1, 5)) : null;
                    $universityNotes = $this->getUniversityNotes($status);
                }

                // Determine certificate path (some null for missing-docs anomaly)
                $certificate = null;
                if ($status !== 'draft') {
                    $shouldHaveCert = rand(1, 100) <= 85; // 15% will have missing docs
                    if ($shouldHaveCert) {
                        $certificate = "certificates/cert_{$studentId}_{$i}.pdf";
                    }
                }

                // For SLA breach anomaly: some pending items with old submitted_at
                if (in_array($status, ['Menunggu', 'submitted', 'faculty_review']) && $submittedAt) {
                    if (rand(1, 100) <= 40) { // 40% of pending items become SLA breach
                        $submittedAt = now()->subDays(rand(10, 30)); // > 7 days = SLA breach
                    }
                }

                // For abandoned drafts: some drafts with old updated_at
                $createdAt = $submittedAt ?? $eventDate->copy()->addDays(rand(1, 7));
                $updatedAt = $createdAt->copy();
                if ($status === 'draft' && rand(1, 100) <= 60) {
                    $updatedAt = now()->subDays(rand(35, 90)); // > 30 days = abandoned
                    $createdAt = $updatedAt->copy()->subDays(rand(1, 10));
                }

                // Create the achievement record
                $sa = StudentAchievement::create([
                    'student_id' => $studentId,
                    'achievement_id' => $achievement['id'],
                    'academic_period_id' => $selectedPeriod->id,
                    'event_name' => $achievement['name'] . ' ' . $eventDate->format('Y'),
                    'level' => $level,
                    'organizer' => $this->getRandomOrganizer($level),
                    'event_date' => $eventDate,
                    'ranking' => $this->getRandomRanking(),
                    'description' => "Prestasi {$achievement['name']} tingkat {$level}",
                    'certificate' => $certificate,
                    'validation_status' => $status,
                    'validation_stage' => $validationStage,
                    'current_stage' => $currentStage,
                    'submitted_by' => rand(0, 1) ? 'student' : 'validator',
                    'submitted_at' => $submittedAt,
                    'faculty_validator_id' => $facultyValidatorId,
                    'faculty_validated_at' => $facultyValidatedAt,
                    'faculty_notes' => $facultyNotes,
                    'university_validator_id' => $universityValidatorId,
                    'university_validated_at' => $universityValidatedAt,
                    'university_notes' => $universityNotes,
                    'sk_required' => $skRequired,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);

                // Create validation logs based on status
                $this->createValidationLogs($sa, $status, $operator, $adminUser, $submittedAt, $facultyValidatedAt, $universityValidatedAt);

                // Create SK assignment for university_approved
                if ($status === 'university_approved' && $skRequired) {
                    $this->createSKAssignment($sa, $faculty, $universityValidatedAt);
                }

                // Create achievement documents (for non-draft)
                if ($status !== 'draft') {
                    $this->createAchievementDocuments($sa, $status, $certificate);
                }

                $achievementCount++;
            }
        }

        // Create duplicate achievements for anomaly detection
        $this->createDuplicateAnomalies($duplicateStudents, $achievements, $activePeriod);

        // Show distribution
        $this->command->info("   ✓ Created {$achievementCount} student achievements (+ duplicates)");
        foreach ($periods as $period) {
            $count = \DB::table('student_achievements')
                ->where('academic_period_id', $period->id)
                ->count();
            $this->command->info("      - {$period->name}: {$count} achievements");
        }

        $statusDistribution = \DB::table('student_achievements')
            ->select('validation_status', \DB::raw('COUNT(*) as count'))
            ->groupBy('validation_status')
            ->orderByDesc('count')
            ->get();
        $this->command->info('   📊 Status distribution:');
        foreach ($statusDistribution as $row) {
            $this->command->info("      - {$row->validation_status}: {$row->count}");
        }
    }

    /**
     * Create validation logs based on the achievement status
     */
    private function createValidationLogs(
        StudentAchievement $sa,
        string $status,
        ?User $operator,
        User $admin,
        ?\Carbon\Carbon $submittedAt,
        ?\Carbon\Carbon $facultyValidatedAt,
        ?\Carbon\Carbon $universityValidatedAt
    ): void {
        // Faculty-level validation logs
        if (in_array($status, ['faculty_approved', 'university_review', 'university_approved', 'university_rejected', 'appeal_submitted', 'appeal_approved', 'appeal_rejected']) && $operator) {
            ValidationLog::create([
                'sa_id' => $sa->sa_id,
                'validator_id' => $operator->id,
                'old_status' => 'Menunggu',
                'new_status' => 'faculty_approved',
                'notes' => 'Diverifikasi dan disetujui oleh operator fakultas',
                'validation_stage' => 'faculty',
                'validation_level' => 'faculty',
                'is_stage_transition' => true,
                'validated_at' => $facultyValidatedAt ?? now()->subDays(rand(5, 30)),
            ]);
        }

        if ($status === 'faculty_rejected' && $operator) {
            ValidationLog::create([
                'sa_id' => $sa->sa_id,
                'validator_id' => $operator->id,
                'old_status' => 'Menunggu',
                'new_status' => 'faculty_rejected',
                'notes' => 'Dokumen tidak lengkap atau tidak sesuai kriteria',
                'validation_stage' => 'faculty',
                'validation_level' => 'faculty',
                'is_stage_transition' => false,
                'validated_at' => $facultyValidatedAt ?? now()->subDays(rand(5, 30)),
            ]);
        }

        if ($status === 'faculty_revision' && $operator) {
            ValidationLog::create([
                'sa_id' => $sa->sa_id,
                'validator_id' => $operator->id,
                'old_status' => 'Menunggu',
                'new_status' => 'faculty_revision',
                'notes' => 'Perlu revisi pada dokumen pendukung',
                'validation_stage' => 'faculty',
                'validation_level' => 'faculty',
                'is_stage_transition' => false,
                'validated_at' => $facultyValidatedAt ?? now()->subDays(rand(5, 30)),
            ]);
        }

        // University-level validation logs
        if (in_array($status, ['university_approved', 'appeal_submitted', 'appeal_approved', 'appeal_rejected'])) {
            ValidationLog::create([
                'sa_id' => $sa->sa_id,
                'validator_id' => $admin->id,
                'old_status' => 'faculty_approved',
                'new_status' => 'university_approved',
                'notes' => 'Diverifikasi dan disetujui oleh admin universitas',
                'validation_stage' => 'university',
                'validation_level' => 'university',
                'is_stage_transition' => true,
                'validated_at' => $universityValidatedAt ?? now()->subDays(rand(1, 14)),
            ]);
        }

        if ($status === 'university_rejected') {
            // Faculty approved first
            if ($operator) {
                ValidationLog::create([
                    'sa_id' => $sa->sa_id,
                    'validator_id' => $operator->id,
                    'old_status' => 'Menunggu',
                    'new_status' => 'faculty_approved',
                    'notes' => 'Diverifikasi oleh operator fakultas',
                    'validation_stage' => 'faculty',
                    'validation_level' => 'faculty',
                    'is_stage_transition' => true,
                    'validated_at' => $facultyValidatedAt ?? now()->subDays(rand(10, 30)),
                ]);
            }
            // Then university rejected
            ValidationLog::create([
                'sa_id' => $sa->sa_id,
                'validator_id' => $admin->id,
                'old_status' => 'faculty_approved',
                'new_status' => 'university_rejected',
                'notes' => 'Tidak memenuhi kriteria universitas',
                'validation_stage' => 'university',
                'validation_level' => 'university',
                'is_stage_transition' => false,
                'validated_at' => $universityValidatedAt ?? now()->subDays(rand(1, 14)),
            ]);
        }
    }

    /**
     * Create SK assignment for approved achievements
     */
    private function createSKAssignment(StudentAchievement $sa, string $faculty, ?\Carbon\Carbon $approvedAt): void
    {
        if (isset($this->skDocuments[$faculty]) && count($this->skDocuments[$faculty]) > 0) {
            $sk = $this->skDocuments[$faculty][array_rand($this->skDocuments[$faculty])];

            SKAssignment::updateOrCreate(
                [
                    'sk_id' => $sk->id,
                    'sa_id' => $sa->sa_id,
                ],
                [
                    'assigned_by' => $this->users['admin']->id,
                    'assigned_at' => $approvedAt ?? now()->subDays(rand(1, 14)),
                ]
            );
        }
    }

    /**
     * Create achievement documents for an achievement
     */
    private function createAchievementDocuments(StudentAchievement $sa, string $status, ?string $certificate): void
    {
        $docStatus = match ($status) {
            'university_approved', 'appeal_approved' => 'approved',
            'faculty_rejected', 'university_rejected', 'appeal_rejected' => 'rejected',
            'faculty_revision' => 'revision',
            'draft' => 'draft',
            default => 'pending',
        };

        // Always create a certificate document entry
        if ($certificate) {
            AchievementDocument::create([
                'sa_id' => $sa->sa_id,
                'document_type' => 'sertifikat',
                'file_path' => $certificate,
                'file_name' => basename($certificate),
                'file_type' => 'application/pdf',
                'file_size' => rand(50000, 500000),
                'status' => $docStatus,
            ]);
        }

        // 50% chance to have additional documentation photo
        if (rand(0, 1)) {
            AchievementDocument::create([
                'sa_id' => $sa->sa_id,
                'document_type' => 'foto_dokumentasi',
                'file_path' => "documents/foto_{$sa->sa_id}.jpg",
                'file_name' => "foto_{$sa->sa_id}.jpg",
                'file_type' => 'image/jpeg',
                'file_size' => rand(100000, 2000000),
                'status' => $docStatus,
            ]);
        }
    }

    /**
     * Create duplicate achievements for anomaly detection
     */
    private function createDuplicateAnomalies(array $students, array $achievements, ?AcademicPeriod $period): void
    {
        if (!$period || empty($students))
            return;

        $this->command->info('   🔄 Creating duplicate anomalies...');
        $dupCount = 0;

        foreach (array_slice($students, 0, 5) as $student) {
            $studentId = is_object($student) ? $student->student_id : $student['student_id'];
            $faculty = is_object($student) ? $student->faculty : $student['faculty'];

            // Find an existing achievement for this student to duplicate
            $existing = StudentAchievement::where('student_id', $studentId)->first();
            if (!$existing)
                continue;

            // Create a duplicate with same event_name and level
            StudentAchievement::create([
                'student_id' => $studentId,
                'achievement_id' => $existing->achievement_id,
                'academic_period_id' => $period->id,
                'event_name' => $existing->event_name, // Same event_name = duplicate
                'level' => $existing->level,       // Same level = duplicate
                'organizer' => $existing->organizer,
                'event_date' => now()->subDays(rand(10, 60)),
                'ranking' => $existing->ranking,
                'description' => $existing->description,
                'certificate' => "certificates/dup_cert_{$studentId}.pdf",
                'validation_status' => 'Menunggu',
                'validation_stage' => 'faculty',
                'current_stage' => 'faculty',
                'submitted_by' => 'student',
                'submitted_at' => now()->subDays(rand(1, 5)),
                'sk_required' => $existing->level !== 'Universitas',
            ]);
            $dupCount++;
        }

        $this->command->info("   ✓ Created {$dupCount} duplicate anomalies");
    }

    /**
     * Get validation stage and current_stage based on status
     */
    private function getStageForStatus(string $status): array
    {
        return match ($status) {
            'draft', 'submitted', 'Menunggu', 'faculty_review', 'faculty_rejected', 'faculty_revision'
            => ['faculty', 'faculty'],
            'faculty_approved', 'university_review'
            => ['university', 'university'],
            'university_approved', 'university_rejected'
            => ['university', 'completed'],
            'appeal_submitted', 'appeal_approved', 'appeal_rejected'
            => ['appeal', 'appeal'],
            default
            => ['faculty', 'faculty'],
        };
    }

    /**
     * Get faculty validation notes based on status
     */
    private function getFacultyNotes(string $status): ?string
    {
        return match ($status) {
            'faculty_approved', 'university_review', 'university_approved', 'appeal_approved'
            => 'Dokumen lengkap dan valid. Disetujui untuk tahap universitas.',
            'faculty_rejected'
            => 'Dokumen tidak lengkap. Sertifikat asli tidak tersedia.',
            'faculty_revision'
            => 'Perbaiki format penanggalan dan lampirkan foto dokumentasi.',
            'university_rejected'
            => 'Disetujui di tingkat fakultas.',
            'appeal_submitted', 'appeal_rejected'
            => 'Disetujui di tingkat fakultas, ditolak di universitas.',
            default => null,
        };
    }

    /**
     * Get university validation notes based on status
     */
    private function getUniversityNotes(string $status): ?string
    {
        return match ($status) {
            'university_approved', 'appeal_approved'
            => 'Prestasi diverifikasi dan memenuhi kriteria universitas.',
            'university_rejected', 'appeal_submitted', 'appeal_rejected'
            => 'Tidak memenuhi standar minimal prestasi tingkat universitas.',
            default => null,
        };
    }

    /**
     * Get random status based on weighted distribution
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
        $this->command->info("\n" . str_repeat('=', 70));
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
                ['Achievement Documents', AchievementDocument::count()],
                ['Validation Logs', ValidationLog::count()],
                ['SK Assignments', SKAssignment::count()],
            ]
        );

        $this->command->info("\n🔑 LOGIN CREDENTIALS:");
        $this->command->info("   Super Admin : superadmin@unpatti.ac.id / password");
        $this->command->info("   Admin       : admin@unpatti.ac.id / password");
        $this->command->info("   Operator FT : operator.ft@unpatti.ac.id / password");
        $this->command->info("   Dekan FT    : dekan.ft@unpatti.ac.id / password");
        $this->command->info("   Mahasiswa   : NIM: 20240101 / password");
        $this->command->info("   Pattern     : operator.{faculty_code}@unpatti.ac.id");
        $this->command->info(str_repeat('=', 70) . "\n");
    }
}
