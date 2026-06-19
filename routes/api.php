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
use App\Http\Controllers\FeedbackController;
use \App\Http\Controllers\DoctorController;
use App\Http\Controllers\PetTipController;

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

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::post('/auth/login', [AuthController::class, 'login']);

Route::prefix('pharmacy')->group(function () {
    Route::get('/dashboard', [PharmacyController::class, 'dashboard']);
    Route::get('/low-stock', [PharmacyController::class, 'lowStock']);
    Route::get('/patient-demographics', [PharmacyController::class, 'patientDemographics']);
    Route::get('/expiring-products', [PharmacyController::class, 'expiringProducts']);
    Route::get('/inventory-summary', [PharmacyController::class, 'inventorySummary']);

    // Stock Monitoring
    Route::get('/products', [PharmacyController::class, 'products']);
    Route::delete('/products/{id}', [PharmacyController::class, 'deleteProduct']);

    // Prescriptions
    Route::get('/prescriptions', [PharmacyController::class, 'prescriptions']);
    Route::patch('/prescriptions/{medicalRecordId}/status', [PharmacyController::class, 'updatePrescriptionStatus']);
});

Route::apiResource('suppliers', SupplierController::class);

Route::post('/feedbacks', [FeedbackController::class, 'store']);
Route::get('/feedbacks', [FeedbackController::class, 'index']);

Route::apiResource('doctors', DoctorController::class);
Route::apiResource('pet-tips', PetTipController::class);
