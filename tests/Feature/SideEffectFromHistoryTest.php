<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Patient;
use App\Models\MedicationReport;
use Illuminate\Support\Facades\DB;


class SideEffectFromHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    private function createPatientWithReport(bool $hasSideEffect = false): array
    {
        $user = User::factory()->create(['role' => 'pasien']);
        $placeId = DB::table('treatment_places')->insertGetId([
            'name' => 'Puskesmas Test', 'type' => 'Puskesmas', 'address' => 'Alamat Test',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $patient = Patient::create([
            'user_id' => $user->id, 'full_name' => 'Pasien Test', 'nik' => fake()->unique()->numerify('################'),
            'birth_date' => '1990-01-01', 'gender' => 'L', 'phone' => fake()->unique()->numerify('628#########'),
            'rt' => '001', 'rw' => '001', 'full_address' => 'Alamat Test', 'treatment_place_id' => $placeId,
            'treatment_start_date' => '2026-01-01', 'status' => 'active',
        ]);
        $report = MedicationReport::create([
            'patient_id' => $patient->id,
            'report_date' => now()->toDateString(),
            'medication_taken' => true,
            'has_side_effect' => $hasSideEffect,
            'side_effect_category' => $hasSideEffect ? 'mual' : null,
            'side_effect_description' => $hasSideEffect ? 'Mual setelah minum obat' : null,
            'status' => $hasSideEffect ? 'follow_up' : 'submitted',
            'server_received_at' => now(),
        ]);
        return [$user, $patient, $report];
    }

    public function test_patient_can_add_side_effect_to_existing_report(): void
    {
        [$user, $patient, $report] = $this->createPatientWithReport(false);

        $response = $this->actingAs($user)->postJson("/api/v1/me/medication/reports/{$report->id}/side-effect", [
            'category' => 'pusing',
            'description' => 'Pusing setelah minum obat pagi',
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $report->refresh();
        $this->assertTrue($report->has_side_effect);
        $this->assertEquals('follow_up', $report->status);
        $this->assertEquals('pusing', $report->side_effect_category);

        $this->assertDatabaseHas('side_effect_reports', [
            'medication_report_id' => $report->id,
            'patient_id' => $patient->id,
            'category' => 'pusing',
        ]);
    }

    public function test_patient_can_update_side_effect_that_already_exists(): void
    {
        [$user, $patient, $report] = $this->createPatientWithReport(true);
        DB::table('side_effect_reports')->insert([
            'medication_report_id' => $report->id,
            'patient_id' => $patient->id,
            'category' => 'mual',
            'description' => 'Mual setelah minum obat',
            'severity' => 'low',
            'requires_follow_up' => true,
            'follow_up_status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson("/api/v1/me/medication/reports/{$report->id}/side-effect", [
            'category' => 'ruam',
            'description' => 'Gatal di tangan kanan',
        ]);

        $response->assertOk();

        $report->refresh();
        $this->assertEquals('ruam', $report->side_effect_category);
        $this->assertEquals('Gatal di tangan kanan', $report->side_effect_description);
        $this->assertDatabaseCount('side_effect_reports', 1);
        $this->assertDatabaseHas('side_effect_reports', [
            'medication_report_id' => $report->id,
            'category' => 'ruam',
            'description' => 'Gatal di tangan kanan',
        ]);
    }

    public function test_validation_rejects_invalid_category(): void
    {
        [$user, , $report] = $this->createPatientWithReport(false);

        $response = $this->actingAs($user)->postJson("/api/v1/me/medication/reports/{$report->id}/side-effect", [
            'category' => 'tidak_valid',
            'description' => 'Sesuatu',
        ]);

        $response->assertUnprocessable();
    }

    public function test_patient_cannot_add_side_effect_to_other_patient_report(): void
    {
        [, , $report] = $this->createPatientWithReport(false);

        $otherUser = User::factory()->create(['role' => 'pasien']);
        $placeId = DB::table('treatment_places')->insertGetId([
            'name' => 'Puskesmas Lain', 'type' => 'Puskesmas', 'address' => 'Alamat Lain',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        Patient::create([
            'user_id' => $otherUser->id, 'full_name' => 'Pasien Lain', 'nik' => fake()->unique()->numerify('################'),
            'birth_date' => '1991-01-01', 'gender' => 'P', 'phone' => fake()->unique()->numerify('628#########'),
            'rt' => '002', 'rw' => '002', 'full_address' => 'Alamat Lain', 'treatment_place_id' => $placeId,
            'treatment_start_date' => '2026-01-01', 'status' => 'active',
        ]);

        $response = $this->actingAs($otherUser)->postJson("/api/v1/me/medication/reports/{$report->id}/side-effect", [
            'category' => 'pusing',
            'description' => 'Pusing',
        ]);

        $response->assertNotFound();
    }
}
