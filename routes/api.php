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
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PetTipController;
use App\Http\Controllers\DiagnosisDictionaryController;
use App\Http\Controllers\SurgeryController;
use App\Http\Controllers\VaccinationController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\LabResultController;
use App\Http\Controllers\EReceiptController;
use App\Http\Controllers\MedicalCertificateController;
use App\Http\Controllers\StockMutationController;
use App\Http\Controllers\ProductController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth User Session
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user()->load('pets');
    });
    Route::post('/user/profile', [UserController::class, 'updateProfile']);
});

// Authentication Open Routes (OTP, Register, Login)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Google OAuth Routes
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// Core Resource Routes (User, Pet, Appointment)
Route::apiResource('users', UserController::class);
Route::apiResource('pets', PetController::class);
Route::get('available-sessions', [AppointmentController::class, 'getAvailableSessions']);
Route::apiResource('appointments', AppointmentController::class);
Route::apiResource('products', ProductController::class);

// Finance & Services Routes
// ==============================================
// ADMIN & RECEPTIONIST ROUTES
// ==============================================
Route::middleware(['auth:sanctum', 'role:admin,resepsionis,Resepsionis'])->group(function () {
    Route::apiResource('users', UserController::class);
    
    // Clinic Settings & System Logs 
    Route::get('clinic-settings', [ClinicSettingController::class, 'index']);
    Route::post('clinic-settings', [ClinicSettingController::class, 'update']);
    Route::get('/audit-logs', [AuditLogController::class, 'index']);
    
    // Admin Dashboard
    Route::get('admin/dashboard/summary', [ReportController::class, 'dashboardSummary']);

    // Reports Group 
    Route::prefix('reports')->group(function () {
        Route::get('financial', [ReportController::class, 'financial']);
        Route::get('demographics', [ReportController::class, 'demographics']);
        Route::get('stock-mutation', [ReportController::class, 'stockMutation']);
    });
});

// ==============================================
// OWNER & RECEPTIONIST ROUTES
// ==============================================
Route::middleware(['auth:sanctum', 'role:owner,pemilik hewan,resepsionis,Resepsionis'])->group(function () {
    Route::apiResource('pets', PetController::class);
    Route::apiResource('appointments', AppointmentController::class);
});

// ==============================================
// PUBLIC ATAU SHARED ROUTES
// ==============================================
// Finance & Services Routes 
Route::apiResource('services', ServiceController::class)->except(['create', 'edit']);
Route::apiResource('invoices', InvoiceController::class)->except(['create', 'edit']);
Route::apiResource('payments', PaymentController::class)->except(['create', 'edit', 'update']);
Route::patch('payments/{id}/refund', [PaymentController::class, 'refund']);

// Clinic Settings & System Logs
Route::get('clinic-settings', [ClinicSettingController::class, 'index']);
Route::post('clinic-settings', [ClinicSettingController::class, 'update']);
Route::get('/audit-logs', [AuditLogController::class, 'index']);

// Reports Group
Route::prefix('reports')->group(function () {
    Route::get('financial', [ReportController::class, 'financial']);
    Route::get('demographics', [ReportController::class, 'demographics']);
    Route::get('stock-mutation', [ReportController::class, 'stockMutation']);
});

// Pharmacy Group
// Pharmacy Group 
Route::prefix('auth')->group(function(){
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::get('/google', [AuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::apiResource('pet-tips', PetTipController::class);
Route::apiResource('feedbacks', FeedbackController::class);

Route::get('/audit-logs', [AuditLogController::class, 'index']);

Route::prefix('pharmacy')->group(function () {
    Route::get('/dashboard', [PharmacyController::class, 'dashboard']);
    Route::get('/low-stock', [PharmacyController::class, 'lowStock']);
    Route::get('/patient-demographics', [PharmacyController::class, 'patientDemographics']);
    Route::get('/expiring-products', [PharmacyController::class, 'expiringProducts']);
    Route::get('/inventory-summary', [PharmacyController::class, 'inventorySummary']);


    // STOCK MUTATION
    Route::get('/stock-mutations', [StockMutationController::class, 'index']);
    Route::post('/stock-mutations', [StockMutationController::class, 'store']);
    Route::put('/stock-mutations/{id}', [StockMutationController::class, 'update']);
    Route::delete('/stock-mutations/{id}', [StockMutationController::class, 'destroy']);

    // Prescriptions
    Route::get('/prescriptions', [PharmacyController::class, 'prescriptions']);
    Route::patch('/prescriptions/{medicalRecordId}/status', [PharmacyController::class, 'updatePrescriptionStatus']);
});

// Other Master Data Routes
Route::apiResource('suppliers', SupplierController::class);
Route::post('/feedbacks', [FeedbackController::class, 'store']);
Route::get('/feedbacks', [FeedbackController::class, 'index']);
Route::apiResource('doctors', DoctorController::class);
Route::apiResource('pet-tips', PetTipController::class);
Route::apiResource('medical-records', MedicalRecordController::class);

Route::middleware('auth:sanctum')->prefix('doctor')->group(function () {
    // Diagnosis Dictionary Routes
    Route::apiResource('diagnoses', DiagnosisDictionaryController::class);

    // Surgery Routes
    Route::apiResource('surgeries', SurgeryController::class);

    // Vaccination Routes
    Route::apiResource('vaccinations', VaccinationController::class);

    // Medical Records (SOAP) Routes
    

    // Lab Results Routes
    Route::apiResource('lab-results', LabResultController::class);

    // E-Receipts Routes
    Route::apiResource('e-receipts', EReceiptController::class);

    // Medical Certificates Routes
    Route::apiResource('medical-certificates', MedicalCertificateController::class);
});

// Patient Profile Update Route
Route::middleware('auth:sanctum')->put('patients/{id}/profile', [PetController::class, 'updateProfile']);

Route::apiResource('doctors', DoctorController::class);
