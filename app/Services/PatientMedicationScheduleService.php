<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Support\Facades\DB;

class PatientMedicationScheduleService
{
    /** Creates a default plan only when the patient has no active medication plan yet. */
    public function ensureFor(Patient $patient): void
    {
        if ($patient->status !== 'active' || ! $patient->treatment_start_date) {
            return;
        }

        $activePlan = DB::table('patient_medication_plans')
            ->where('patient_id', $patient->id)
            ->where('is_active', true)
            ->first(['id', 'frequency_per_day']);

        if ($activePlan) {
            if ((int) $activePlan->frequency_per_day === 1) {
                DB::table('medication_schedule_times')
                    ->where('patient_medication_plan_id', $activePlan->id)
                    ->whereNull('time_of_day')
                    ->update(['time_of_day' => '07:00', 'label' => 'Pagi', 'updated_at' => now()]);
            }

            return;
        }

        $frequency = max(1, min(6, (int) $patient->daily_dose_frequency));
        $planId = DB::table('patient_medication_plans')->insertGetId([
            'patient_id' => $patient->id,
            'dose' => 'Sesuai anjuran',
            'frequency_per_day' => $frequency,
            'start_date' => $patient->treatment_start_date,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($this->timesFor($frequency) as $index => $time) {
            DB::table('medication_schedule_times')->insert([
                'patient_medication_plan_id' => $planId,
                'time_of_day' => $time['time_of_day'],
                'label' => $time['label'],
                'sequence' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function timesFor(int $frequency): array
    {
        $presets = [
            1 => [['label' => 'Pagi', 'time_of_day' => '07:00']],
            2 => [['label' => 'Pagi', 'time_of_day' => '07:00'], ['label' => 'Malam', 'time_of_day' => '19:00']],
            3 => [['label' => 'Pagi', 'time_of_day' => '07:00'], ['label' => 'Siang', 'time_of_day' => '13:00'], ['label' => 'Malam', 'time_of_day' => '19:00']],
        ];

        if (isset($presets[$frequency])) {
            return $presets[$frequency];
        }

        return array_map(fn (int $slot): array => ['label' => "Dosis $slot", 'time_of_day' => null], range(1, $frequency));
    }
}
