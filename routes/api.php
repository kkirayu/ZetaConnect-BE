<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PharmacyDashboardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('users', UserController::class);
Route::apiResource('pets', PetController::class);
Route::apiResource('appointments', AppointmentController::class);

// Google OAuth Routes
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::prefix('pharmacy')->group(function () {

    Route::get('/dashboard', [PharmacyDashboardController::class, 'dashboard']);

    Route::get('/low-stock', [PharmacyDashboardController::class, 'lowStock']);

    Route::get('/patient-demographics', [PharmacyDashboardController::class, 'patientDemographics']);
});
