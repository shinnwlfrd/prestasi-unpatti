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

        // 1. Buat User Admin (akses Filament)
        User::create([
            'name' => 'Admin Sistem',
            'email' => 'admin@univ.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);

        // 2. Buat User Validator (akses dashboard validator)
        $validator = User::create([
            'name' => 'Validator Prestasi',
            'email' => 'validator@univ.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Validator',
        ]);

        ValidatorProfile::create([
            'user_id' => $validator->id,
            'name' => 'Validator Prestasi',
            'email' => 'validator@univ.ac.id',
            'phone' => '081234567890',
            'department' => 'Bidang Kemahasiswaan',
        ]);

        // 2. Buat Jenis Prestasi
        $achievements = Achievement::insertGetId([
            'name' => 'Juara Lomba Debat Nasional',
            'category' => 'Non-akademik',
        ]);

        Achievement::insertGetId([
            'name' => 'Peneliti Muda Terbaik',
            'category' => 'Akademik',
        ]);

        Achievement::insertGetId([
            'name' => 'Peserta Pertukaran Mahasiswa Internasional',
            'category' => 'Non-akademik',
        ]);

        $achievementIds = Achievement::pluck('id')->toArray();

        // 3. Buat Mahasiswa Testing dengan kredensial tetap
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

        $studentIds = [$testStudent->student_id];

        // Buat mahasiswa random tambahan
        for ($i = 1; $i <= 4; $i++) {
            $nim = '12345678' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $name = $faker->name;
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

            $studentIds[] = $student->student_id;
        }

        // 4. Buat Prestasi Mahasiswa
        $levels = ['Universitas', 'Nasional', 'Internasional'];
        $statuses = ['Menunggu', 'Disetujui', 'Ditolak'];
        $prestasiData = [];

        foreach ($studentIds as $studentId) {
            for ($j = 0; $j < rand(1, 3); $j++) {
                $prestasiData[] = [
                    'student_id' => $studentId,
                    'achievement_id' => $faker->randomElement($achievementIds),
                    'event_name' => $faker->sentence(3),
                    'level' => $faker->randomElement($levels),
                    'organizer' => $faker->company,
                    'event_date' => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                    'description' => $faker->paragraph,
                    'certificate_path' => 'certificates/sample.pdf',
                    'validation_status' => $status = $faker->randomElement($statuses),
                    'validator_id' => in_array($status, ['Disetujui', 'Ditolak']) ? $validator->id : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        StudentAchievement::insert($prestasiData);

        // 5. Buat ValidationLog untuk setiap Approved/Rejected
        $validatedAchievements = StudentAchievement::whereIn('validation_status', ['Disetujui', 'Ditolak'])->get();
        foreach ($validatedAchievements as $ach) {
            ValidationLog::create([
                'sa_id' => $ach->sa_id,
                'validator_id' => $ach->validator_id,
                'old_status' => 'Menunggu',
                'new_status' => $ach->validation_status,
                'validated_at' => now(),
                'notes' => $ach->validation_status === 'Ditolak' ? 'Bukti tidak valid.' : null,
            ]);
        }
    }
}