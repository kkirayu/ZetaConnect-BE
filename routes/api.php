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

// Google OAuth Routes
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
