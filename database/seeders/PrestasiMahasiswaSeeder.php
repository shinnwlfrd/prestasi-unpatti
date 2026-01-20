<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Student;
use App\Models\SikadCredential;
use App\Models\Achievement;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\ValidatorProfile;
use App\Models\ValidationLog;
use Faker\Factory as Faker;

class PrestasiMahasiswaSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');

        // 1. Buat User Admin
        User::firstOrCreate(
            ['email' => 'admin@unpatti.ac.id'],
            [
                'name' => 'Admin Sistem',
                'password' => Hash::make('password'),
                'role' => 'Admin',
            ]
        );

        // 2. Buat User Validator
        $validator = User::firstOrCreate(
            ['email' => 'validator@unpatti.ac.id'],
            [
                'name' => 'Validator Prestasi',
                'password' => Hash::make('password'),
                'role' => 'Validator',
            ]
        );

        ValidatorProfile::create([
            'user_id' => $validator->id,
            'name' => 'Validator Prestasi',
            'email' => 'validator@univ.ac.id',
            'phone' => '081234567890',
            'department' => 'Bidang Kemahasiswaan',
        ]);


        // 3. Buat 2 Kategori Prestasi
        $akademik = Achievement::create(['category' => 'Akademik']);
        $nonAkademik = Achievement::create(['category' => 'Non-Akademik']);

        // 4. Buat Mahasiswa Test
        $testStudent = Student::create([
            'student_id' => '2021001001',
            'name' => 'Mahasiswa Test',
            'faculty' => 'Fakultas Teknik',
            'program_study' => 'Teknik Informatika',
            'semester' => 5,
            'gpa' => '3.75',
            'email' => 'mahasiswa@student.univ.ac.id',
        ]);

        SikadCredential::create([
            'student_id' => '2021001001',
            'password_hash' => Hash::make('password'),
            'last_login' => now(),
            'is_active' => true,
        ]);

        $students = [$testStudent];

        // 5. Buat 4 mahasiswa tambahan
        for ($i = 1; $i <= 4; $i++) {
            $nim = '2021001' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            $name = $faker->name();
            $email = strtolower(str_replace(' ', '.', $name)) . '@student.univ.ac.id';

            $student = Student::create([
                'student_id' => $nim,
                'name' => $name,
                'faculty' => 'Fakultas Teknik',
                'program_study' => 'Teknik Informatika',
                'semester' => rand(3, 8),
                'gpa' => number_format($faker->randomFloat(2, 2.5, 4.0), 2),
                'email' => $email,
            ]);

            SikadCredential::create([
                'student_id' => $nim,
                'password_hash' => Hash::make('password'),
                'last_login' => now(),
                'is_active' => true,
            ]);

            $students[] = $student;
        }

        // 6. Buat Prestasi Mahasiswa
        $levels = ['Universitas', 'Nasional', 'Internasional'];
        $statuses = ['Menunggu', 'Disetujui', 'Ditolak'];
        $achievements = [$akademik, $nonAkademik];

        $akademikEvents = [
            'Publikasi Jurnal Internasional',
            'Penelitian Terbaik Bidang Teknologi',
            'Beasiswa Akademik Penuh',
            'Presentasi Paper di Konferensi Nasional',
            'Penghargaan Akademik Terbaik',
        ];

        $nonAkademikEvents = [
            'Juara Lomba Debat Nasional',
            'Peserta Pertukaran Mahasiswa Internasional',
            'Juara Kompetisi Olahraga Nasional',
            'Juara Kompetisi Robotika Internasional',
            'Peserta Program Magang Luar Negeri',
            'Juara Lomba Inovasi Teknologi',
        ];

        foreach ($students as $student) {
            // 2-3 prestasi per mahasiswa
            for ($j = 0; $j < rand(2, 3); $j++) {
                $achievement = $faker->randomElement($achievements);
                $eventList = $achievement->id === $akademik->id ? $akademikEvents : $nonAkademikEvents;
                $eventName = $faker->randomElement($eventList);
                $status = $faker->randomElement($statuses);

                StudentAchievement::create([
                    'student_id' => $student->student_id,
                    'achievement_id' => $achievement->id,
                    'event_name' => $eventName,
                    'level' => $faker->randomElement($levels),
                    'organizer' => $faker->company(),
                    'event_date' => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                    'description' => $faker->paragraph(),
                    'certificate' => 'certificates/sample.pdf',
                    'validation_status' => $status,
                    'validator_id' => \in_array($status, ['Disetujui', 'Ditolak']) ? $validator->id : null,
                    'submitted_by' => 'student',
                    'submitted_at' => now(),
                ]);
            }
        }

        // 7. Buat ValidationLog untuk prestasi yang sudah divalidasi
        $validatedAchievements = StudentAchievement::whereIn('validation_status', ['Disetujui', 'Ditolak'])->get();
        foreach ($validatedAchievements as $ach) {
            ValidationLog::create([
                'sa_id' => $ach->sa_id,
                'validator_id' => $ach->validator_id,
                'old_status' => 'Menunggu',
                'new_status' => $ach->validation_status,
                'sk_document' => 'sk_documents/sample.pdf',
                'validated_at' => now(),
                'notes' => $ach->validation_status === 'Ditolak' ? 'Bukti tidak lengkap atau tidak sesuai kriteria.' : null,
            ]);
        }
    }
}
