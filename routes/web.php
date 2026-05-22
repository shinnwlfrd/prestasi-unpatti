<?php

use App\Http\Controllers\Admin\AcademicPeriodController;
use App\Http\Controllers\Admin\AchievementCategoryController;
use App\Http\Controllers\Admin\AchievementLevelController;
use App\Http\Controllers\Admin\AdminAchievementController;
use App\Http\Controllers\Admin\ConfigAuditLogController;
use App\Http\Controllers\Admin\NotificationDeliveryLogController;
use App\Http\Controllers\Admin\UniversityValidationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ValidationLogController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\SiakadMahasiswaController;
use App\Http\Controllers\Api\SigapController;
use App\Http\Controllers\Api\SigapFilterController;
use App\Http\Controllers\Api\SKSearchController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DocumentUploadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleSwitchController;
use App\Http\Controllers\SSO\AuthController;
use App\Http\Controllers\Student\AchievementController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Validator\FacultyValidationController;
use App\Http\Controllers\Validator\HistoryController;
use App\Http\Controllers\Validator\SKDocumentController;
use App\Http\Controllers\Validator\StudentController;
use App\Http\Controllers\Validator\SubmitController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

// Halaman utama: arahkan ke dashboard sesuai role, atau ke login jika belum login
Route::get('/', function () {
    if (session('auth_role') === 'student') {
        return redirect()->route('student.dashboard');
    } elseif (auth()->check()) {
        $user = auth()->user();
        $activeRoleType = request()->session()->get('active_role_type');

        if (! $activeRoleType) {
            $activeRoles = $user->activeRoles()->get();

            if ($activeRoles->count() > 1) {
                return redirect()->route('role.switch.page');
            }

            $activeRoleType = $activeRoles->first()?->role;
        }

        if ($activeRoleType) {
            return match ($activeRoleType) {
                'super_admin', 'admin' => redirect('/admin'),
                'operator' => redirect()->route('validator.dashboard'),
                'pimpinan' => redirect()->route('pimpinan.dashboard'),
                'mahasiswa' => redirect()->route('student.dashboard'),
                default => redirect()->route('login'),
            };
        }

        return match ($user->role) {
            'Admin' => redirect('/admin'),
            'Operator' => redirect()->route('validator.dashboard'),
            'Pimpinan' => redirect()->route('pimpinan.dashboard'),
            default => redirect()->route('login'),
        };
    }

    return redirect()->route('login');
});

// Panduan Penggunaan (Public - no auth required)

// Route login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Role Switch Routes (for multi-role users)
Route::middleware(['auth.any'])->group(function () {
    Route::get('/switch-role', [RoleSwitchController::class, 'showSwitchPage'])->name('role.switch.page');
    Route::get('/api/available-roles', [RoleSwitchController::class, 'getAvailableRoles'])->name('role.available');
    Route::post('/api/switch-role', [RoleSwitchController::class, 'switchRole'])->name('role.switch');
});

