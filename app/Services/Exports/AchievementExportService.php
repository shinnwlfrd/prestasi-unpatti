<?php

namespace App\Services\Exports;

use App\Helpers\ValidationStatusHelper;
use App\Jobs\GenerateAchievementExport;
use App\Models\AcademicPeriod;
use App\Models\AchievementExport;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AchievementExportService
{
    public function __construct(
        protected SimpleXlsxWriter $xlsxWriter
    ) {}

    public function queueAdminExport(User $user, Request $request): AchievementExport
    {
        $periodId = $request->get('period', 'all');
        $period = $periodId !== 'all' ? AcademicPeriod::find($periodId) : null;

        $scope = [
            'role_label' => 'Admin Universitas',
            'scope_name' => 'Seluruh Universitas',
            'filename_scope' => 'universitas',
            'period_label' => $period?->name ?? 'Semua Periode',
        ];

        return $this->createExport(
            user: $user,
            role: null,
            format: $this->normalizeFormat($request->get('format', 'csv')),
            filters: [
                'period' => $periodId,
            ],
            scope: $scope,
            source: (string) $request->get('type', 'dashboard')
        );
    }

    public function queueScopedExport(User $user, UserRole $role, Request $request): AchievementExport
    {
        $scope = $this->getScopeInfo($role);

        return $this->createExport(
            user: $user,
            role: $role,
            format: $this->normalizeFormat($request->get('format', 'xlsx')),
            filters: [
                'status' => $request->input('status'),
                'periods' => $this->normalizeArrayFilter($request->input('periods')),
                'period_id' => $request->input('period_id'),
                'category_id' => $request->input('category_id'),
                'levels' => $this->normalizeArrayFilter($request->input('levels')),
            ],
            scope: $scope,
            source: 'dashboard'
        );
    }

    public function getRecentForUser(User $user, ?UserRole $role = null, int $limit = 6): Collection
    {
        return AchievementExport::query()
            ->where('user_id', $user->id)
            ->when($role && $this->hasPersistentRoleId($role), fn (Builder $query) => $query->where('requested_for_role_id', $role->id))
            ->when(! $role, fn (Builder $query) => $query->whereNull('requested_for_role_id'))
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function serialize(AchievementExport $export, string $downloadRouteName, string $showRouteName): array
    {
        return [
            'id' => $export->id,
            'format' => strtoupper($export->format),
            'status' => $export->status,
            'status_label' => $this->statusLabel($export->status),
            'scope_name' => data_get($export->scope_snapshot, 'scope_name', 'Seluruh Universitas'),
            'role_label' => data_get($export->scope_snapshot, 'role_label', 'Pengguna'),
            'file_name' => $export->file_name,
            'row_count' => $export->row_count,
            'error_message' => $export->error_message,
            'created_at' => optional($export->created_at)?->format('d/m/Y H:i:s'),
            'completed_at' => optional($export->completed_at)?->format('d/m/Y H:i:s'),
            'download_url' => $export->isCompleted() ? route($downloadRouteName, $export) : null,
            'show_url' => route($showRouteName, $export),
        ];
    }

    public function generate(AchievementExport $export): void
    {
        $export->update([
            'status' => AchievementExport::STATUS_PROCESSING,
            'started_at' => now(),
            'error_message' => null,
        ]);

        $disk = $export->disk ?: 'local';
        $directory = 'exports/achievements/'.now()->format('Y/m');
        $extension = $export->format === 'xlsx' ? 'xlsx' : 'csv';
        $filename = $this->buildFilename($export, $extension);
        $filePath = $directory.'/'.$filename;

        Storage::disk($disk)->makeDirectory($directory);
        $absolutePath = Storage::disk($disk)->path($filePath);

        if ($export->format === 'xlsx') {
            $rows = $this->buildRows($export);
            $this->xlsxWriter->store($absolutePath, $rows);
        } else {
            $this->writeCsv($absolutePath, $export);
        }

        $export->update([
            'status' => AchievementExport::STATUS_COMPLETED,
            'file_path' => $filePath,
            'file_name' => $filename,
            'file_size' => Storage::disk($disk)->size($filePath),
            'completed_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);
    }

    public function buildQuery(AchievementExport $export): Builder
    {
        $query = StudentAchievement::query()->with([
            'student',
            'achievement.category',
            'academicPeriod',
            'facultyValidator',
            'universityValidator',
            'documents',
            'skAssignment.skDocument',
        ]);

        $filters = $export->filters ?? [];
        $role = $export->requestedForRole;
        $scope = $export->scope_snapshot ?? [];

        $roleName = $role?->role ?? ($scope['role'] ?? null);
        $level = $role?->level ?? ($scope['level'] ?? null);
        $facultyId = $role?->faculty_id ?? ($scope['faculty_id'] ?? null);
        $departmentId = $role?->department_id ?? ($scope['department_id'] ?? null);
        $programStudyId = $role?->program_study_id ?? ($scope['program_study_id'] ?? null);

        if ($roleName) {
            if ($level !== 'university') {
                $query->whereHas('student', function (Builder $studentQuery) use ($level, $facultyId, $departmentId, $programStudyId) {
                    if ($level === 'faculty') {
                        $studentQuery->where('faculty_id', $facultyId);
                    } elseif ($level === 'department') {
                        $studentQuery->where('department_id', $departmentId);
                    } elseif ($level === 'program_study') {
                        $studentQuery->where('program_study_id', $programStudyId);
                    }
                });
            }

            if ($roleName === 'pimpinan') {
                $query->whereIn('validation_status', StudentAchievement::getValidationDecisionStatusGroups()['approved']);
            }
        }

        if (($filters['period'] ?? 'all') !== 'all' && filled($filters['period'] ?? null)) {
            $query->where('academic_period_id', $filters['period']);
        }

        if (! empty($filters['periods'])) {
            $query->whereIn('academic_period_id', $filters['periods']);
        } elseif (filled($filters['period_id'] ?? null)) {
            $query->where('academic_period_id', $filters['period_id']);
        }

        if (filled($filters['status'] ?? null)) {
            $this->applyStatusFilter($query, $filters['status']);
        }

        if (filled($filters['category_id'] ?? null)) {
            $query->whereHas('achievement', function (Builder $achievementQuery) use ($filters) {
                $achievementQuery->where('category_id', $filters['category_id']);
            });
        }

        if (! empty($filters['levels'])) {
            $query->whereIn('student_achievements.level', $filters['levels']);
        }

        return $query->orderBy('sa_id');
    }

    public function authorizeDownload(AchievementExport $export, User $user, ?UserRole $role = null): bool
    {
        if ($export->user_id !== $user->id) {
            return false;
        }

        if ($role && $this->hasPersistentRoleId($role) && $export->requested_for_role_id !== $role->id) {
            return false;
        }

        if (! $role && $export->requested_for_role_id !== null) {
            return false;
        }

        return $export->isCompleted();
    }

    private function createExport(User $user, ?UserRole $role, string $format, array $filters, array $scope, string $source): AchievementExport
    {
        $export = AchievementExport::create([
            'user_id' => $user->id,
            'requested_for_role_id' => $this->hasPersistentRoleId($role) ? $role->id : null,
            'context' => 'achievements',
            'source' => $source,
            'format' => $format,
            'status' => AchievementExport::STATUS_QUEUED,
            'filters' => array_filter($filters, fn ($value) => ! is_null($value) && $value !== []),
            'scope_snapshot' => $scope,
            'queued_at' => now(),
        ]);

        GenerateAchievementExport::dispatch($export->id);

        return $export->fresh(['requestedForRole']);
    }

    private function writeCsv(string $absolutePath, AchievementExport $export): void
    {
        $handle = fopen($absolutePath, 'wb');

        if ($handle === false) {
            throw new \RuntimeException('Gagal membuka file CSV export.');
        }

        fwrite($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        foreach ($this->buildRows($export) as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
    }

    private function buildRows(AchievementExport $export): \Generator
    {
        yield ['LAPORAN PRESTASI MAHASISWA - SIMAPRES UNPATTI'];
        yield ['Jabatan: '.data_get($export->scope_snapshot, 'role_label', 'Pengguna')];
        yield ['Cakupan: '.data_get($export->scope_snapshot, 'scope_name', 'Seluruh Universitas')];
        yield ['Periode: '.data_get($export->scope_snapshot, 'period_label', 'Sesuai Filter')];
        yield ['Tanggal Cetak: '.now()->format('d/m/Y H:i:s')];
        yield ['Dicetak oleh: '.$export->user->name];
        yield [];
        yield $this->headings();

        $rowCount = 0;
        foreach ($this->buildQuery($export)->lazyById(200, 'sa_id') as $achievement) {
            $rowCount++;
            yield $this->mapAchievementRow($achievement);
        }

        $export->forceFill(['row_count' => $rowCount])->save();
    }

    private function headings(): array
    {
        return [
            'ID Prestasi',
            'NIM',
            'Nama Mahasiswa',
            'Email',
            'Angkatan',
            'Fakultas',
            'Jurusan',
            'Program Studi',
            'Nama Event/Kompetisi',
            'Kategori Prestasi',
            'Tingkat Prestasi',
            'Peringkat/Pencapaian',
            'Penyelenggara',
            'Lokasi Event',
            'Tanggal Event',
            'Tahun',
            'Deskripsi',
            'Periode Akademik',
            'Status Validasi',
            'Tahap Validasi',
            'Validator Fakultas',
            'Tanggal Validasi Fakultas',
            'Catatan Fakultas',
            'Validator Universitas',
            'Tanggal Validasi Universitas',
            'Catatan Universitas',
            'Nomor SK',
            'Tanggal SK',
            'Jumlah Dokumen',
            'Submitted By',
            'Tanggal Submit',
            'Tanggal Dibuat',
            'Terakhir Diupdate',
        ];
    }

    private function mapAchievementRow(StudentAchievement $achievement): array
    {
        $snapshot = $achievement->student_snapshot ?? [];
        $student = $achievement->student;
        $skDocument = $achievement->skAssignment?->skDocument;

        return [
            $achievement->sa_id,
            $snapshot['student_id'] ?? $student->student_id ?? '-',
            $snapshot['name'] ?? $student->name ?? '-',
            $snapshot['email'] ?? $student->email ?? '-',
            $snapshot['angkatan'] ?? $student->angkatan ?? '-',
            $snapshot['faculty'] ?? $student->faculty ?? '-',
            $snapshot['department'] ?? $student->department ?? '-',
            $snapshot['program_study'] ?? $student->program_study ?? '-',
            $achievement->event_name ?? '-',
            $achievement->achievement?->category?->name ?? '-',
            $achievement->level ?? '-',
            $achievement->ranking ?? '-',
            $achievement->organizer ?? '-',
            $achievement->event_location ?? '-',
            $achievement->event_date?->format('d/m/Y') ?? '-',
            $achievement->event_date?->format('Y') ?? '-',
            $achievement->description ?? '-',
            $achievement->academicPeriod?->name ?? '-',
            ValidationStatusHelper::getLabel($achievement->validation_status),
            ValidationStatusHelper::getStageLabel($achievement->validation_status),
            $achievement->facultyValidator?->name ?? '-',
            $achievement->faculty_validated_at?->format('d/m/Y H:i') ?? '-',
            $achievement->faculty_notes ?? '-',
            $achievement->universityValidator?->name ?? '-',
            $achievement->university_validated_at?->format('d/m/Y H:i') ?? '-',
            $achievement->university_notes ?? '-',
            $skDocument?->sk_number ?? '-',
            $skDocument?->issued_date?->format('d/m/Y') ?? '-',
            (string) $achievement->documents->count(),
            $this->submittedByLabel($achievement->submitted_by),
            $achievement->submitted_at?->format('d/m/Y H:i') ?? '-',
            $achievement->created_at?->format('d/m/Y H:i') ?? '-',
            $achievement->updated_at?->format('d/m/Y H:i') ?? '-',
        ];
    }

    private function normalizeFormat(string $format): string
    {
        return strtolower($format) === 'csv' ? 'csv' : 'xlsx';
    }

    private function normalizeArrayFilter(mixed $value): ?array
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        $items = is_array($value) ? $value : [$value];
        $items = array_values(array_filter($items, fn ($item) => $item !== null && $item !== ''));

        return $items === [] ? null : $items;
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            AchievementExport::STATUS_QUEUED => 'Dalam antrean',
            AchievementExport::STATUS_PROCESSING => 'Sedang diproses',
            AchievementExport::STATUS_COMPLETED => 'Siap diunduh',
            AchievementExport::STATUS_FAILED => 'Gagal',
            default => ucfirst($status),
        };
    }

    private function submittedByLabel(?string $submittedBy): string
    {
        return match ($submittedBy) {
            'student' => 'Mahasiswa',
            'validator' => 'Operator',
            'admin' => 'Administrator',
            default => $submittedBy ?? '-',
        };
    }

    private function applyStatusFilter(Builder $query, string $status): void
    {
        $groups = StudentAchievement::getWorkflowStatusGroups();
        $groups += StudentAchievement::getValidationDecisionStatusGroups();

        if (isset($groups[$status])) {
            $query->whereIn('validation_status', $groups[$status]);

            return;
        }

        $query->where('validation_status', $status);
    }

    private function buildFilename(AchievementExport $export, string $extension): string
    {
        $scope = data_get($export->scope_snapshot, 'filename_scope', 'universitas');
        $timestamp = now()->format('Ymd_His');

        return 'prestasi_'.Str::slug($scope, '_').'_'.$timestamp.'_'.$export->id.'.'.$extension;
    }

    private function getScopeInfo(UserRole $role): array
    {
        return [
            'role' => $role->role,
            'level' => $role->level,
            'faculty_id' => $role->faculty_id,
            'department_id' => $role->department_id,
            'program_study_id' => $role->program_study_id,
            'role_label' => $role->getRoleDisplayName(),
            'scope_name' => $role->getScopeDescription(),
            'filename_scope' => $role->getScopeDescription(),
            'period_label' => 'Sesuai Filter Dashboard',
        ];
    }

    private function hasPersistentRoleId(?UserRole $role): bool
    {
        return $role !== null && $role->exists && is_numeric($role->id);
    }
}
