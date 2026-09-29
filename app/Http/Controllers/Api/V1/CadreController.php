<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCadreRequest;
use App\Models\Cadre;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CadreController extends Controller
{
    public function show(Request $request, Cadre $cadre): JsonResponse { abort_unless(in_array($request->user()->role,['admin','staff'],true),403); return response()->json(['success'=>true,'data'=>$cadre->load('user')]); }
    public function index(Request $request): JsonResponse { abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403); $items = Cadre::query()->with(['patients:id,full_name,cadre_id'])->withCount('patients')->latest()->paginate(min((int) $request->input('per_page', 20), 100)); return response()->json(['success' => true, 'data' => $items->items(), 'meta' => ['current_page' => $items->currentPage(), 'last_page' => $items->lastPage(), 'total' => $items->total()]]); }
    public function store(StoreCadreRequest $request): JsonResponse { $data = $request->validated(); $photoPath = $request->file('photo')?->store('cadres', 'public'); unset($data['photo']); $phone = $this->phone($data['phone']); abort_unless($phone, 422, 'Nomor HP Indonesia tidak valid.'); $cadre = DB::transaction(function () use ($data, $phone, $photoPath): Cadre { $user = User::create(['name' => $data['full_name'], 'phone' => $phone, 'password' => Hash::make($data['password']), 'role' => 'kader', 'is_active' => true]); unset($data['password']); $data['phone'] = $phone; $data['user_id'] = $user->id; $data['photo_path'] = $photoPath; return Cadre::create($data); }); return response()->json(['success' => true, 'message' => 'Kader berhasil dibuat.', 'data' => $cadre], 201); }
    public function update(Request $request, Cadre $cadre): JsonResponse
    {
        abort_unless(in_array($request->user()->role,['admin','staff'],true),403);
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', (string) $request->input('birth_date'))) { [$day,$month,$year]=explode('/',(string) $request->input('birth_date')); $request->merge(['birth_date'=>"$year-$month-$day"]); }
        $data=$request->validate([
            'full_name'=>['sometimes','string','max:255'], 'nik'=>['sometimes','digits:16',Rule::unique('cadres','nik')->ignore($cadre->id)],
            'birth_place'=>['sometimes','string','max:100'], 'birth_date'=>['sometimes','date'], 'gender'=>['sometimes',Rule::in(['L','P'])],
            'phone'=>['sometimes','string','max:20'], 'rt'=>['sometimes','string','max:3'], 'rw'=>['sometimes','string','max:3'],
            'full_address'=>['sometimes','string','max:1000'], 'is_active'=>['sometimes','boolean'],
            'working_area'=>['nullable','string','max:255'], 'password'=>['nullable','string','min:8'],
            'photo'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);
        if (isset($data['phone'])) { $phone=$this->phone($data['phone']); abort_unless($phone,422,'Nomor HP Indonesia tidak valid.'); $data['phone']=$phone; }
        if (isset($data['phone'])) {
            Validator::make($data, [
                'phone' => [Rule::unique('users', 'phone')->ignore($cadre->user_id)],
            ])->validate();
        }
        $account = array_filter(['name'=>$data['full_name']??null,'phone'=>$data['phone']??null],fn($value)=>$value!==null);
        if (!empty($data['password'])) $account['password'] = Hash::make($data['password']);
        unset($data['password'], $data['photo']);
        if ($request->hasFile('photo')) $data['photo_path'] = $request->file('photo')->store('cadres', 'public');
        DB::transaction(function() use($cadre,$data,$account): void { $cadre->update($data); $cadre->user?->update($account); });
        return response()->json(['success'=>true,'message'=>'Kader diperbarui.','data'=>$cadre->fresh()]);
    }
    public function deactivate(Request $request, Cadre $cadre): JsonResponse { abort_unless(in_array($request->user()->role,['admin','staff'],true),403); $cadre->update(['is_active'=>false]); return response()->json(['success'=>true,'message'=>'Kader dinonaktifkan.']); }
    public function destroy(Request $request, Cadre $cadre): JsonResponse { abort_unless($request->user()->role==='admin',403); $cadre->delete(); return response()->json(['success'=>true,'message'=>'Kader dihapus.']); }
    private function phone(string $phone): ?string { $number = preg_replace('/[^0-9+]/', '', $phone) ?? ''; if (str_starts_with($number, '+62')) $number = substr($number, 1); if (str_starts_with($number, '0')) $number = '62'.substr($number, 1); return preg_match('/^628[0-9]{7,12}$/', $number) ? $number : null; }
}
