<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PatientNextMedicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_next_medication_is_today_until_reported_then_moves_to_tomorrow(): void
    {
        Carbon::setTestNow('2026-09-29 08:00:00');
        $user = User::factory()->create(['role' => 'pasien', 'is_active' => true]);
        $placeId = DB::table('treatment_places')->insertGetId(['name' => 'Puskesmas Test', 'type' => 'puskesmas', 'address' => 'Alamat']);
        $patientId = DB::table('patients')->insertGetId([
            'user_id' => $user->id, 'nik' => '1234567890123456', 'full_name' => 'Pasien Test', 'birth_place' => '',
            'birth_date' => '1990-01-01', 'gender' => 'L', 'phone' => '081234567890', 'rt' => '001', 'rw' => '002',
            'full_address' => 'Alamat', 'treatment_place_id' => $placeId, 'treatment_start_date' => '2026-09-01',
            'daily_dose_frequency' => 1, 'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $medicationId = DB::table('medications')->insertGetId(['name' => 'OAT', 'unit' => 'tablet', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $planId = DB::table('patient_medication_plans')->insertGetId([
            'patient_id' => $patientId, 'medication_id' => $medicationId, 'dose' => '1', 'dose_unit' => 'tablet',
            'frequency_per_day' => 1, 'start_date' => '2026-09-01', 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $scheduleId = DB::table('medication_schedule_times')->insertGetId([
            'patient_medication_plan_id' => $planId, 'time_of_day' => '07:00', 'label' => 'Pagi', 'sequence' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/me/dashboard')->assertOk()
            ->assertJsonPath('data.next_medication.schedule_date', '2026-09-29');

        DB::table('medication_reports')->insert([
            'patient_id' => $patientId, 'patient_medication_plan_id' => $planId, 'schedule_time_id' => $scheduleId,
            'report_date' => '2026-09-29', 'scheduled_time' => '07:00', 'medication_taken' => true,
            'has_side_effect' => false, 'status' => 'submitted', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->getJson('/api/v1/me/dashboard')->assertOk()
            ->assertJsonPath('data.next_medication.schedule_date', '2026-09-30')
            ->assertJsonPath('data.next_medication.schedule_time_id', $scheduleId);
    }
}
