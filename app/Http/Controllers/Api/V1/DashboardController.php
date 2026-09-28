<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MedicationReport;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff', 'kader'], true), 403);
        $patientQuery = Patient::query()->where('status', 'active');
        if ($request->user()->role === 'kader') $patientQuery->whereHas('cadre', fn ($query) => $query->where('user_id', $request->user()->id));
        $patientIds = $patientQuery->pluck('id'); $today = today(config('app.timezone'));
        $reported = MedicationReport::query()->whereIn('patient_id', $patientIds)->whereDate('report_date', $today);
        $history = collect(range(6, 0))->map(function ($offset) use ($today, $patientIds) {
            $date = $today->copy()->subDays($offset);
            $taken = MedicationReport::whereIn('patient_id', $patientIds)->whereDate('report_date', $date)->where('medication_taken', true)->distinct()->count('patient_id');
            return ['date' => $date->toDateString(), 'percent' => $patientIds->count() ? round($taken / $patientIds->count() * 100) : 0];
        });
        return response()->json(['success' => true, 'data' => [
            'total_active_patients' => $patientIds->count(),
            'taken_today' => (clone $reported)->where('medication_taken', true)->distinct('patient_id')->count('patient_id'),
            'not_taken_today' => (clone $reported)->where('medication_taken', false)->distinct('patient_id')->count('patient_id'),
            'side_effects_today' => (clone $reported)->where('has_side_effect', true)->count(),
            'controls_today' => DB::table('control_schedules')->whereIn('patient_id', $patientIds)->whereDate('control_date', $today)->count(),
            'unreported_today' => $patientIds->count() - (clone $reported)->distinct()->count('patient_id'),
            'history' => $history,
            'recent_reports' => (clone $reported)->with('patient:id,full_name')->latest('server_received_at')->limit(6)->get(['id','patient_id','medication_taken','has_side_effect','status','server_received_at','formatted_address']),
            'schedules' => DB::table('control_schedules')->join('patients', 'patients.id', '=', 'control_schedules.patient_id')->whereIn('patient_id', $patientIds)->whereDate('control_date', $today)->orderBy('start_time')->limit(5)->get(['control_schedules.id','patients.full_name','start_time','control_schedules.status']),
        ]]);
    }
}
