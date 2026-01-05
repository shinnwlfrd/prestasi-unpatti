<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentAchievementController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Di sini Anda dapat mendaftarkan rute API untuk aplikasi Anda.
|
*/

// Contoh rute API sederhana yang memerlukan otentikasi
// routes/api.php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('student')->group(function () {
        Route::post('/achievements', [StudentAchievementController::class, 'store']);
        Route::get('/achievements', [StudentAchievementController::class, 'index']);
    });
});    return $request->user();