<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MedicationReportController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ControlScheduleController;
use App\Http\Controllers\Api\V1\PatientController;
use App\Http\Controllers\Api\V1\CadreController;
use App\Http\Controllers\Api\V1\MedicationPlanController;
use App\Http\Controllers\Api\V1\MedicationController;
use App\Http\Controllers\Api\V1\StaffControlScheduleController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\TreatmentPlaceController;
use App\Http\Controllers\Api\V1\UserManagementController;
use App\Http\Controllers\Api\V1\MeDashboardController;
use App\Http\Controllers\Api\V1\PushSubscriptionController;
use App\Http\Controllers\Api\V1\ReportExportController;
use App\Http\Controllers\Api\V1\MedicationMonitoringController;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', fn () => response()->json([
        'success' => true,
        'message' => 'Empati TB API aktif.',
        'data' => ['server_time' => now()->toIso8601String()],
    ]));
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/me/medication/reports', [MedicationReportController::class, 'store'])->middleware('auth:sanctum');
    Route::post('/me/medication/reports/{report}/side-effect', [MedicationReportController::class, 'addSideEffect'])->middleware('auth:sanctum');
    Route::get('/dashboard/summary', [DashboardController::class, 'summary'])->middleware('auth:sanctum');
    Route::get('/me/control-schedules', [ControlScheduleController::class, 'mine'])->middleware('auth:sanctum');
    Route::get('/patients', [PatientController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/patients', [PatientController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/patients/statistics', [PatientController::class, 'statistics'])->middleware('auth:sanctum');
    Route::get('/patients/{patient}/history', [PatientController::class, 'history'])->middleware('auth:sanctum');
    Route::get('/patients/{patient}', [PatientController::class, 'show'])->middleware('auth:sanctum');
    Route::put('/patients/{patient}', [PatientController::class, 'update'])->middleware('auth:sanctum');
    Route::post('/patients/{patient}/deactivate', [PatientController::class, 'deactivate'])->middleware('auth:sanctum');
    Route::delete('/patients/{patient}', [PatientController::class, 'destroy'])->middleware('auth:sanctum');
    Route::get('/cadres', [CadreController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/cadres', [CadreController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/cadres/{cadre}', [CadreController::class, 'show'])->middleware('auth:sanctum');
    Route::put('/cadres/{cadre}', [CadreController::class, 'update'])->middleware('auth:sanctum');
    Route::post('/cadres/{cadre}/deactivate', [CadreController::class, 'deactivate'])->middleware('auth:sanctum');
    Route::delete('/cadres/{cadre}', [CadreController::class, 'destroy'])->middleware('auth:sanctum');
    Route::post('/patients/{patient}/medication-plan', [MedicationPlanController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/medications', [MedicationController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/medications', [MedicationController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/medications/{medication}', [MedicationController::class, 'show'])->middleware('auth:sanctum');
    Route::put('/medications/{medication}', [MedicationController::class, 'update'])->middleware('auth:sanctum');
    Route::post('/medications/{medication}/deactivate', [MedicationController::class, 'deactivate'])->middleware('auth:sanctum');
    Route::get('/control-schedules', [StaffControlScheduleController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/control-schedules', [StaffControlScheduleController::class, 'store'])->middleware('auth:sanctum');
    Route::put('/control-schedules/{schedule}', [StaffControlScheduleController::class, 'update'])->middleware('auth:sanctum');
    Route::delete('/control-schedules/{schedule}', [StaffControlScheduleController::class, 'destroy'])->middleware('auth:sanctum');
    Route::patch('/control-schedules/{schedule}/status', [StaffControlScheduleController::class, 'updateStatus'])->middleware('auth:sanctum');
    Route::get('/notifications', [NotificationController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->middleware('auth:sanctum');
    Route::get('/treatment-places', [TreatmentPlaceController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/treatment-places', [TreatmentPlaceController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/treatment-places/{treatmentPlace}', [TreatmentPlaceController::class, 'show'])->middleware('auth:sanctum');
    Route::put('/treatment-places/{treatmentPlace}', [TreatmentPlaceController::class, 'update'])->middleware('auth:sanctum');
    Route::post('/treatment-places/{treatmentPlace}/deactivate', [TreatmentPlaceController::class, 'deactivate'])->middleware('auth:sanctum');
    Route::get('/users', [UserManagementController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/users', [UserManagementController::class, 'store'])->middleware('auth:sanctum');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->middleware('auth:sanctum');
    Route::post('/users/{user}/deactivate', [UserManagementController::class, 'deactivate'])->middleware('auth:sanctum');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->middleware('auth:sanctum');
    Route::get('/me/dashboard', [MeDashboardController::class, 'patient'])->middleware('auth:sanctum');
    Route::get('/kader/dashboard', [MeDashboardController::class, 'cadre'])->middleware('auth:sanctum');
    Route::get('/kader/patients', [MeDashboardController::class, 'cadrePatients'])->middleware('auth:sanctum');
    Route::get('/kader/medication-reports/daily', [MeDashboardController::class, 'cadreDailyMedicationReports'])->middleware('auth:sanctum');
    Route::get('/kader/control-schedules', [MeDashboardController::class, 'cadreSchedules'])->middleware('auth:sanctum');
    Route::get('/me/medication/history', [MedicationReportController::class, 'history'])->middleware('auth:sanctum');
    Route::get('/medication-monitoring', [MedicationMonitoringController::class, 'index'])->middleware('auth:sanctum');
    Route::put('/medication-monitoring/{report}', [MedicationMonitoringController::class, 'update'])->middleware('auth:sanctum');
    Route::delete('/medication-monitoring/{report}', [MedicationMonitoringController::class, 'destroy'])->middleware('auth:sanctum');
    Route::get('/medication-monitoring/map', [MedicationMonitoringController::class, 'map'])->middleware('auth:sanctum');
    Route::get('/medication-monitoring/unreported', [MedicationMonitoringController::class, 'unreported'])->middleware('auth:sanctum');
    Route::post('/medication-monitoring/{report}/verify', [MedicationMonitoringController::class, 'verify'])->middleware('auth:sanctum');
    Route::get('/medication-monitoring/{report}/photo', [MedicationMonitoringController::class, 'photo'])->middleware('auth:sanctum');
    Route::get('/side-effects', [MedicationMonitoringController::class, 'sideEffects'])->middleware('auth:sanctum');
    Route::post('/side-effects/{sideEffect}/follow-up', [MedicationMonitoringController::class, 'followUp'])->middleware('auth:sanctum');
    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/reports/adherence.csv', [ReportExportController::class, 'csv'])->middleware('auth:sanctum');
    Route::get('/reports/adherence.xlsx', [ReportExportController::class, 'excel'])->middleware('auth:sanctum');
    Route::get('/reports/adherence.pdf', [ReportExportController::class, 'pdf'])->middleware('auth:sanctum');
    Route::get('/auth/me', fn (\Illuminate\Http\Request $request) => response()->json([
        'success' => true,
        'data' => $request->user(),
    ]))->middleware('auth:sanctum');
});
