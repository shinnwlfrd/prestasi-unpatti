<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DocumentUploadController;
use Illuminate\Support\Facades\Route;

// Halaman utama: arahkan ke dashboard sesuai role, atau ke login jika belum login
Route::get('/', function () {
    if (session('auth_role') === 'student') {
        return redirect()->route('student.dashboard');
    } elseif (auth()->check()) {
        $role = auth()->user()->role;
        if ($role === 'Admin') {
            return redirect('/admin');
        } elseif ($role === 'Validator') {
            return redirect()->route('validator.dashboard');
        } elseif ($role === 'Pimpinan') {
            return redirect()->route('pimpinan.dashboard');
        }
    }

    return redirect()->route('login');
});

// Panduan Penggunaan (Public - no auth required)

// Route login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
// Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Role Switch Routes (for multi-role users)
Route::middleware(['auth.any'])->group(function () {
    Route::get('/switch-role', [\App\Http\Controllers\RoleSwitchController::class, 'showSwitchPage'])->name('role.switch.page');
    Route::get('/api/available-roles', [\App\Http\Controllers\RoleSwitchController::class, 'getAvailableRoles'])->name('role.available');
    Route::post('/api/switch-role', [\App\Http\Controllers\RoleSwitchController::class, 'switchRole'])->name('role.switch');
});

// SSO Routes (Unpatti SSO Integration)
Route::prefix('sso')->name('sso.')->group(function () {
    Route::get('/redirect', [\App\Http\Controllers\SSO\AuthController::class, 'redirect'])->name('redirect');
    Route::get('/callback', [\App\Http\Controllers\SSO\AuthController::class, 'callback'])->name('callback');
    Route::post('/logout', [\App\Http\Controllers\SSO\AuthController::class, 'logout'])->name('logout');
});

// Generate sample PDF (for development/testing)
Route::get('/generate-sample-pdf', function () {
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.seeder-documents');

    return $pdf->download('prestasi_mahasiswa_dokumen_sample.pdf');
})->name('generate.sample.pdf');

// API Routes for AJAX
Route::prefix('api')->name('api.')->group(function () {
    Route::middleware(['auth'])->group(function () {
        // Search mahasiswa from SIAKAD (for dropdown)
        Route::get('/siakad/mahasiswa/search', [
            \App\Http\Controllers\Api\SiakadMahasiswaController::class,
            'search'
        ])->name('siakad.mahasiswa.search');

        // Get mahasiswa detail by ID
        Route::get('/siakad/mahasiswa/{id}', [
            \App\Http\Controllers\Api\SiakadMahasiswaController::class,
            'show'
        ])->name('siakad.mahasiswa.show');
    });
});

