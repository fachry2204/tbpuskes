<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MeDashboardController extends Controller
{
    public function patient(Request $request): JsonResponse
    {
        abort_unless($request->user()->role === 'pasien', 403);

        $patient = Patient::query()->with('cadre')->where('user_id', $request->user()->id)->firstOrFail();
        $today = today(config('app.timezone'));
        $reports = DB::table('medication_reports')->where('patient_id', $patient->id)->whereDate('report_date', $today);
        $medicationSchedules = DB::table('medication_schedule_times as times')
            ->join('patient_medication_plans as plans', 'plans.id', '=', 'times.patient_medication_plan_id')
            ->leftJoin('medications', 'medications.id', '=', 'plans.medication_id')
            ->where('plans.patient_id', $patient->id)
            ->where('plans.is_active', true)
            ->whereDate('plans.start_date', '<=', $today)
            ->where(fn ($query) => $query->whereNull('plans.end_date')->orWhereDate('plans.end_date', '>=', $today))
            ->orderBy('times.time_of_day')
            ->get([
                'times.id as schedule_time_id',
                'times.time_of_day',
                'times.label',
                'plans.dose',
                'plans.dose_unit',
                DB::raw("COALESCE(medications.name, 'Obat belum ditentukan') as medication_name"),
            ]);
        $scheduleTimeIds = $medicationSchedules->pluck('schedule_time_id');
        $activeScheduleReports = (clone $reports)->whereIn('schedule_time_id', $scheduleTimeIds);
        $reportedScheduleIds = (clone $activeScheduleReports)->pluck('schedule_time_id')->filter()->all();
        $nextMedication = $medicationSchedules->first(fn ($schedule) => !in_array($schedule->schedule_time_id, $reportedScheduleIds));
        $next = DB::table('control_schedules')
            ->join('treatment_places', 'treatment_places.id', '=', 'control_schedules.treatment_place_id')
            ->where('control_schedules.patient_id', $patient->id)
            ->whereDate('control_date', '>=', $today)
            ->whereNotIn('control_schedules.status', ['cancelled', 'attended', 'missed'])
            ->orderBy('control_date')
            ->orderBy('start_time')
            ->first(['control_schedules.*', 'treatment_places.name as place_name']);

        return response()->json(['success' => true, 'data' => [
            'patient' => $patient,
            'taken_today' => (clone $activeScheduleReports)->where('medication_taken', true)->count(),
            'reports_today' => (clone $activeScheduleReports)->count(),
            'medication_schedules' => $medicationSchedules,
            'next_medication' => $nextMedication,
            'next_control' => $next,
        ]]);
    }
    public function cadre(Request $request): JsonResponse
    {
        abort_unless($request->user()->role === 'kader', 403);

        $today = today(config('app.timezone'));
        $cadreId = DB::table('cadres')->where('user_id', $request->user()->id)->value('id');
        $patientIds = Patient::query()->where('cadre_id', $cadreId)->where('status', 'active')->pluck('id');
        $reports = DB::table('medication_reports')->whereIn('patient_id', $patientIds)->whereDate('report_date', $today);
        $reportedToday = (clone $reports)->distinct()->count('patient_id');

        return response()->json(['success' => true, 'data' => [
            'patients' => $patientIds->count(),
            'taken_today' => (clone $reports)->where('medication_taken', true)->distinct('patient_id')->count('patient_id'),
            'not_taken_today' => (clone $reports)->where('medication_taken', false)->distinct('patient_id')->count('patient_id'),
            'reported_today' => $reportedToday,
            'unreported_today' => max(0, $patientIds->count() - $reportedToday),
            'side_effects' => (clone $reports)->where('has_side_effect', true)->count(),
            'controls_today' => DB::table('control_schedules')->whereIn('patient_id', $patientIds)->whereDate('control_date', $today)->count(),
        ]]);
    }

    public function cadrePatients(Request $request): JsonResponse
    {
        abort_unless($request->user()->role === 'kader', 403);
        $cadreId = DB::table('cadres')->where('user_id', $request->user()->id)->value('id');
        $today = today(config('app.timezone'));

        $patients = Patient::query()
            ->where('cadre_id', $cadreId)
            ->where('status', 'active')
            ->select(['id', 'full_name', 'phone', 'rt', 'rw', 'status'])
            ->selectSub(fn ($query) => $query->from('medication_reports')->whereColumn('medication_reports.patient_id', 'patients.id')->whereDate('report_date', $today)->selectRaw('COUNT(*)'), 'reports_today')
            ->selectSub(fn ($query) => $query->from('medication_reports')->whereColumn('medication_reports.patient_id', 'patients.id')->whereDate('report_date', $today)->where('medication_taken', false)->selectRaw('COUNT(*)'), 'not_taken_today')
            ->orderBy('full_name')
            ->get();

        return response()->json(['success' => true, 'data' => $patients]);
    }

    public function cadreSchedules(Request $request): JsonResponse
    {
        abort_unless($request->user()->role === 'kader', 403);
        $cadreId = DB::table('cadres')->where('user_id', $request->user()->id)->value('id');

        $schedules = DB::table('control_schedules as schedules')
            ->join('patients', 'patients.id', '=', 'schedules.patient_id')
            ->join('treatment_places', 'treatment_places.id', '=', 'schedules.treatment_place_id')
            ->where('patients.cadre_id', $cadreId)
            ->where('patients.status', 'active')
            ->whereDate('schedules.control_date', '>=', today(config('app.timezone')))
            ->orderBy('schedules.control_date')
            ->orderBy('schedules.start_time')
            ->limit(20)
            ->get(['schedules.id', 'schedules.control_date', 'schedules.start_time', 'schedules.status', 'patients.full_name', 'treatment_places.name as treatment_place']);

        return response()->json(['success' => true, 'data' => $schedules]);
    }
}
