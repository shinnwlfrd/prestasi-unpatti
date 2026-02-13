<?php

use App\Http\Controllers\StudentAchievementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Di sini Anda dapat mendaftarkan rute API untuk aplikasi Anda.
|
*/

// Contoh rute API untuk user yang terautentikasi
Route::middleware(['auth:sanctum'])->group(function () {
    // Get current user info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Student achievement endpoints
    Route::prefix('student')->group(function () {
        Route::post('/achievements', [StudentAchievementController::class, 'store']);
        Route::get('/achievements', [StudentAchievementController::class, 'index']);
    });

    // SK Search
    Route::get('/sk-documents/search', [\App\Http\Controllers\Api\SKSearchController::class, 'search']);
});

// SIKAD Integration API (akan diimplementasikan)
Route::prefix('sikad')->group(function () {
    // Endpoint placeholder untuk integrasi SIKAD
    // Route::get('/student/{nim}', [SikadController::class, 'getStudent']);
    // Route::post('/sync/{nim}', [SikadController::class, 'syncStudent']);
});
