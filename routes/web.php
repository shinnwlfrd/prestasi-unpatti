<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentAchievementController;
use App\Http\Controllers\ValidatorController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;

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

// Route Mahasiswa (dilindungi oleh middleware khusus)
Route::middleware(['auth.student'])->group(function () {
    Route::get('/dashboard', [StudentController::class, 'index'])->name('student.dashboard');
    Route::get('/submit', [StudentAchievementController::class, 'create'])->name('student.achievement.create');
    Route::post('/submit', [StudentAchievementController::class, 'store'])->name('student.achievement.store');
});

// Validator routes (hanya untuk role Validator)
Route::middleware(['auth'])->prefix('validator')->group(function () {
    Route::get('/', [ValidatorController::class, 'dashboard'])->name('validator.dashboard');
    Route::get('/history', [ValidatorController::class, 'history'])->name('validator.history');
    Route::get('/submit', [ValidatorController::class, 'submitForm'])->name('validator.submit.form');
    Route::post('/submit', [ValidatorController::class, 'submitStore'])->name('validator.submit.store');
    Route::patch('/achievements/{sa_id}/approve', [ValidatorController::class, 'approve']);
    Route::patch('/achievements/{sa_id}/reject', [ValidatorController::class, 'reject']);
});


// Admin routes
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/students', [AdminController::class, 'students'])->name('admin.students');
    Route::get('/achievements', [AdminController::class, 'achievementTypes'])->name('admin.achievements');
    Route::get('/student-achievements', [AdminController::class, 'studentAchievements'])->name('admin.student-achievements');
    Route::get('/validation-logs', [AdminController::class, 'validationLogs'])->name('admin.validation-logs');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
});
