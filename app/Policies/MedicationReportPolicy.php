<?php
namespace App\Policies;
use App\Models\MedicationReport;use App\Models\User;
class MedicationReportPolicy { public function view(User $user,MedicationReport $report):bool{return in_array($user->role,['admin','staff'],true)||($user->role==='pasien'&&$report->patient?->user_id===$user->id)||($user->role==='kader'&&$report->patient?->cadre?->user_id===$user->id);}public function verify(User $user,MedicationReport $report):bool{return in_array($user->role,['admin','staff'],true);} }
