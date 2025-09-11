<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormFieldController;
use App\Http\Controllers\SubmissionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Menambahkan rute resource untuk categories di sini
Route::apiResource('categories', CategoryController::class);
Route::apiResource('forms', FormController::class);
// Ini adalah Nested Route. Rute untuk form-fields berada "di dalam" forms.
Route::apiResource('forms.form-fields', FormFieldController::class)->scoped();
// Rute untuk submit data ke sebuah form
Route::post('forms/{form}/submissions', [SubmissionController::class, 'store']);