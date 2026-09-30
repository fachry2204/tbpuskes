<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicationReportRequest;
use App\Models\MedicationReport;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\GeocodingService;
use App\Services\MedicationEvidenceService;

class MedicationReportController extends Controller
{
    public function history(\Illuminate\Http\Request $request): JsonResponse { $patient=Patient::query()->where('user_id',$request->user()->id)->firstOrFail();$items=MedicationReport::query()->where('patient_id',$patient->id)->latest('server_received_at')->paginate(min((int)$request->input('per_page',20),100));return response()->json(['success'=>true,'data'=>$items->items(),'meta'=>['total'=>$items->total()]]); }
    public function store(StoreMedicationReportRequest $request): JsonResponse
    {
        $patient = Patient::query()->where('user_id', $request->user()->id)->where('status', 'active')->firstOrFail();
        $schedule = DB::table('medication_schedule_times')->join('patient_medication_plans', 'patient_medication_plans.id', '=', 'medication_schedule_times.patient_medication_plan_id')->where('medication_schedule_times.id', $request->integer('schedule_time_id'))->where('patient_medication_plans.patient_id', $patient->id)->select('medication_schedule_times.*', 'patient_medication_plans.id as plan_id')->first();
        abort_unless($schedule, 403, 'Jadwal obat tidak dimiliki pasien.');
        $today = today(config('app.timezone'));
        if (MedicationReport::query()->where('patient_id', $patient->id)->where('schedule_time_id', $schedule->id)->whereDate('report_date', $today)->exists()) abort(422, 'Laporan untuk jadwal ini sudah dikirim.');
        $file=$request->file('photo');$hash=hash_file('sha256',$file->getRealPath());$address=app(GeocodingService::class)->reverse((float)$request->input('latitude'),(float)$request->input('longitude'));$watermark="{$patient->full_name}\n".now()->timezone(config('app.timezone'))->format('d F Y | H:i:s T')."\n{$request->input('latitude')}, {$request->input('longitude')}\n".($address??'Alamat belum tersedia');$path=app(MedicationEvidenceService::class)->store($file,$patient->id,$watermark);
        $report = DB::transaction(function () use ($patient,$schedule,$today,$request,$path,$hash,$address) { $report=MedicationReport::create(['patient_id'=>$patient->id,'patient_medication_plan_id'=>$schedule->plan_id,'schedule_time_id'=>$schedule->id,'report_date'=>$today,'scheduled_time'=>$schedule->time_of_day,'medication_taken'=>$request->boolean('medication_taken'),'not_taken_reason'=>$request->input('not_taken_reason'),'has_side_effect'=>$request->boolean('has_side_effect'),'side_effect_category'=>$request->input('side_effect_category'),'side_effect_description'=>$request->input('side_effect_description'),'photo_path'=>$path,'photo_hash'=>$hash,'latitude'=>$request->input('latitude'),'longitude'=>$request->input('longitude'),'gps_accuracy'=>$request->input('gps_accuracy'),'formatted_address'=>$address,'server_received_at'=>now(),'status'=>$request->boolean('has_side_effect')?'follow_up':'submitted']); if($request->boolean('has_side_effect')) DB::table('side_effect_reports')->insert(['medication_report_id'=>$report->id,'patient_id'=>$patient->id,'category'=>$request->input('side_effect_category'),'description'=>$request->input('side_effect_description'),'severity'=>'low','requires_follow_up'=>true,'follow_up_status'=>'open','created_at'=>now(),'updated_at'=>now()]); return $report; });

        // Notify assigned cadre that patient has reported medication
        if ($patient->cadre_id) {
            $cadre = \App\Models\Cadre::find($patient->cadre_id);
            if ($cadre && $cadre->user_id) {
                $takenLabel = $report->medication_taken ? 'sudah minum obat' : 'tidak minum obat';
                DB::table('user_notifications')->insert([
                    'user_id' => $cadre->user_id,
                    'title' => 'Laporan Minum Obat',
                    'message' => "{$patient->full_name} melaporkan {$takenLabel} pada " . now()->timezone(config('app.timezone'))->format('d M Y H:i') . ".",
                    'type' => 'medication_report',
                    'related_entity_type' => 'medication_report',
                    'related_entity_id' => $report->id,
                    'is_read' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Laporan berhasil dikirim.', 'data' => $report], 201);
    }

    public function addSideEffect(\Illuminate\Http\Request $request, int $reportId): JsonResponse
    {
        $patient = Patient::query()->where('user_id', $request->user()->id)->firstOrFail();
        $report = MedicationReport::query()->where('id', $reportId)->where('patient_id', $patient->id)->firstOrFail();

        $request->validate([
            'category' => 'required|string|in:mual,pusing,ruam,lainnya',
            'description' => 'required|string|max:2000',
        ]);

        DB::transaction(function () use ($report, $request, $patient) {
            $report->update([
                'has_side_effect' => true,
                'side_effect_category' => $request->input('category'),
                'side_effect_description' => $request->input('description'),
                'status' => 'follow_up',
            ]);

            DB::table('side_effect_reports')->updateOrInsert(
                ['medication_report_id' => $report->id],
                [
                    'patient_id' => $patient->id,
                    'category' => $request->input('category'),
                    'description' => $request->input('description'),
                    'severity' => 'low',
                    'requires_follow_up' => true,
                    'follow_up_status' => 'open',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        });

        return response()->json(['success' => true, 'message' => 'Efek samping berhasil dilaporkan.']);
    }
}
