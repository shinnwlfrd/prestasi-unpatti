<?php

use App\Http\Controllers\AchievementAppealController;
use App\Http\Controllers\AchievementDashboardController;
use App\Http\Controllers\AchievementValidationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DocumentUploadController;
use App\Http\Controllers\StudentAchievementController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ValidatorController;
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
        }
    }

    return redirect()->route('login');
});

// Route login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// SSO Routes
Route::prefix('auth/sso')->name('sso.')->group(function () {
    Route::get('/redirect', [\App\Http\Controllers\Auth\SSOController::class, 'redirect'])->name('redirect');
    Route::get('/callback', [\App\Http\Controllers\Auth\SSOController::class, 'callback'])->name('callback');
    Route::post('/logout', [\App\Http\Controllers\Auth\SSOController::class, 'logout'])->name('logout');
});

// Generate sample PDF (for development/testing)
Route::get('/generate-sample-pdf', function () {
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.seeder-documents');

    return $pdf->download('prestasi_mahasiswa_dokumen_sample.pdf');
})->name('generate.sample.pdf');



// Route Mahasiswa (dilindungi oleh middleware khusus)
Route::middleware(['auth.student'])->group(function () {
    // Dashboard - using new controller
    Route::get('/dashboard', [\App\Http\Controllers\Student\DashboardController::class, 'index'])->name('student.dashboard');
    
    // Profile (keep old controller for now)
    Route::get('/student/profile', [StudentController::class, 'profile'])->name('student.profile');
    
    // Submit Achievement - using new controller
    Route::get('/submit', [\App\Http\Controllers\Student\AchievementController::class, 'create'])->name('student.achievement.create');
    Route::post('/submit', [\App\Http\Controllers\Student\AchievementController::class, 'store'])->name('student.achievement.store');

    // Appeals - using new controller
    Route::get('/achievements/{achievement}/appeal', [\App\Http\Controllers\Student\AppealController::class, 'create'])
        ->name('achievements.appeal.create');
    Route::post('/achievements/{achievement}/appeal', [\App\Http\Controllers\Student\AppealController::class, 'store'])
        ->name('achievements.appeal.store');
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
    Route::post('/documents/{document}/replace', [DocumentUploadController::class, 'replace'])
        ->name('achievements.documents.replace');
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

// Document preview & history - accessible by all authenticated users (student, admin, validator)
Route::middleware(['auth.any'])->group(function () {
    Route::get('/documents/{document}/preview', [DocumentUploadController::class, 'preview'])
        ->name('achievements.documents.preview');
    Route::get('/documents/{document}/history', [DocumentUploadController::class, 'history'])
        ->name('achievements.documents.history');

    // Preview SK from validation log (by file path)
    Route::get('/validation-logs/{log}/sk-preview', [ValidatorController::class, 'previewSK'])
        ->name('validation.sk.preview');

    // Note: Document upload routes are now also in auth.any group above
});

// Profile - only for regular users (admin/validator)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});

// Validator routes (hanya untuk role Validator)
Route::middleware(['auth', 'auth.validator'])->prefix('validator')->name('validator.')->group(function () {
    // Dashboard - using new controller
    Route::get('/', [\App\Http\Controllers\Validator\DashboardController::class, 'index'])->name('dashboard');
    
    // History - using new controller
    Route::get('/history', [\App\Http\Controllers\Validator\HistoryController::class, 'index'])->name('history');
    
    // Submit - using new controller
    Route::get('/submit', [\App\Http\Controllers\Validator\SubmitController::class, 'create'])->name('submit.form');
    Route::post('/submit', [\App\Http\Controllers\Validator\SubmitController::class, 'store'])->name('submit.store');

    // Achievement validation - using new controller
    Route::get('/achievements/{achievement}', [\App\Http\Controllers\Validator\ValidationController::class, 'show'])->name('achievements.show');
    Route::get('/achievements/{achievement}/documents', [\App\Http\Controllers\Validator\ValidationController::class, 'documents'])->name('achievements.documents');
    Route::post('/achievements/{achievement}/validate', [\App\Http\Controllers\Validator\ValidationController::class, 'validate'])->name('achievements.validate');
    Route::post('/documents/{document}/verify', [ValidatorController::class, 'verifyDocument'])->name('documents.verify');

    // Legacy routes (keep for backward compatibility)
    Route::patch('/achievements/{sa_id}/approve', [ValidatorController::class, 'approve']);
    Route::patch('/achievements/{sa_id}/reject', [ValidatorController::class, 'reject']);
});

// Admin routes
Route::middleware(['auth', 'auth.admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard - using new controller
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Students - using new controller
    Route::get('/students', [\App\Http\Controllers\Admin\StudentController::class, 'index'])->name('students');
    
    // Achievements - using new controller
    Route::get('/achievements', [AdminController::class, 'achievementTypes'])->name('achievements');
    Route::get('/student-achievements', [\App\Http\Controllers\Admin\AchievementController::class, 'index'])->name('student-achievements');
    
    // Validation Logs - using new controller
    Route::get('/validation-logs', [\App\Http\Controllers\Admin\ValidationLogController::class, 'index'])->name('validation-logs');
    
    // Users - using new controller
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users');
    Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.delete');

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

    // Achievement Validation System
    Route::prefix('achievements')->name('achievements.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AchievementDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/export', [AchievementDashboardController::class, 'export'])->name('dashboard.export');
        Route::get('/dashboard/chart-data', [AchievementDashboardController::class, 'chartData'])->name('dashboard.chart');

        // Validation
        Route::get('/validation', [AchievementValidationController::class, 'index'])->name('validation.index');
        Route::get('/validation/{achievement}', [AchievementValidationController::class, 'show'])->name('validation.show');
        Route::get('/validation/{achievement}/documents', [AchievementValidationController::class, 'documents'])->name('validation.documents');
        Route::post('/validation/{achievement}', [AchievementValidationController::class, 'validate'])->name('validation.process');
        Route::post('/validation/{achievement}/checklist', [AchievementValidationController::class, 'saveChecklist'])->name('validation.checklist');
        Route::get('/validation/{achievement}/history', [AchievementValidationController::class, 'history'])->name('validation.history');
        Route::post('/{achievement}/revert', [AchievementValidationController::class, 'revertToPending'])->name('validation.revert');

        // Document Verification
        Route::post('/documents/{document}/verify', [DocumentUploadController::class, 'verify'])->name('documents.verify');
    });

    // Appeals Management - REDIRECT to Validation with appeal tab
    Route::get('/appeals', function () {
        return redirect()->route('admin.achievements.validation.index', ['tab' => 'appeal']);
    })->name('appeals.index');

    Route::get('/appeals/{appeal}', [AchievementAppealController::class, 'show'])->name('appeals.show');
    Route::post('/appeals/{appeal}/review', [AchievementAppealController::class, 'review'])->name('appeals.review');
});