// Route Mahasiswa (dilindungi oleh middleware khusus)
Route::middleware(['auth.student'])->group(function () {
    // Dashboard - using new controller
    Route::get('/dashboard', [\App\Http\Controllers\Student\DashboardController::class, 'index'])->name('student.dashboard');

    // Profile
    Route::get('/student/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('student.profile');

    // Submit Achievement - using new controller
    Route::get('/submit', [\App\Http\Controllers\Student\AchievementController::class, 'create'])->name('student.achievement.create');
    Route::post('/submit', [\App\Http\Controllers\Student\AchievementController::class, 'store'])->name('student.achievement.store');

    // Request Review Ulang (menggantikan fitur banding)
    Route::post('/achievements/{achievement}/request-review', [\App\Http\Controllers\Student\AchievementController::class, 'requestReview'])
        ->name('student.achievement.request-review');

    // Delete Achievement (soft delete for rejected achievements)
    Route::delete('/achievements/{achievement}', [\App\Http\Controllers\Student\AchievementController::class, 'destroy'])
        ->name('student.achievement.destroy');
});

// Achievement documents - accessible by students, validators, and admins
Route::middleware(['auth.any'])->group(function () {
    Route::get('/achievements/{achievement}/documents', [DocumentUploadController::class, 'index'])
        ->name('achievements.documents.index');
    Route::post('/achievements/{achievement}/documents', [DocumentUploadController::class, 'store'])
        ->name('achievements.documents.store');
    Route::post('/achievements/{achievement}/documents/upload', [DocumentUploadController::class, 'upload'])
        ->name('achievements.documents.upload');
    Route::post('/achievements/{achievement}/documents/submit', [DocumentUploadController::class, 'submit'])
        ->name('achievements.documents.submit');
    Route::get('/documents/{document}/preview', [DocumentUploadController::class, 'preview'])
        ->name('achievements.documents.preview');
    Route::get('/achievements/{achievement}/certificate/preview', [DocumentUploadController::class, 'previewCertificate'])
        ->name('achievements.certificate.preview');
    Route::get('/documents/{document}/history', [DocumentUploadController::class, 'history'])
        ->name('achievements.documents.history');
    Route::post('/documents/{document}/replace', [DocumentUploadController::class, 'replace'])
        ->name('achievements.documents.replace');
    Route::post('/achievements/{achievement}/certificate/replace', [DocumentUploadController::class, 'replaceCertificate'])
        ->name('achievements.certificate.replace');
    Route::post('/documents/{document}/submit', [DocumentUploadController::class, 'submitSingle'])
        ->name('achievements.documents.submitSingle');
    Route::delete('/documents/{document}', [DocumentUploadController::class, 'destroy'])
        ->name('achievements.documents.destroy');

    // Admin/Validator actions
    Route::post('/documents/{document}/revert', [DocumentUploadController::class, 'revertToPending'])
        ->name('achievements.documents.revert');
    Route::post('/documents/{document}/add-note', [DocumentUploadController::class, 'addNote'])
        ->name('achievements.documents.addNote');
});

// Profile - only for regular users (admin/validator)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});

// API Routes for Validator (must be before validator prefix to avoid /validator/api/validator path)
Route::middleware(['auth', 'auth.validator'])->group(function () {
    Route::get('/api/validator/achievements/{achievement}', [\App\Http\Controllers\Validator\ValidationController::class, 'getAchievementData'])->name('api.validator.achievements.data');
});

// SIGAP API Routes (for fetching faculty, department, study program data)
Route::prefix('api/sigap')->name('api.sigap.')->group(function () {
    // Filter cascade endpoints
    Route::get('/faculties', [\App\Http\Controllers\Api\SigapFilterController::class, 'getFaculties'])->name('faculties');
    Route::get('/departments', [\App\Http\Controllers\Api\SigapFilterController::class, 'getDepartments'])->name('departments');
    Route::get('/study-programs', [\App\Http\Controllers\Api\SigapFilterController::class, 'getStudyPrograms'])->name('study-programs');
    Route::get('/hierarchy', [\App\Http\Controllers\Api\SigapFilterController::class, 'getHierarchy'])->name('hierarchy');

    // Student search endpoint
    Route::get('/students/search', [\App\Http\Controllers\Api\SigapController::class, 'searchStudents'])->name('students.search');

    // SK search endpoint
    Route::get('/sk/search', [\App\Http\Controllers\Api\SKSearchController::class, 'search'])->name('sk.search');

    // Cache management
    Route::post('/clear-cache', [\App\Http\Controllers\Api\SigapController::class, 'clearCache'])->name('clear-cache');
});

// Check if email exists in students table (for multi-role detection)
Route::get('/api/check-student-email', function (Illuminate\Http\Request $request) {
    try {
        $email = $request->query('email');

        // Validate email
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'exists' => false,
                'email' => $email,
                'error' => 'Invalid email format'
            ], 400);
        }

        $exists = \App\Models\Student::where('email', $email)->exists();

        return response()->json([
            'exists' => $exists,
            'email' => $email
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Check student email error', [
            'error' => $e->getMessage(),
            'email' => $request->query('email')
        ]);

        return response()->json([
            'exists' => false,
            'email' => $request->query('email'),
            'error' => 'Server error'
        ], 500);
    }
})->name('api.check-student-email');

