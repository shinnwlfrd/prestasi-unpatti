<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class StudentSyncService
{
    public function __construct(
        protected SiakadApiService $siakadApiService
    ) {}

    public function syncReferencedStudents(?int $limit = null): array
    {
        $students = Student::withTrashed()
            ->whereNotNull('id_mahasiswa')
            ->where(function ($query) {
                $query->has('achievements')
                    ->orWhereNotNull('updated_at');
            })
            ->orderByDesc('updated_at')
            ->when($limit, fn ($query) => $query->limit($limit))
            ->get();

        return $this->syncStudents($students);
    }

    public function syncStudents(Collection $students): array
    {
        $summary = [
            'processed' => 0,
            'updated' => 0,
            'restored' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        foreach ($students as $student) {
            $summary['processed']++;

            try {
                if (! $student->id_mahasiswa) {
                    $summary['skipped']++;

                    continue;
                }

                $siakadData = $this->siakadApiService->getMahasiswaById((string) $student->id_mahasiswa);

                if (! $siakadData) {
                    $summary['skipped']++;
                    Log::warning('Student sync skipped because SIAKAD detail was unavailable', [
                        'student_id' => $student->student_id,
                        'id_mahasiswa' => $student->id_mahasiswa,
                    ]);

                    continue;
                }

                $mapped = $this->siakadApiService->transformToStudentData($siakadData);

                if ($student->trashed()) {
                    $student->restore();
                    $summary['restored']++;
                }

                $student->fill($mapped);
                $student->save();
                $summary['updated']++;
            } catch (\Throwable $e) {
                $summary['errors']++;
                Log::warning('Student sync failed', [
                    'student_id' => $student->student_id,
                    'id_mahasiswa' => $student->id_mahasiswa,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $summary;
    }
}
