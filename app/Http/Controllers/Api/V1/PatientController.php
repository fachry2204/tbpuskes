<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientRequest;
use App\Models\Patient;
use App\Models\User;
use App\Services\PatientMedicationScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PatientController extends Controller
{
    public function __construct(private readonly PatientMedicationScheduleService $medicationScheduleService) {}
    public function show(Request $request, Patient $patient): JsonResponse { abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403); return response()->json(['success' => true, 'data' => $patient->load('cadre')]); }
    public function history(Request $request, Patient $patient): JsonResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);
        $controls = DB::table('control_schedules as schedules')->join('treatment_places as places', 'places.id', '=', 'schedules.treatment_place_id')->where('schedules.patient_id', $patient->id)->orderByDesc('schedules.control_date')->orderByDesc('schedules.start_time')->select('schedules.id','schedules.control_date','schedules.start_time','schedules.purpose','schedules.status','schedules.attended_at','places.name as treatment_place')->get();
        $medications = DB::table('medication_reports')->where('patient_id', $patient->id)->orderByDesc('report_date')->orderByDesc('server_received_at')->select('id','report_date','scheduled_time','medication_taken','not_taken_reason','has_side_effect','side_effect_category','status','server_received_at')->get();
        return response()->json(['success'=>true,'data'=>['patient'=>$patient->load('cadre'),'controls'=>$controls,'medications'=>$medications]]);
    }
    public function index(Request $request): JsonResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);
        $items = Patient::query()->with(['cadre:id,full_name,phone'])->when($request->string('search')->trim()->value(), fn ($query, $search) => $query->where(fn ($q) => $q->where('full_name', 'like', "%{$search}%")->orWhere('nik', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%")->orWhere('medical_record_number', 'like', "%{$search}%")))->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))->latest()->paginate(min((int) $request->input('per_page', 20), 100));
        $base = Patient::query()->where('status', 'active');
        return response()->json(['success' => true, 'data' => $items->items(), 'meta' => ['current_page' => $items->currentPage(), 'last_page' => $items->lastPage(), 'total' => $items->total(), 'stats' => ['total' => (clone $base)->count(), 'male' => (clone $base)->where('gender', 'L')->count(), 'female' => (clone $base)->where('gender', 'P')->count(), 'under_40' => (clone $base)->whereDate('birth_date', '>', today()->subYears(40))->count(), 'over_40' => (clone $base)->whereDate('birth_date', '<=', today()->subYears(40))->count()]]]);
    }

    /** Ringkasan statistik pasien untuk halaman laporan admin. */
    public function statistics(Request $request): JsonResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);

        $today = today(config('app.timezone'));
        $patients = Patient::query();
        $activePatients = (clone $patients)->where('status', 'active');
        $activePatientIds = (clone $activePatients)->pluck('id');
        $reportsToday = DB::table('medication_reports')
            ->whereIn('patient_id', $activePatientIds)
            ->whereDate('report_date', $today);
        $activeTotal = $activePatientIds->count();
        $reportedToday = (clone $reportsToday)->distinct()->count('patient_id');

        $adherenceHistory = collect(range(6, 0))->map(function (int $offset) use ($today, $activePatientIds, $activeTotal): array {
            $date = $today->copy()->subDays($offset);
            $taken = DB::table('medication_reports')
                ->whereIn('patient_id', $activePatientIds)
                ->whereDate('report_date', $date)
                ->where('medication_taken', true)
                ->distinct()
                ->count('patient_id');

            return [
                'date' => $date->toDateString(),
                'total' => $taken,
                'percent' => $activeTotal ? (int) round($taken / $activeTotal * 100) : 0,
            ];
        });

        return response()->json(['success' => true, 'data' => [
            'generated_at' => now()->toIso8601String(),
            'summary' => [
                'total' => (clone $patients)->count(),
                'active' => $activeTotal,
                'completed' => (clone $patients)->where('status', 'completed')->count(),
                'paused' => (clone $patients)->where('status', 'paused')->count(),
                'moved' => (clone $patients)->where('status', 'moved')->count(),
                'deceased' => (clone $patients)->where('status', 'deceased')->count(),
            ],
            'gender' => [
                'male' => (clone $patients)->where('gender', 'L')->count(),
                'female' => (clone $patients)->where('gender', 'P')->count(),
            ],
            'age' => [
                'under_40' => (clone $patients)->whereDate('birth_date', '>', $today->copy()->subYears(40))->count(),
                'age_40_plus' => (clone $patients)->whereDate('birth_date', '<=', $today->copy()->subYears(40))->count(),
            ],
            'cadre' => [
                'assigned' => (clone $patients)->whereNotNull('cadre_id')->count(),
                'unassigned' => (clone $patients)->whereNull('cadre_id')->count(),
            ],
            'today' => [
                'controls' => DB::table('control_schedules')->whereDate('control_date', $today)->count(),
                'taken' => (clone $reportsToday)->where('medication_taken', true)->distinct()->count('patient_id'),
                'not_taken' => (clone $reportsToday)->where('medication_taken', false)->distinct()->count('patient_id'),
                'reported' => $reportedToday,
                'not_reported' => max(0, $activeTotal - $reportedToday),
                'side_effects' => (clone $reportsToday)->where('has_side_effect', true)->count(),
            ],
            'by_status' => (clone $patients)->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->orderBy('status')->get(),
            'by_rw' => (clone $patients)->select('rw', DB::raw('COUNT(*) as total'))->groupBy('rw')->orderByDesc('total')->orderBy('rw')->get(),
            'by_rt' => (clone $patients)->select('rt', 'rw', DB::raw('COUNT(*) as total'))->groupBy('rt', 'rw')->orderByDesc('total')->orderBy('rw')->orderBy('rt')->limit(10)->get(),
            'by_treatment_place' => DB::table('patients')
                ->join('treatment_places', 'treatment_places.id', '=', 'patients.treatment_place_id')
                ->whereNull('patients.deleted_at')
                ->select('treatment_places.name', DB::raw('COUNT(*) as total'))
                ->groupBy('treatment_places.id', 'treatment_places.name')
                ->orderByDesc('total')
                ->get(),
            'adherence_history' => $adherenceHistory,
        ]]);
    }

    public function store(StorePatientRequest $request): JsonResponse
    {
        $data = $request->validated(); $photoPath = $request->file('photo')?->store('patients', 'public'); unset($data['photo']); $phone = $this->normalizePhone($data['phone']); abort_unless($phone, 422, 'Nomor HP Indonesia tidak valid.');
        $patient = DB::transaction(function () use ($data, $phone, $photoPath): Patient { $user = User::create(['name' => $data['full_name'], 'phone' => $phone, 'password' => Hash::make($data['password']), 'role' => 'pasien', 'is_active' => true]); unset($data['password']); $data['phone'] = $phone; $data['user_id'] = $user->id; $data['photo_path'] = $photoPath; $patient = Patient::create($data); $this->medicationScheduleService->ensureFor($patient); return $patient; });
        return response()->json(['success' => true, 'message' => 'Pasien berhasil dibuat.', 'data' => $patient], 201);
    }
    public function update(Request $request, Patient $patient): JsonResponse { abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403); $data=$request->validate(['full_name'=>['sometimes','string','max:255'],'nik'=>['sometimes','digits:16','unique:patients,nik,'.$patient->id],'birth_place'=>['sometimes','string','max:100'],'birth_date'=>['sometimes','date','before:today'],'gender'=>['sometimes','in:L,P'],'phone'=>['sometimes','string','max:20'],'rt'=>['sometimes','string','max:3'],'rw'=>['sometimes','string','max:3'],'full_address'=>['sometimes','string','max:1000'],'cadre_id'=>['nullable','exists:cadres,id'],'treatment_place_id'=>['sometimes','exists:treatment_places,id'],'treatment_start_date'=>['sometimes','date'],'tb_diagnosis'=>['sometimes','in:TB Paru,TB Ekstraparu'],'diagnosis_type'=>['sometimes','in:Bakteriologis,Klinis'],'status'=>['sometimes','in:active,completed,paused,moved,deceased'],'daily_dose_frequency'=>['sometimes','integer','between:1,6']]); DB::transaction(function () use ($patient, $data): void { $patient->update($data); $this->medicationScheduleService->ensureFor($patient->fresh()); }); return response()->json(['success'=>true,'message'=>'Pasien diperbarui.','data'=>$patient->fresh('cadre')]); }
    public function deactivate(Request $request, Patient $patient): JsonResponse { abort_unless(in_array($request->user()->role,['admin','staff'],true),403); $patient->update(['status'=>'paused']); return response()->json(['success'=>true,'message'=>'Pasien dinonaktifkan.']); }
    public function destroy(Request $request, Patient $patient): JsonResponse { abort_unless($request->user()->role==='admin',403); $patient->delete(); return response()->json(['success'=>true,'message'=>'Pasien dihapus.']); }

    private function normalizePhone(string $phone): ?string { $number = preg_replace('/[^0-9+]/', '', $phone) ?? ''; if (str_starts_with($number, '+62')) $number = substr($number, 1); if (str_starts_with($number, '0')) $number = '62'.substr($number, 1); return preg_match('/^628[0-9]{7,12}$/', $number) ? $number : null; }
}