// Check user data (for add role feature)
Route::get('/api/check-user-data', function (Illuminate\Http\Request $request) {
    try {
        $email = $request->query('email');

        // Validate email
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'exists_in_users' => false,
                'exists_in_students' => false,
                'error' => 'Invalid email format'
            ], 400);
        }

        // Check in users table
        $user = \App\Models\User::where('email', $email)->first();

        // Check in students table
        $student = \App\Models\Student::where('email', $email)->first();

        // Check if it's a staff email domain
        $isStaffDomain = str_ends_with(strtolower($email), '@staff.unpatti.ac.id');

        return response()->json([
            'exists_in_users' => $user !== null,
            'exists_in_students' => $student !== null,
            'is_staff' => $isStaffDomain,
            'user_data' => $user ? [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ] : null,
            'student_data' => $student ? [
                'student_id' => $student->student_id,
                'name' => $student->name,
                'email' => $student->email,
                'program_study' => $student->program_study,
                'faculty' => $student->faculty
            ] : null
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Check user data error', [
            'error' => $e->getMessage(),
            'email' => $request->query('email')
        ]);

        return response()->json([
            'exists_in_users' => false,
            'exists_in_students' => false,
            'error' => 'Server error'
        ], 500);
    }
})->name('api.check-user-data');

// Export API Routes (accessible by validator, pimpinan, admin)
Route::middleware(['auth', 'multi.role:operator,pimpinan,admin,super_admin'])->prefix('api/export')->name('api.export.')->group(function () {
    Route::get('/achievements', [\App\Http\Controllers\Api\ExportController::class, 'exportAchievements'])->name('achievements');
    Route::get('/statistics', [\App\Http\Controllers\Api\ExportController::class, 'exportStatistics'])->name('statistics');
});

// Validator routes (Validator/Operator Fakultas - same role, different name)
Route::middleware(['auth', 'multi.role:operator', 'operator.level'])->prefix('validator')->name('validator.')->group(function () {
    // Dashboard redirect to pending (index page for validator)
    Route::get('/', function () {
        return redirect()->route('validator.pending.index');
    })->name('dashboard');

    // Dashboard AJAX endpoints
    Route::get('/api/hierarchical-chart-data', [\App\Http\Controllers\Validator\DashboardController::class, 'getHierarchicalChartData'])->name('api.hierarchical-chart-data');
    Route::get('/api/event-participants', [\App\Http\Controllers\Validator\DashboardController::class, 'getEventParticipants'])->name('api.event-participants');
    Route::get('/api/sla-breach-details', [\App\Http\Controllers\Validator\DashboardController::class, 'getSlaBreachDetails'])->name('sla-breach-details');

    // Students
    Route::get('/students', [\App\Http\Controllers\Validator\StudentController::class, 'index'])->name('students.index');
    Route::get('/students/{studentId}', [\App\Http\Controllers\Validator\StudentController::class, 'show'])->name('students.show');
    Route::get('/api/students/search', [\App\Http\Controllers\Validator\StudentController::class, 'search'])->name('students.search');

    // Faculty Validation (INDEX PAGE)
    Route::get('/pending', [\App\Http\Controllers\Validator\FacultyValidationController::class, 'index'])->name('pending.index');
    Route::get('/pending/{achievement}', [\App\Http\Controllers\Validator\FacultyValidationController::class, 'show'])->name('pending.show');
    Route::post('/pending/{achievement}/validate', [\App\Http\Controllers\Validator\FacultyValidationController::class, 'validate'])->name('pending.validate');
    Route::post('/pending/{achievement}/start-review', [\App\Http\Controllers\Validator\FacultyValidationController::class, 'startReview'])->name('pending.start-review');

    // History
    Route::get('/history', [\App\Http\Controllers\Validator\HistoryController::class, 'index'])->name('history');

    // Submit Achievement
    Route::get('/submit', [\App\Http\Controllers\Validator\SubmitController::class, 'create'])->name('submit.form');
    Route::post('/submit', [\App\Http\Controllers\Validator\SubmitController::class, 'store'])->name('submit.store');

    // SK Documents
    Route::prefix('sk')->name('sk.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Validator\SKDocumentController::class, 'index'])->name('index');
        Route::get('/{sk}', [\App\Http\Controllers\Validator\SKDocumentController::class, 'show'])->name('show');
        Route::get('/{sk}/preview', [\App\Http\Controllers\Validator\SKDocumentController::class, 'preview'])->name('preview');
        Route::get('/{sk}/achievements', [\App\Http\Controllers\Validator\SKDocumentController::class, 'getAchievements'])->name('achievements');
        Route::post('/{sk}/process-assignment', [\App\Http\Controllers\Validator\SKDocumentController::class, 'processAssignment'])->name('process-assignment');
    });
});

