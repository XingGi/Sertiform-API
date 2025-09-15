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
Route::get('forms/{form:slug}', [FormController::class, 'show']);
Route::post('forms/{form:slug}/submissions', [SubmissionController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']); // <-- Rute Login

// Rute Terproteksi (harus login dengan token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    Route::get('/forms/{form}/submissions', [SubmissionController::class, 'index']);

    Route::post('/forms/{form}/clone', [FormController::class, 'clone']);
    Route::get('/admin/forms/{form}', [FormController::class, 'showForAdmin']);

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('forms', FormController::class)->except(['show']); // show sudah di atas
    Route::apiResource('forms.form-fields', FormFieldController::class)->scoped();
    Route::apiResource('admins', AdminController::class);
    Route::post('/form-fields/update-order', [FormFieldController::class, 'updateOrder']);
});