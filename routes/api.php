<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormFieldController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\DashboardController;

// Rute Publik (tidak perlu login)
Route::post('/uploads', [FileUploadController::class, 'store']);
Route::post('forms/{form}/submissions', [SubmissionController::class, 'store']);
Route::get('forms/{form}', [FormController::class, 'show']); // Kita anggap publik juga
Route::post('/login', [AuthController::class, 'login']); // <-- Rute Login

// Rute Terproteksi (harus login dengan token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']); // <-- Rute Logout

    Route::post('/forms/{form}/clone', [FormController::class, 'clone']);

    // Pindahkan rute-rute yang butuh proteksi ke sini
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('forms', FormController::class)->except(['show']); // show sudah di atas
    Route::apiResource('forms.form-fields', FormFieldController::class)->scoped();

    // Gantikan Route::post(...) yang lama dengan ini.
    // Ini akan membuat GET, POST, SHOW, DELETE untuk submissions.
    Route::apiResource('forms.submissions', SubmissionController::class)->scoped()->except(['update']);
    // RUTE BARU KHUSUS ADMIN UNTUK MENGAMBIL DETAIL FORM/TEMPLATE
    Route::get('/admin/forms/{form}', [FormController::class, 'showForAdmin']);
    Route::apiResource('admins', AdminController::class);
    // Rute untuk data statistik dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
});