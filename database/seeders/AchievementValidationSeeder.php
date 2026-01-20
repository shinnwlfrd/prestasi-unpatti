<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\AchievementDocument;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\ValidationLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AchievementValidationSeeder extends Seeder
{
    public function run(): void
    {
        // Create validator user if not exists
        $validator = User::firstOrCreate(
            ['email' => 'validator@unpatti.ac.id'],
            [
                'name' => 'Validator Prestasi',
                'password' => Hash::make('password'),
                'role' => 'Validator',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create admin user if not exists
        User::firstOrCreate(
            ['email' => 'admin@unpatti.ac.id'],
            [
                'name' => 'Admin Sistem',
                'password' => Hash::make('password'),
                'role' => 'Admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create achievement categories
        $akademik = Achievement::firstOrCreate(['category' => 'Akademik']);
        $nonAkademik = Achievement::firstOrCreate(['category' => 'Non-Akademik']);

        // Sample students
        $students = Student::take(5)->get();
        
        if ($students->isEmpty()) {
            $this->command->info('No students found. Please run StudentSeeder first.');
            return;
        }

        // Ensure sample documents directory exists
        $sampleDir = storage_path('app/public/sample-documents');
        if (!File::exists($sampleDir)) {
            File::makeDirectory($sampleDir, 0755, true);
        }

        // Create a simple sample PDF if it doesn't exist
        $this->createSamplePdfIfNotExists();

        $competitions = [
            ['name' => 'Olimpiade Matematika Nasional', 'level' => 'Nasional', 'organizer' => 'Kemendikbud', 'category' => $akademik->id],
            ['name' => 'Hackathon Indonesia', 'level' => 'Nasional', 'organizer' => 'Kemenkominfo', 'category' => $nonAkademik->id],
            ['name' => 'International Science Competition', 'level' => 'Internasional', 'organizer' => 'UNESCO', 'category' => $akademik->id],
            ['name' => 'Lomba Debat Bahasa Inggris', 'level' => 'Universitas', 'organizer' => 'BEM Unpatti', 'category' => $nonAkademik->id],
            ['name' => 'Programming Contest Asia', 'level' => 'Internasional', 'organizer' => 'ACM ICPC', 'category' => $akademik->id],
            ['name' => 'Kompetisi Bisnis Plan', 'level' => 'Nasional', 'organizer' => 'Kemenparekraf', 'category' => $nonAkademik->id],
            ['name' => 'Lomba Karya Tulis Ilmiah', 'level' => 'Nasional', 'organizer' => 'LIPI', 'category' => $akademik->id],
            ['name' => 'Festival Seni Budaya', 'level' => 'Universitas', 'organizer' => 'Unpatti', 'category' => $nonAkademik->id],
        ];

        $statuses = ['Menunggu', 'Disetujui', 'Ditolak','Revisi'];
        $docStatuses = ['draft', 'pending', 'approved', 'rejected', 'revision'];
        $rankings = ['Juara 1', 'Juara 2', 'Juara 3', 'Finalis', 'Peserta Terbaik'];

        foreach ($students as $student) {
            // Create 2-3 achievements per student
            $numAchievements = rand(2, 3);
            $selectedCompetitions = collect($competitions)->random($numAchievements);

            foreach ($selectedCompetitions as $competition) {
                $status = $statuses[array_rand($statuses)];

                $achievement = StudentAchievement::create([
                    'student_id' => $student->student_id,
                    'achievement_id' => $competition['category'],
                    'event_name' => $competition['name'],
                    'level' => $competition['level'],
                    'organizer' => $competition['organizer'],
                    'event_date' => now()->subDays(rand(30, 365)),
                    'description' => 'Partisipasi dalam ' . $competition['name'] . ' yang diselenggarakan oleh ' . $competition['organizer'],
                    'ranking' => $rankings[array_rand($rankings)],
                    'validation_status' => $status,
                    'submitted_at' => now()->subDays(rand(1, 30)),
                    'validator_id' => $status !== 'pending' ? $validator->id : null,
                ]);

                // Add sample documents with proper status
                $documentTypes = [
                    ['type' => 'sk_resmi', 'name' => 'SK_Resmi_' . $achievement->sa_id . '.pdf'],
                    ['type' => 'sertifikat', 'name' => 'Sertifikat_' . $achievement->sa_id . '.pdf'],
                    ['type' => 'foto_dokumentasi', 'name' => 'Foto_Dokumentasi_' . $achievement->sa_id . '.jpg'],
                ];
                
                $numDocs = rand(1, 3);
                
                for ($i = 0; $i < $numDocs; $i++) {
                    $docType = $documentTypes[$i % count($documentTypes)];
                    
                    // Determine document status based on achievement status
                    $docStatus = match($status) {
                        'approved' => 'approved',
                        'rejected' => rand(0, 1) ? 'rejected' : 'approved',
                        'need_revision' => 'revision',
                        default => collect(['draft', 'pending'])->random(),
                    };

                    // Copy sample file to achievement folder
                    $achievementDir = 'achievements/' . $achievement->sa_id;
                    Storage::disk('public')->makeDirectory($achievementDir);
                    
                    $sampleFile = 'sample-documents/sample.pdf';
                    $targetFile = $achievementDir . '/' . $docType['name'];
                    
                    // Create the document record
                    $document = AchievementDocument::create([
                        'sa_id' => $achievement->sa_id,
                        'document_type' => $docType['type'],
                        'file_path' => Storage::disk('public')->exists($sampleFile) ? $targetFile : null,
                        'file_name' => $docType['name'],
                        'file_type' => str_ends_with($docType['name'], '.pdf') ? 'application/pdf' : 'image/jpeg',
                        'file_size' => rand(100000, 5000000),
                        'status' => $docStatus,
                        'verified_by' => in_array($docStatus, ['approved', 'rejected']) ? $validator->id : null,
                        'verified_at' => in_array($docStatus, ['approved', 'rejected']) ? now()->subDays(rand(1, 5)) : null,
                        'revision_notes' => $docStatus === 'revision' ? 'Dokumen tidak jelas, mohon upload ulang dengan kualitas lebih baik.' : null,
                    ]);

                    // Copy sample file if exists
                    if (Storage::disk('public')->exists($sampleFile)) {
                        Storage::disk('public')->copy($sampleFile, $targetFile);
                    }
                }

                // Add validation log if not pending
                if ($status !== 'pending') {
                    ValidationLog::create([
                        'sa_id' => $achievement->sa_id,
                        'validator_id' => $validator->id,
                        'old_status' => 'pending',
                        'new_status' => $status,
                        'notes' => $status === 'approved' 
                            ? 'Dokumen lengkap dan valid.' 
                            : ($status === 'rejected' 
                                ? 'Dokumen tidak memenuhi kriteria.' 
                                : 'Diperlukan dokumen tambahan.'),
                        'validated_at' => now()->subDays(rand(1, 10)),
                    ]);
                }
            }
        }

        $this->command->info('Achievement validation sample data seeded successfully!');
        $this->command->info('');
        $this->command->info('Sample accounts created:');
        $this->command->info('  - Admin: admin@unpatti.ac.id / password');
        $this->command->info('  - Validator: validator@unpatti.ac.id / password');
    }

    protected function createSamplePdfIfNotExists(): void
    {
        $samplePath = storage_path('app/public/sample-documents/sample.pdf');
        
        if (File::exists($samplePath)) {
            return;
        }

        // Create a simple PDF content
        $pdfContent = "%PDF-1.4
1 0 obj
<< /Type /Catalog /Pages 2 0 R >>
endobj
2 0 obj
<< /Type /Pages /Kids [3 0 R] /Count 1 >>
endobj
3 0 obj
<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>
endobj
4 0 obj
<< /Length 200 >>
stream
BT
/F1 24 Tf
100 700 Td
(SAMPLE DOCUMENT) Tj
0 -40 Td
/F1 12 Tf
(Universitas Pattimura) Tj
0 -20 Td
(Dokumen ini adalah contoh untuk testing.) Tj
0 -20 Td
(Tidak memiliki kekuatan hukum.) Tj
ET
endstream
endobj
5 0 obj
<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>
endobj
xref
0 6
0000000000 65535 f
0000000009 00000 n
0000000058 00000 n
0000000115 00000 n
0000000266 00000 n
0000000518 00000 n
trailer
<< /Size 6 /Root 1 0 R >>
startxref
595
%%EOF";

        File::put($samplePath, $pdfContent);
    }
}
