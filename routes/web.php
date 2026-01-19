<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentAchievementController;
use App\Http\Controllers\ValidatorController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AchievementValidationController;
use App\Http\Controllers\AchievementDashboardController;
use App\Http\Controllers\AchievementAppealController;
use App\Http\Controllers\DocumentUploadController;

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
    Route::get('/dashboard', [StudentController::class, 'index'])->name('student.dashboard');
    Route::get('/student/profile', [StudentController::class, 'profile'])->name('student.profile');
    Route::get('/submit', [StudentAchievementController::class, 'create'])->name('student.achievement.create');
    Route::post('/submit', [StudentAchievementController::class, 'store'])->name('student.achievement.store');
    
    // Achievement documents (student only)
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
    
    // Appeals
    Route::get('/achievements/{achievement}/appeal', [AchievementAppealController::class, 'create'])
        ->name('achievements.appeal.create');
    Route::post('/achievements/{achievement}/appeal', [AchievementAppealController::class, 'store'])
        ->name('achievements.appeal.store');
});

// Document preview & history - accessible by all authenticated users (student, admin, validator)
Route::middleware(['auth'])->group(function () {
    Route::get('/documents/{document}/preview', [DocumentUploadController::class, 'preview'])
        ->name('achievements.documents.preview');
    Route::get('/documents/{document}/history', [DocumentUploadController::class, 'history'])
        ->name('achievements.documents.history');
    
    // Profile
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});

// Validator routes (hanya untuk role Validator)
Route::middleware(['auth'])->prefix('validator')->name('validator.')->group(function () {
    Route::get('/', [ValidatorController::class, 'dashboard'])->name('dashboard');
    Route::get('/history', [ValidatorController::class, 'history'])->name('history');
    Route::get('/submit', [ValidatorController::class, 'submitForm'])->name('submit.form');
    Route::post('/submit', [ValidatorController::class, 'submitStore'])->name('submit.store');
    
    // Achievement validation (validator-specific)
    Route::get('/achievements/{achievement}', [ValidatorController::class, 'show'])->name('achievements.show');
    Route::get('/achievements/{achievement}/documents', [ValidatorController::class, 'documents'])->name('achievements.documents');
    Route::post('/achievements/{achievement}/validate', [ValidatorController::class, 'validateAchievement'])->name('achievements.validate');
    Route::post('/documents/{document}/verify', [ValidatorController::class, 'verifyDocument'])->name('documents.verify');
    
    // Legacy routes
    Route::patch('/achievements/{sa_id}/approve', [ValidatorController::class, 'approve']);
    Route::patch('/achievements/{sa_id}/reject', [ValidatorController::class, 'reject']);
});


// Admin routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/students', [AdminController::class, 'students'])->name('students');
    Route::get('/achievements', [AdminController::class, 'achievementTypes'])->name('achievements');
    Route::get('/student-achievements', [AdminController::class, 'studentAchievements'])->name('student-achievements');
    Route::get('/validation-logs', [AdminController::class, 'validationLogs'])->name('validation-logs');
    Route::get('/auth-logs', [AdminController::class, 'authLogs'])->name('auth-logs');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    
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
        
        // Document Verification
        Route::post('/documents/{document}/verify', [DocumentUploadController::class, 'verify'])->name('documents.verify');
    });
    
    // Appeals Management
    Route::get('/appeals', [AchievementAppealController::class, 'index'])->name('appeals.index');
    Route::get('/appeals/{appeal}', [AchievementAppealController::class, 'show'])->name('appeals.show');
    Route::post('/appeals/{appeal}/review', [AchievementAppealController::class, 'review'])->name('appeals.review');
});
