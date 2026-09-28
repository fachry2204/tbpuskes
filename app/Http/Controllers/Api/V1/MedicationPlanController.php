<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicationPlanController extends Controller
{
    public function store(Request $request, Patient $patient): JsonResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);
        $data = $request->validate(['medication_id' => ['nullable', 'exists:medications,id'], 'dose' => ['required', 'string', 'max:50'], 'dose_unit' => ['nullable', 'string', 'max:30'], 'frequency_per_day' => ['required', 'integer', 'between:1,6'], 'start_date' => ['required', 'date'], 'end_date' => ['nullable', 'date', 'after_or_equal:start_date'], 'instructions' => ['nullable', 'string', 'max:1000'], 'periods' => ['nullable', 'array', 'min:1', 'max:3'], 'periods.*' => ['in:Pagi,Siang,Malam'], 'schedule_times' => ['nullable', 'array', 'min:1', 'max:6'], 'schedule_times.*.time_of_day' => ['required_with:schedule_times', 'date_format:H:i'], 'schedule_times.*.label' => ['nullable', 'string', 'max:50']]);
        $times = $this->resolveTimes($data);
        abort_if(count($times) !== (int) $data['frequency_per_day'], 422, 'Jumlah jadwal harus sama dengan frekuensi minum obat per hari.');
        abort_unless($patient->status === 'active', 422, 'Pasien tidak aktif.');
        $plan = DB::transaction(function () use ($data, $patient, $times) { unset($data['schedule_times'], $data['periods']); $planId = DB::table('patient_medication_plans')->insertGetId(array_merge($data, ['patient_id' => $patient->id, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()])); foreach ($times as $index => $time) DB::table('medication_schedule_times')->insert(['patient_medication_plan_id' => $planId, 'time_of_day' => $time['time_of_day'], 'label' => $time['label'] ?? null, 'sequence' => $index + 1, 'created_at' => now(), 'updated_at' => now()]); return DB::table('patient_medication_plans')->where('id', $planId)->first(); });
        return response()->json(['success' => true, 'message' => 'Rencana obat berhasil disimpan.', 'data' => $plan], 201);
    }

    private function resolveTimes(array $data): array
    {
        if (isset($data['schedule_times'])) return $data['schedule_times'];
        if ((int) $data['frequency_per_day'] === 1 && empty($data['periods'])) return [['label' => 'Sekali sehari', 'time_of_day' => null]];
        $preset = ['Pagi' => '07:00', 'Siang' => '13:00', 'Malam' => '19:00'];
        return array_map(fn (string $period): array => ['label' => $period, 'time_of_day' => $preset[$period]], $data['periods']);
    }
}
