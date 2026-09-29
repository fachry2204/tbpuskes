<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffControlScheduleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);
        $items = DB::table('control_schedules as schedules')
            ->join('patients', 'patients.id', '=', 'schedules.patient_id')
            ->join('treatment_places as places', 'places.id', '=', 'schedules.treatment_place_id')
            ->select('schedules.id', 'patients.id as patient_id', 'patients.full_name', 'patients.phone', 'schedules.treatment_place_id', 'places.name as treatment_place', 'schedules.control_date', 'schedules.start_time', 'schedules.end_time', 'schedules.purpose', 'schedules.notes', 'schedules.status')
            ->orderByDesc('schedules.control_date')->orderBy('schedules.start_time')->orderBy('schedules.id')
            ->paginate(20);
        return response()->json(['success' => true, 'data' => $items->items(), 'meta' => ['current_page' => $items->currentPage(), 'last_page' => $items->lastPage(), 'total' => $items->total()]]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);
        $data = $request->validate(['patient_id' => ['required', 'exists:patients,id'], 'treatment_place_id' => ['required', 'exists:treatment_places,id'], 'control_date' => ['required', 'date'], 'start_time' => ['required', 'date_format:H:i'], 'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'], 'purpose' => ['nullable', 'string', 'max:255'], 'notes' => ['nullable', 'string', 'max:2000']]);
        abort_unless(Patient::query()->whereKey($data['patient_id'])->where('status', 'active')->exists(), 422, 'Pasien tidak aktif.');
        $id = DB::table('control_schedules')->insertGetId(array_merge($data, ['status' => 'scheduled', 'created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]));
        return response()->json(['success' => true, 'message' => 'Jadwal kontrol berhasil dibuat.', 'data' => DB::table('control_schedules')->find($id)], 201);
    }

    public function update(Request $request, int $schedule): JsonResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);
        abort_unless(DB::table('control_schedules')->where('id', $schedule)->exists(), 404, 'Jadwal kontrol tidak ditemukan.');
        $data = $request->validate([
            'treatment_place_id' => ['required', 'exists:treatment_places,id'],
            'control_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        DB::table('control_schedules')->where('id', $schedule)->update(array_merge($data, ['updated_at' => now()]));
        return response()->json(['success' => true, 'message' => 'Jadwal kontrol diperbarui.', 'data' => DB::table('control_schedules')->find($schedule)]);
    }

    public function destroy(Request $request, int $schedule): JsonResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);
        abort_unless(DB::table('control_schedules')->where('id', $schedule)->delete(), 404, 'Jadwal kontrol tidak ditemukan.');
        return response()->json(['success' => true, 'message' => 'Jadwal kontrol dihapus.']);
    }

    public function updateStatus(Request $request, int $schedule): JsonResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);
        $data = $request->validate(['status' => ['required', 'in:scheduled,missed,rescheduled,attended']]);
        $updated = DB::table('control_schedules')->where('id', $schedule)->update([
            'status' => $data['status'],
            'attended_at' => $data['status'] === 'attended' ? now() : null,
            'updated_at' => now(),
        ]);
        abort_unless($updated, 404, 'Jadwal kontrol tidak ditemukan.');
        return response()->json(['success' => true, 'message' => 'Status jadwal kontrol diperbarui.']);
    }
}
