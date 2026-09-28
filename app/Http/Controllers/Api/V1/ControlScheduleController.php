<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ControlScheduleController extends Controller
{
    public function mine(Request $request): JsonResponse
    {
        $patientId = DB::table('patients')->where('user_id', $request->user()->id)->value('id');
        abort_unless($patientId, 403);
        $items = DB::table('control_schedules')->join('treatment_places', 'treatment_places.id', '=', 'control_schedules.treatment_place_id')->where('control_schedules.patient_id', $patientId)->whereDate('control_schedules.control_date', '>=', today())->orderBy('control_schedules.control_date')->select('control_schedules.*', 'treatment_places.name as treatment_place_name', 'treatment_places.address as treatment_place_address')->paginate(20);
        return response()->json(['success' => true, 'data' => $items->items(), 'meta' => ['current_page' => $items->currentPage(), 'last_page' => $items->lastPage()]]);
    }
}
