<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\Api\PharmacyController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ClinicSettingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuditLogController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('users', UserController::class);
Route::apiResource('pets', PetController::class);
Route::apiResource('appointments', AppointmentController::class);
Route::apiResource('services', ServiceController::class)->except(['create', 'edit']);
Route::apiResource('invoices', InvoiceController::class)->except(['create', 'edit']);
Route::apiResource('payments', PaymentController::class)->except(['create', 'edit', 'update']);
Route::patch('payments/{id}/refund', [PaymentController::class, 'refund']);

Route::get('clinic-settings', [ClinicSettingController::class, 'index']);
Route::post('clinic-settings', [ClinicSettingController::class, 'update']);

Route::prefix('reports')->group(function () {
    Route::get('financial', [ReportController::class, 'financial']);
    Route::get('demographics', [ReportController::class, 'demographics']);
    Route::get('stock-mutation', [ReportController::class, 'stockMutation']);
});

Route::get('/audit-logs', [AuditLogController::class, 'index']);

// Google OAuth Routes
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);



Route::prefix('pharmacy')->group(function () {

    Route::get('/dashboard', [PharmacyController::class, 'dashboard']);

    Route::get('/low-stock', [PharmacyController::class, 'lowStock']);

    Route::get('/patient-demographics', [PharmacyController::class, 'patientDemographics']);

    // Opsional
    Route::get('/expiring-products', [PharmacyController::class, 'expiringProducts']);

    Route::get('/inventory-summary', [PharmacyController::class, 'inventorySummary']);

    // Stock Monitoring
    Route::get('/products', [PharmacyController::class, 'products']);
    Route::delete('/products/{id}', [PharmacyController::class, 'deleteProduct']);
});

Route::apiResource('suppliers', SupplierController::class);
