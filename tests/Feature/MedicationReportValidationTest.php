<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MedicationReportValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_medication_report_requires_live_photo_and_gps_fields(): void
    {
        $user = User::create(['name' => 'Patient', 'phone' => '6281234567891', 'password' => Hash::make('password123'), 'role' => 'pasien', 'is_active' => true]);
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/me/medication/reports', ['medication_taken' => true, 'has_side_effect' => false])
            ->assertUnprocessable()->assertJsonValidationErrors(['photo', 'latitude', 'longitude', 'gps_accuracy', 'schedule_time_id']);
    }

    public function test_not_taken_reason_is_required_when_patient_has_not_taken_medication(): void
    {
        $user = User::create(['name' => 'Patient 2', 'phone' => '6281234567892', 'password' => Hash::make('password123'), 'role' => 'pasien', 'is_active' => true]);
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/me/medication/reports', ['medication_taken' => false, 'has_side_effect' => false])
            ->assertUnprocessable()->assertJsonValidationErrors('not_taken_reason');
    }
}