// Pimpinan routes (Read-only - uses same pages as validator)
Route::middleware(['auth', 'multi.role:pimpinan', 'pimpinan.level'])->prefix('pimpinan')->name('pimpinan.')->group(function () {
    // Dashboard (Read-only - same as validator)
    Route::get('/', [\App\Http\Controllers\Validator\DashboardController::class, 'index'])->name('dashboard');

    // Dashboard AJAX endpoints
    Route::get('/api/hierarchical-chart-data', [\App\Http\Controllers\Validator\DashboardController::class, 'getHierarchicalChartData'])->name('api.hierarchical-chart-data');
    Route::get('/api/event-participants', [\App\Http\Controllers\Validator\DashboardController::class, 'getEventParticipants'])->name('api.event-participants');
    Route::get('/api/sla-breach-details', [\App\Http\Controllers\Validator\DashboardController::class, 'getSlaBreachDetails'])->name('sla-breach-details');

    // Students (Read-only - same as validator)
    Route::get('/students', [\App\Http\Controllers\Validator\StudentController::class, 'index'])->name('students.index');
    Route::get('/students/{studentId}', [\App\Http\Controllers\Validator\StudentController::class, 'show'])->name('students.show');

    // Pending/Validation (Read-only - can view but not validate)
    Route::get('/pending', [\App\Http\Controllers\Validator\FacultyValidationController::class, 'index'])->name('pending.index');
    Route::get('/pending/{achievement}', [\App\Http\Controllers\Validator\FacultyValidationController::class, 'show'])->name('pending.show');

    // History (Read-only - same as validator)
    Route::get('/history', [\App\Http\Controllers\Validator\HistoryController::class, 'index'])->name('history');

    // SK Documents (Read-only - same as validator)
    Route::prefix('sk')->name('sk.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Validator\SKDocumentController::class, 'index'])->name('index');
        Route::get('/{sk}', [\App\Http\Controllers\Validator\SKDocumentController::class, 'show'])->name('show');
        Route::get('/{sk}/preview', [\App\Http\Controllers\Validator\SKDocumentController::class, 'preview'])->name('preview');
    });
});