// SSO Routes (Unpatti SSO Integration)
Route::prefix('sso')->name('sso.')->group(function () {
    Route::get('/redirect', [AuthController::class, 'redirect'])->name('redirect');
    Route::get('/callback', [AuthController::class, 'callback'])->name('callback');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Generate sample PDF (for development/testing)
Route::get('/generate-sample-pdf', function () {
    $pdf = Pdf::loadView('pdf.seeder-documents');

    return $pdf->download('prestasi_mahasiswa_dokumen_sample.pdf');
})->name('generate.sample.pdf');

// API Routes for AJAX
Route::prefix('api')->middleware(['throttle:60,1'])->name('api.')->group(function () {
    Route::middleware(['auth'])->group(function () {
        // Search mahasiswa from SIAKAD (for dropdown)
        Route::get('/siakad/mahasiswa/search', [
            SiakadMahasiswaController::class,
            'search',
        ])->name('siakad.mahasiswa.search');

        // Get mahasiswa detail by ID
        Route::get('/siakad/mahasiswa/{id}', [
            SiakadMahasiswaController::class,
            'show',
        ])->name('siakad.mahasiswa.show');
    });
});

// Route Mahasiswa (dilindungi oleh middleware khusus)
Route::middleware(['auth.student'])->group(function () {
    // Dashboard - using new controller
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('student.dashboard');

    // Profile
    Route::get('/student/profile', [ProfileController::class, 'index'])->name('student.profile');

    // Submit Achievement - using new controller
    Route::get('/submit', [AchievementController::class, 'create'])->name('student.achievement.create');
    Route::post('/submit', [AchievementController::class, 'store'])->name('student.achievement.store');

    // Request Review Ulang (menggantikan fitur banding)
    Route::post('/achievements/{achievement}/request-review', [AchievementController::class, 'requestReview'])
        ->name('student.achievement.request-review');

    // Delete Achievement (soft delete for rejected achievements)
    Route::delete('/achievements/{achievement}', [AchievementController::class, 'destroy'])
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
    Route::post('/documents/{document}/verify', [DocumentUploadController::class, 'verify'])
        ->name('achievements.documents.verify');

    // Admin/Validator actions
    Route::post('/documents/{document}/revert', [DocumentUploadController::class, 'revertToPending'])
        ->name('achievements.documents.revert');
    Route::post('/documents/{document}/add-note', [DocumentUploadController::class, 'addNote'])
        ->name('achievements.documents.addNote');
    Route::post('/validator/documents/{document}/verify', [DocumentUploadController::class, 'verify']);
});

// Profile - only for regular users (admin/validator)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// API Routes for Validator (must be before validator prefix to avoid /validator/api/validator path)
Route::middleware(['auth', 'auth.validator', 'throttle:60,1'])->group(function () {
    Route::get('/api/validator/achievements/{achievement}', [FacultyValidationController::class, 'getAchievementData'])->name('api.validator.achievements.data');
});

// SIGAP API Routes (for fetching faculty, department, study program data)
Route::prefix('api/sigap')->middleware(['throttle:60,1'])->name('api.sigap.')->group(function () {
    // Filter cascade endpoints
    Route::get('/faculties', [SigapFilterController::class, 'getFaculties'])->name('faculties');
    Route::get('/departments', [SigapFilterController::class, 'getDepartments'])->name('departments');
    Route::get('/study-programs', [SigapFilterController::class, 'getStudyPrograms'])->name('study-programs');
    Route::get('/hierarchy', [SigapFilterController::class, 'getHierarchy'])->name('hierarchy');

    // Student search endpoint
    Route::get('/students/search', [SigapController::class, 'searchStudents'])
        ->middleware(['auth', 'throttle:10,1'])
        ->name('students.search');

    // SK search endpoint
    Route::get('/sk/search', [SKSearchController::class, 'search'])->name('sk.search');

    // Cache management
    Route::post('/clear-cache', [SigapController::class, 'clearCache'])
        ->middleware(['auth', 'can:admin', 'throttle:5,1'])
        ->name('clear-cache');
});

// Check if email exists in students table (for multi-role detection)
Route::get('/api/check-student-email', function (Request $request) {
    try {
        Log::info('Student email check accessed', [
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'status' => 'ok',
        ]);
    } catch (Exception $e) {
        Log::error('Check student email error', [
            'error' => $e->getMessage(),
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'status' => 'ok',
        ], 500);
    }
})->middleware(['auth', 'throttle:10,1'])->name('api.check-student-email');

// Check user data (for add role feature)
Route::get('/api/check-user-data', function (Request $request) {
    try {
        Log::info('User data check accessed', [
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'status' => 'ok',
        ]);
    } catch (Exception $e) {
        Log::error('Check user data error', [
            'error' => $e->getMessage(),
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'status' => 'ok',
        ], 500);
    }
})->middleware(['auth', 'throttle:10,1'])->name('api.check-user-data');

// Export API Routes (accessible by validator, pimpinan, admin)
Route::middleware(['auth', 'multi.role:operator,pimpinan,admin,super_admin', 'throttle:60,1'])->prefix('api/export')->name('api.export.')->group(function () {
    Route::get('/achievements', [ExportController::class, 'exportAchievements'])->name('achievements');
    Route::get('/recent', [ExportController::class, 'recent'])->name('recent');
    Route::get('/achievements/{achievementExport}', [ExportController::class, 'show'])->name('show');
    Route::get('/achievements/{achievementExport}/download', [ExportController::class, 'download'])->name('download');
    Route::get('/statistics', [ExportController::class, 'exportStatistics'])->name('statistics');
});

// Validator routes (Validator/Operator Fakultas - same role, different name)
Route::middleware(['auth', 'multi.role:operator', 'operator.level'])->prefix('validator')->name('validator.')->group(function () {
    // Dashboard redirect to pending (index page for validator)
    Route::get('/', function () {
        return redirect()->route('validator.pending.index');
    })->name('dashboard');

    // Dashboard AJAX endpoints
    Route::get('/api/hierarchical-chart-data', [App\Http\Controllers\Validator\DashboardController::class, 'getHierarchicalChartData'])->name('api.hierarchical-chart-data');
    Route::get('/api/event-participants', [App\Http\Controllers\Validator\DashboardController::class, 'getEventParticipants'])->name('api.event-participants');
    Route::get('/api/sla-breach-details', [App\Http\Controllers\Validator\DashboardController::class, 'getSlaBreachDetails'])->name('sla-breach-details');

    // Students
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/{studentId}', [StudentController::class, 'show'])->name('students.show');
    Route::get('/api/students/search', [StudentController::class, 'search'])->name('students.search');

    // Faculty Validation (INDEX PAGE)
    Route::get('/pending', [FacultyValidationController::class, 'index'])->name('pending.index');
    Route::get('/pending/{achievement}', [FacultyValidationController::class, 'show'])->name('pending.show');
    Route::post('/pending/{achievement}/validate', [FacultyValidationController::class, 'validate'])->name('pending.validate');
    Route::post('/pending/{achievement}/start-review', [FacultyValidationController::class, 'startReview'])->name('pending.start-review');

    // History
    Route::get('/history', [HistoryController::class, 'index'])->name('history');

    // Submit Achievement
    Route::get('/submit', [SubmitController::class, 'create'])->name('submit.form');
    Route::post('/submit', [SubmitController::class, 'store'])->name('submit.store');

    // SK Documents
    Route::prefix('sk')->name('sk.')->group(function () {
        Route::get('/', [SKDocumentController::class, 'index'])->name('index');
        Route::get('/{sk}', [SKDocumentController::class, 'show'])->name('show');
        Route::get('/{sk}/preview', [SKDocumentController::class, 'preview'])->name('preview');
        Route::get('/{sk}/achievements', [SKDocumentController::class, 'getAchievements'])->name('achievements');
        Route::post('/{sk}/process-assignment', [SKDocumentController::class, 'processAssignment'])->name('process-assignment');
    });
});

// Pimpinan routes (Read-only - uses same pages as validator)
Route::middleware(['auth', 'multi.role:pimpinan', 'pimpinan.level'])->prefix('pimpinan')->name('pimpinan.')->group(function () {
    // Dashboard (Read-only - same as validator)
    Route::get('/', [App\Http\Controllers\Validator\DashboardController::class, 'index'])->name('dashboard');

    // Dashboard AJAX endpoints
    Route::get('/api/hierarchical-chart-data', [App\Http\Controllers\Validator\DashboardController::class, 'getHierarchicalChartData'])->name('api.hierarchical-chart-data');
    Route::get('/api/event-participants', [App\Http\Controllers\Validator\DashboardController::class, 'getEventParticipants'])->name('api.event-participants');
    Route::get('/api/sla-breach-details', [App\Http\Controllers\Validator\DashboardController::class, 'getSlaBreachDetails'])->name('sla-breach-details');

    // Students (Read-only - same as validator)
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/{studentId}', [StudentController::class, 'show'])->name('students.show');

    // Pending/Validation (Read-only - can view but not validate)
    Route::get('/pending', [FacultyValidationController::class, 'index'])->name('pending.index');
    Route::get('/pending/{achievement}', [FacultyValidationController::class, 'show'])->name('pending.show');

    // History (Read-only - same as validator)
    Route::get('/history', [HistoryController::class, 'index'])->name('history');

    // SK Documents (Read-only - same as validator)
    Route::prefix('sk')->name('sk.')->group(function () {
        Route::get('/', [SKDocumentController::class, 'index'])->name('index');
        Route::get('/{sk}', [SKDocumentController::class, 'show'])->name('show');
        Route::get('/{sk}/preview', [SKDocumentController::class, 'preview'])->name('preview');
    });
});

// Admin routes (accessible by super_admin and admin roles)
Route::middleware(['auth', 'multi.role:super_admin,admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard - using new controller
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // API for Dashboard Anomalies
    Route::get('/api/anomalies/{type}', [App\Http\Controllers\Admin\DashboardController::class, 'getAnomalyDetails'])->name('api.anomalies');
    Route::delete('/api/achievements/{id}', [App\Http\Controllers\Admin\DashboardController::class, 'deleteAchievement'])->name('api.achievements.delete');

    // API for Unit Distribution
    Route::get('/api/unit-distribution', [App\Http\Controllers\Admin\DashboardController::class, 'getUnitDistribution'])->name('api.unit-distribution');
    Route::get('/api/integration-health', [App\Http\Controllers\Admin\DashboardController::class, 'getIntegrationHealth'])->name('api.integration-health');

    // University Validation (Two-Stage System)
    Route::prefix('university')->name('university.')->group(function () {
        Route::get('/pending', [UniversityValidationController::class, 'index'])->name('index');
        Route::get('/achievements/{achievement}', [UniversityValidationController::class, 'show'])->name('show');
        Route::post('/achievements/{achievement}/validate', [UniversityValidationController::class, 'validate'])->name('validate');
        Route::post('/achievements/{achievement}/start-review', [UniversityValidationController::class, 'startReview'])->name('start-review');
        Route::post('/bulk-assign', [UniversityValidationController::class, 'bulkAssign'])->name('bulk-assign');
    });

    // Students - using new controller
    Route::get('/students', [App\Http\Controllers\Admin\StudentController::class, 'index'])->name('students');
    Route::get('/students/{studentId}', [App\Http\Controllers\Admin\StudentController::class, 'show'])->name('students.show');

    // Achievements
    Route::get('/student-achievements', [App\Http\Controllers\Admin\AchievementController::class, 'index'])->name('student-achievements');
    Route::get('/student-achievements/{id}', [App\Http\Controllers\Admin\AchievementController::class, 'show'])->name('student-achievements.show');

    // Validation Logs - using new controller
    Route::get('/validation-logs', [ValidationLogController::class, 'index'])->name('validation-logs');

    // Notification Delivery Logs
    Route::get('/notification-delivery-logs', [NotificationDeliveryLogController::class, 'index'])
        ->name('notification-delivery-logs.index');
    Route::get('/notification-delivery-logs/summary', [NotificationDeliveryLogController::class, 'summary'])
        ->name('notification-delivery-logs.summary');

    // Config Audit Logs
    Route::get('/audit-logs', [ConfigAuditLogController::class, 'index'])->name('audit-logs');

    // Users - using new controller
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::post('/users/create-new', [UserController::class, 'createNewUser'])->name('users.create-new');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.delete');
    Route::delete('/users/{user}/roles/{roleId}', [UserController::class, 'deleteRole'])->name('users.delete-role');

    // Submit Achievement (Admin can submit on behalf of student)
    Route::get('/submit-achievement', [AdminAchievementController::class, 'create'])->name('submit.create');
    Route::post('/submit-achievement', [AdminAchievementController::class, 'store'])->name('submit.store');

    // Achievement Categories CRUD
    Route::resource('categories', AchievementCategoryController::class);

    // Achievement Levels CRUD
    Route::resource('levels', AchievementLevelController::class);

    // Academic Periods CRUD
    Route::resource('periods', AcademicPeriodController::class);
    Route::patch('/periods/{period}/activate', [AcademicPeriodController::class, 'activate'])->name('periods.activate');

    // SK Document Management
    Route::prefix('sk')->name('sk.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\SKDocumentController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Admin\SKDocumentController::class, 'store'])->name('store');
        Route::get('/{sk}', [App\Http\Controllers\Admin\SKDocumentController::class, 'show'])->name('show');
        Route::delete('/{sk}', [App\Http\Controllers\Admin\SKDocumentController::class, 'destroy'])->name('destroy');
        Route::get('/{sk}/achievements', [App\Http\Controllers\Admin\SKDocumentController::class, 'getAchievements'])->name('achievements');
        Route::post('/{sk}/process-assignment', [App\Http\Controllers\Admin\SKDocumentController::class, 'processAssignment'])->name('process-assignment');
        Route::get('/{sk}/preview', [App\Http\Controllers\Admin\SKDocumentController::class, 'preview'])->name('preview');
    });

    // Export Achievements
    Route::get('/export/achievements', [App\Http\Controllers\Admin\ExportController::class, 'exportAchievements'])->name('export.achievements');
    Route::get('/export/recent', [App\Http\Controllers\Admin\ExportController::class, 'recent'])->name('export.recent');
    Route::get('/export/achievements/{achievementExport}', [App\Http\Controllers\Admin\ExportController::class, 'show'])->name('export.show');
    Route::get('/export/achievements/{achievementExport}/download', [App\Http\Controllers\Admin\ExportController::class, 'download'])->name('export.download');
});
