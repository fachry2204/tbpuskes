<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicationController extends Controller
{
    public function show(Request $request, int $medication): JsonResponse { abort_unless(in_array($request->user()->role,['admin','staff'],true),403); return response()->json(['success'=>true,'data'=>DB::table('medications')->find($medication)]); }
    public function index(Request $request): JsonResponse { abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403); return response()->json(['success' => true, 'data' => DB::table('medications')->where('is_active', true)->orderBy('name')->get()]); }
    public function store(Request $request): JsonResponse { abort_unless($request->user()->role === 'admin', 403); $data = $request->validate(['name' => ['required', 'string', 'max:255', 'unique:medications,name'], 'generic_name' => ['nullable', 'string', 'max:255'], 'strength' => ['nullable', 'string', 'max:100'], 'unit' => ['required', 'string', 'max:50'], 'description' => ['nullable', 'string', 'max:1000']]); $id = DB::table('medications')->insertGetId(array_merge($data, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])); return response()->json(['success' => true, 'message' => 'Obat berhasil ditambahkan.', 'data' => DB::table('medications')->find($id)], 201); }
    public function update(Request $request,int $medication): JsonResponse { abort_unless($request->user()->role==='admin',403); $data=$request->validate(['name'=>['sometimes','string','max:255'],'generic_name'=>['nullable','string','max:255'],'strength'=>['nullable','string','max:100'],'unit'=>['sometimes','string','max:50'],'description'=>['nullable','string','max:1000'],'is_active'=>['sometimes','boolean']]); DB::table('medications')->where('id',$medication)->update(array_merge($data,['updated_at'=>now()])); return response()->json(['success'=>true,'message'=>'Obat diperbarui.']); }
    public function deactivate(Request $request,int $medication): JsonResponse { abort_unless($request->user()->role==='admin',403); DB::table('medications')->where('id',$medication)->update(['is_active'=>false,'updated_at'=>now()]); return response()->json(['success'=>true,'message'=>'Obat dinonaktifkan.']); }
}