// Admin routes (accessible by super_admin and admin roles)
Route::middleware(['auth', 'multi.role:super_admin,admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard - using new controller
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // API for Dashboard Anomalies
    Route::get('/api/anomalies/{type}', [\App\Http\Controllers\Admin\DashboardController::class, 'getAnomalyDetails'])->name('api.anomalies');
    Route::delete('/api/achievements/{id}', [\App\Http\Controllers\Admin\DashboardController::class, 'deleteAchievement'])->name('api.achievements.delete');

    // API for Unit Distribution
    Route::get('/api/unit-distribution', [\App\Http\Controllers\Admin\DashboardController::class, 'getUnitDistribution'])->name('api.unit-distribution');

    // University Validation (Two-Stage System)
    Route::prefix('university')->name('university.')->group(function () {
        Route::get('/pending', [\App\Http\Controllers\Admin\UniversityValidationController::class, 'index'])->name('index');
        Route::get('/achievements/{achievement}', [\App\Http\Controllers\Admin\UniversityValidationController::class, 'show'])->name('show');
        Route::post('/achievements/{achievement}/validate', [\App\Http\Controllers\Admin\UniversityValidationController::class, 'validate'])->name('validate');
        Route::post('/achievements/{achievement}/start-review', [\App\Http\Controllers\Admin\UniversityValidationController::class, 'startReview'])->name('start-review');
        Route::post('/bulk-assign', [\App\Http\Controllers\Admin\UniversityValidationController::class, 'bulkAssign'])->name('bulk-assign');
    });

    // Students - using new controller
    Route::get('/students', [\App\Http\Controllers\Admin\StudentController::class, 'index'])->name('students');
    Route::get('/students/{studentId}', [\App\Http\Controllers\Admin\StudentController::class, 'show'])->name('students.show');

    // Achievements
    Route::get('/student-achievements', [\App\Http\Controllers\Admin\AchievementController::class, 'index'])->name('student-achievements');
    Route::get('/student-achievements/{id}', [\App\Http\Controllers\Admin\AchievementController::class, 'show'])->name('student-achievements.show');

    // Validation Logs - using new controller
    Route::get('/validation-logs', [\App\Http\Controllers\Admin\ValidationLogController::class, 'index'])->name('validation-logs');

    // Users - using new controller
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users');
    Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::post('/users/create-new', [\App\Http\Controllers\Admin\UserController::class, 'createNewUser'])->name('users.create-new');
    Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.delete');
    Route::delete('/users/{user}/roles/{roleId}', [\App\Http\Controllers\Admin\UserController::class, 'deleteRole'])->name('users.delete-role');

    // Submit Achievement (Admin can submit on behalf of student)
    Route::get('/submit-achievement', [\App\Http\Controllers\Admin\AdminAchievementController::class, 'create'])->name('submit.create');
    Route::post('/submit-achievement', [\App\Http\Controllers\Admin\AdminAchievementController::class, 'store'])->name('submit.store');

    // Achievement Categories CRUD
    Route::resource('categories', \App\Http\Controllers\Admin\AchievementCategoryController::class);

    // Achievement Levels CRUD
    Route::resource('levels', \App\Http\Controllers\Admin\AchievementLevelController::class);

    // Academic Periods CRUD
    Route::resource('periods', \App\Http\Controllers\Admin\AcademicPeriodController::class);
    Route::patch('/periods/{period}/activate', [\App\Http\Controllers\Admin\AcademicPeriodController::class, 'activate'])->name('periods.activate');

    // SK Document Management
    Route::prefix('sk')->name('sk.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SKDocumentController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\SKDocumentController::class, 'store'])->name('store');
        Route::get('/{sk}', [\App\Http\Controllers\Admin\SKDocumentController::class, 'show'])->name('show');
        Route::delete('/{sk}', [\App\Http\Controllers\Admin\SKDocumentController::class, 'destroy'])->name('destroy');
        Route::get('/{sk}/achievements', [\App\Http\Controllers\Admin\SKDocumentController::class, 'getAchievements'])->name('achievements');
        Route::post('/{sk}/process-assignment', [\App\Http\Controllers\Admin\SKDocumentController::class, 'processAssignment'])->name('process-assignment');
        Route::get('/{sk}/preview', [\App\Http\Controllers\Admin\SKDocumentController::class, 'preview'])->name('preview');
    });

    // Export Achievements
    Route::get('/export/achievements', [\App\Http\Controllers\Admin\ExportController::class, 'exportAchievements'])->name('export.achievements');
});
