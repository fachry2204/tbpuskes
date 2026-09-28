<?php
namespace App\Policies;
use App\Models\Patient;use App\Models\User;
class PatientPolicy { public function view(User $user,Patient $patient):bool{return in_array($user->role,['admin','staff'],true)||($user->role==='pasien'&&$patient->user_id===$user->id)||($user->role==='kader'&&$patient->cadre?->user_id===$user->id);}public function update(User $user,Patient $patient):bool{return in_array($user->role,['admin','staff'],true);}public function delete(User $user,Patient $patient):bool{return $user->role==='admin';} }
