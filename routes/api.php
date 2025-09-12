<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormFieldController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\AuthController; // <-- Tambahkan ini

// Rute Publik (tidak perlu login)
Route::post('forms/{form}/submissions', [SubmissionController::class, 'store']);
Route::get('forms/{form}', [FormController::class, 'show']); // Kita anggap publik juga
Route::post('/login', [AuthController::class, 'login']); // <-- Rute Login

// Rute Terproteksi (harus login dengan token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']); // <-- Rute Logout

    // Pindahkan rute-rute yang butuh proteksi ke sini
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('forms', FormController::class)->except(['show']); // show sudah di atas
    Route::apiResource('forms.form-fields', FormFieldController::class)->scoped();

    // Gantikan Route::post(...) yang lama dengan ini.
    // Ini akan membuat GET, POST, SHOW, DELETE untuk submissions.
    Route::apiResource('forms.submissions', SubmissionController::class)->scoped()->except(['update']);
});