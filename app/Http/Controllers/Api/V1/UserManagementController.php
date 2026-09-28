<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    private function admin(Request $request): void { abort_unless($request->user()->role === 'admin', 403); }
    public function index(Request $request): JsonResponse { $this->admin($request); $items=User::query()->whereIn('role',['admin','staff'])->latest()->paginate(min((int)$request->input('per_page',20),100)); return response()->json(['success'=>true,'data'=>$items->items(),'meta'=>['total'=>$items->total()]]); }
    public function store(Request $request): JsonResponse { $this->admin($request); $data=$request->validate(['name'=>['required','string','max:255'],'username'=>['required','string','max:100','unique:users,username'],'email'=>['nullable','email','max:255','unique:users,email'],'phone'=>['nullable','string','max:20','unique:users,phone'],'password'=>['required','string','min:8'],'role'=>['required','in:admin,staff']]); $data['password']=Hash::make($data['password']);$data['is_active']=true;return response()->json(['success'=>true,'message'=>'Pengguna berhasil dibuat.','data'=>User::create($data)],201); }
    public function update(Request $request, User $user): JsonResponse { $this->admin($request); abort_if(in_array($user->role,['kader','pasien'],true),404); $data=$request->validate(['name'=>['sometimes','string','max:255'],'email'=>['nullable','email','max:255'],'phone'=>['nullable','string','max:20'],'role'=>['sometimes','in:admin,staff'],'password'=>['nullable','string','min:8'],'is_active'=>['sometimes','boolean']]); if (empty($data['password'])) unset($data['password']); else $data['password']=Hash::make($data['password']); $user->update($data);return response()->json(['success'=>true,'message'=>'Pengguna diperbarui.','data'=>$user->fresh()]); }
    public function deactivate(Request $request, User $user): JsonResponse { $this->admin($request); abort_if($user->id===$request->user()->id,422,'Tidak dapat menonaktifkan akun sendiri.');abort_unless(in_array($user->role,['admin','staff'],true),404);$user->update(['is_active'=>false]);$user->tokens()->delete();return response()->json(['success'=>true,'message'=>'Pengguna dinonaktifkan.']); }
    public function destroy(Request $request, User $user): JsonResponse { $this->admin($request);abort_if($user->id===$request->user()->id,422,'Tidak dapat menghapus akun sendiri.');abort_unless(in_array($user->role,['admin','staff'],true),404);$user->delete();return response()->json(['success'=>true,'message'=>'Pengguna dihapus.']); }
}
