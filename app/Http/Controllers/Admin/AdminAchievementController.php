<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AchievementDocument;
use App\Models\AchievementLevel;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Services\AchievementApprovalService;
use App\Http\Requests\Admin\StoreAchievementRequest;
use Illuminate\Http\Request;

class AdminAchievementController extends Controller
{
    protected AchievementApprovalService $approvalService;

    public function __construct(AchievementApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Show form for submitting achievement on behalf of student.
     */
    public function create()
    {
        $students = Student::orderBy('name')->get();
        $categories = AchievementCategory::active()->get();
        $levels = AchievementLevel::active()->get();
        $skDocuments = \App\Models\SKDocument::orderBy('issued_date', 'desc')->get();

        // Handle old student_ids to preserve UI state after validation errors
        $oldStudentIds = old('student_ids', []);
        $selectedStudentsJson = '[]';

        if (!empty($oldStudentIds)) {
            $siakadService = app(\App\Services\SiakadApiService::class);
            $selectedStudents = [];

            foreach ($oldStudentIds as $idMahasiswa) {
                try {
                    $siakadData = $siakadService->getMahasiswaById($idMahasiswa);
                    if ($siakadData) {
                        $selectedStudents[] = [
                            'id' => $siakadData['id_mahasiswa'],
                            'student_id' => $siakadData['id_mahasiswa'],
                            'nim' => $siakadData['registrasi']['nim'] ?? '',
                            'name' => $siakadData['nama_mahasiswa'],
                            'faculty' => $siakadData['fakultas']['nama'] ?? '',
                            'prodi' => $siakadData['program_studi']['nama'] ?? ''
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::error('Error resolving old student ID', ['id' => $idMahasiswa, 'error' => $e->getMessage()]);
                }
            }
            $selectedStudentsJson = json_encode($selectedStudents);
        }

        return view('admin.submit', compact('students', 'categories', 'levels', 'skDocuments', 'selectedStudentsJson'));
    }

    /**
     * Store achievement submitted by admin.
     */
    public function store(StoreAchievementRequest $request)
    {
        $validated = $request->validated();

        $achievementId = Achievement::where('category_id', $validated['category_id'])
            ->where('is_active', true)
            ->value('id');

        if (!$achievementId) {
            return redirect()->back()
                ->withErrors(['category_id' => 'Kategori yang dipilih belum memiliki template prestasi. Hubungi admin.'])
                ->withInput();
        }

        $studentIds = $validated['student_ids']; // Array of id_mahasiswa (UUID)
        $createdAchievements = [];
        $errors = [];

        // Attachments from request
        $attachments = $request->file('attachments', []);

        // Process each student
        foreach ($studentIds as $idMahasiswa) {
            try {
                $achievement = $this->createStudentAchievement(
                    $idMahasiswa, 
                    $achievementId, 
                    $validated, 
                    $attachments[$idMahasiswa] ?? null,
                    $request
                );
                
                $createdAchievements[] = $achievement;
            } catch (\Exception $e) {
                $errors[] = "Gagal membuat prestasi untuk mahasiswa ID {$idMahasiswa}: {$e->getMessage()}";
            }
        }

        // Prepare success message
        $successCount = count($createdAchievements);
        $totalCount = count($studentIds);

        if ($successCount === 0) {
            return redirect()->back()
                ->withErrors(['error' => 'Gagal membuat prestasi untuk semua mahasiswa.'])
                ->withInput();
        }

        $message = "Berhasil membuat prestasi untuk {$successCount} dari {$totalCount} mahasiswa.";

        if (count($errors) > 0) {
            $message .= ' Beberapa gagal: ' . implode(', ', $errors);
        }

        // Redirect based on action
        if ($request->submit_action === 'approve' || $request->submit_action === 'reject') {
            return redirect()->route('admin.student-achievements')
                ->with('success', $message);
        }

        // Default: Pending - redirect to first achievement's document upload
        $firstAchievement = $createdAchievements[0];
        return redirect()->route('achievements.documents.index', $firstAchievement)
            ->with('success', $message . ' Anda dapat menambahkan dokumen tambahan.');
    }

    /**
     * Create achievement for a specific student.
     */
    protected function createStudentAchievement($idMahasiswa, $achievementId, array $validated, $studentFiles, StoreAchievementRequest $request): StudentAchievement
    {
        // Fetch full data from SIAKAD API
        $siakadService = app(\App\Services\SiakadApiService::class);
        $siakadData = $siakadService->getMahasiswaById($idMahasiswa);

        if (!$siakadData) {
            throw new \Exception("Data mahasiswa dengan ID {$idMahasiswa} tidak ditemukan di SIAKAD.");
        }

        // Get NIM from SIAKAD data
        $nim = $siakadData['registrasi']['nim'] ?? null;

        if (!$nim) {
            throw new \Exception("NIM tidak ditemukan untuk mahasiswa ID {$idMahasiswa}.");
        }

        // Check if student exists in local database
        $student = Student::withTrashed()->find($nim);

        // If not exists, create from SIAKAD data or update existing
        $studentData = $siakadService->transformToStudentData($siakadData);
        if (!$student) {
            $student = Student::create($studentData);
            \Log::info('New student created from SIAKAD', ['nim' => $nim, 'name' => $student->name]);
        } else {
            if ($student->trashed()) {
                $student->restore();
                \Log::info('Restored soft-deleted student during achievement creation', ['nim' => $nim]);
            }
            $student->update($studentData);
            \Log::info('Student data updated from SIAKAD', ['nim' => $nim, 'name' => $student->name]);
        }

        if (!$studentFiles || !isset($studentFiles['certificate'])) {
            throw new \Exception("Sertifikat untuk mahasiswa ID {$idMahasiswa} tidak ditemukan.");
        }

        $certificate = $studentFiles['certificate'];
        $additionalDocs = $studentFiles['additional_documents'] ?? [];

        // Store certificate for this student
        $certificatePath = $certificate->store('certificates', 'public');

        // Determine initial status based on action
        $initialStatus = match ($request->submit_action) {
            'approve' => 'Disetujui',
            'reject' => 'Ditolak',
            default => 'Menunggu',
        };

        // Determine SK required
        $skRequired = !$request->boolean('skip_sk');

        $achievement = StudentAchievement::create([
            'student_id' => $nim,
            'achievement_id' => $achievementId,
            'event_name' => $validated['event_name'],
            'level' => $validated['level'],
            'organizer' => $validated['organizer'],
            'event_date' => $validated['event_date'],
            'ranking' => $validated['ranking'] ?? null,
            'description' => $validated['description'] ?? null,
            'certificate' => $certificatePath,
            'validation_status' => $initialStatus,
            'validator_id' => $request->submit_action !== 'pending' ? auth()->id() : null,
            'submitted_by' => 'admin',
            'submitted_at' => now(),
            'sk_required' => $skRequired,
            'sk_waiver_reason' => $request->sk_waiver_reason,
            'sk_waiver_notes' => $request->sk_waiver_notes,
        ]);

        // Process additional documents if any
        foreach ($additionalDocs as $file) {
            if ($file->isValid()) {
                $fileName = 'SuppDoc_' . $achievement->sa_id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('achievements/' . $achievement->sa_id, $fileName, 'public');

                $achievement->documents()->create([
                    'document_type' => AchievementDocument::TYPE_FOTO_DOKUMENTASI,
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'status' => AchievementDocument::STATUS_PENDING,
                ]);
            }
        }

        // Handle different actions
        if ($request->submit_action === 'approve') {
            $skDocumentPath = null;

            if ($request->sk_id) {
                $skDocument = \App\Models\SKDocument::find($request->sk_id);
                if ($skDocument) {
                    \App\Models\SKAssignment::create([
                        'sk_id' => $skDocument->id,
                        'sa_id' => $achievement->sa_id,
                        'assigned_by' => auth()->id(),
                        'assigned_at' => now(),
                    ]);
                    $skDocumentPath = $skDocument->file_path;
                }
            }

            if ($request->hasFile('alternative_document')) {
                $file = $request->file('alternative_document');
                $fileName = 'Alt_Doc_' . $achievement->sa_id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('achievements/' . $achievement->sa_id, $fileName, 'public');

                $achievement->update(['alternative_document_path' => $filePath]);

                $achievement->documents()->create([
                    'document_type' => 'dokumen_alternatif',
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'status' => AchievementDocument::STATUS_APPROVED,
                    'verified_by' => auth()->id(),
                    'verified_at' => now(),
                ]);
            }

            $notes = $skRequired
                ? 'Disetujui langsung oleh admin saat submit'
                : 'Disetujui tanpa SK: ' . ($request->sk_waiver_reason ? StudentAchievement::getSkWaiverReasons()[$request->sk_waiver_reason] : 'N/A');

            $this->approvalService->approve($achievement, auth()->user(), $notes, $skDocumentPath);
        } elseif ($request->submit_action === 'reject') {
            $this->approvalService->reject($achievement, auth()->user(), $request->rejection_reason);
        }

        return $achievement;
    }
}
