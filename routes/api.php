<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientController;

// Rrugët publike për autentikim
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rrugët e mbrojtura me Sanctum middleware dhe role-based access
Route::middleware(['auth:sanctum', 'role:admin,nurse,doctor'])->group(function () {
    Route::get('/patients', [PatientController::class, 'index']);
    Route::post('/patients', [PatientController::class, 'store']);
    Route::get('/patients/{id}', [PatientController::class, 'show']);
    Route::put('/patients/{id}', [PatientController::class, 'update']);
    Route::get('/patients/stats/symptoms', [PatientController::class, 'statsSymptoms']);
    Route::get('/patients/stats/visits', [PatientController::class, 'statsVisits']);
    Route::get('/patients/stats/recovery', [PatientController::class, 'statsRecovery']);
    Route::get('/patients/export/excel', [PatientController::class, 'exportExcel']);
    Route::get('/patients/export/pdf', [PatientController::class, 'exportPDF']);
});

// Vetëm admin mund të fshijë pacientë
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::delete('/patients/{id}', [PatientController::class, 'destroy']);
});

// Rruga për marrjen e të dhënave të përdoruesit të autentikuar
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
