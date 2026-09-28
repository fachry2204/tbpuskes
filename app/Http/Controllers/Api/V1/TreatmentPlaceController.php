<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TreatmentPlaceController extends Controller
{
    public function show(Request $request,int $treatmentPlace): JsonResponse { abort_unless(in_array($request->user()->role,['admin','staff'],true),403); return response()->json(['success'=>true,'data'=>DB::table('treatment_places')->find($treatmentPlace)]); }
    public function index(Request $request): JsonResponse { abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403); return response()->json(['success' => true, 'data' => DB::table('treatment_places')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'type', 'address', 'phone'])]); }
    public function store(Request $request): JsonResponse { abort_unless($request->user()->role === 'admin', 403); $data = $request->validate(['name' => ['required', 'string', 'max:255', 'unique:treatment_places,name'], 'type' => ['required', 'in:Puskesmas,RSUD,RS UMUM,Lainnya'], 'address' => ['required', 'string', 'max:1000'], 'phone' => ['nullable', 'string', 'max:20']]); $id = DB::table('treatment_places')->insertGetId(array_merge($data, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])); return response()->json(['success' => true, 'message' => 'Tempat pengobatan berhasil disimpan.', 'data' => DB::table('treatment_places')->find($id)], 201); }
    public function update(Request $request,int $treatmentPlace): JsonResponse { abort_unless($request->user()->role==='admin',403); $data=$request->validate(['name'=>['sometimes','string','max:255'],'type'=>['sometimes','in:Puskesmas,RSUD,RS UMUM,Lainnya'],'address'=>['sometimes','string','max:1000'],'phone'=>['nullable','string','max:20'],'is_active'=>['sometimes','boolean']]); DB::table('treatment_places')->where('id',$treatmentPlace)->update(array_merge($data,['updated_at'=>now()])); return response()->json(['success'=>true,'message'=>'Tempat pengobatan diperbarui.']); }
    public function deactivate(Request $request,int $treatmentPlace): JsonResponse { abort_unless($request->user()->role==='admin',403); DB::table('treatment_places')->where('id',$treatmentPlace)->update(['is_active'=>false,'updated_at'=>now()]); return response()->json(['success'=>true,'message'=>'Tempat pengobatan dinonaktifkan.']); }
}
