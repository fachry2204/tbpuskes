<?php

namespace Tests\Feature;

use App\Models\MedicationReport;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MedicationMonitoringManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createReport(): array
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $admin = User::factory()->create(['role' => 'admin']);
        $placeId = DB::table('treatment_places')->insertGetId([
            'name' => 'Puskesmas Monitoring', 'type' => 'Puskesmas', 'address' => 'Alamat',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $patient = Patient::create([
            'user_id' => User::factory()->create(['role' => 'pasien'])->id,
            'full_name' => 'Pasien Keluhan', 'nik' => '3212345678901234',
            'birth_place' => '', 'birth_date' => '1991-01-01', 'gender' => 'P',
            'phone' => '081234567890', 'rt' => '001', 'rw' => '001',
            'full_address' => 'Alamat Pasien', 'treatment_place_id' => $placeId,
            'treatment_start_date' => '2026-01-01', 'status' => 'active',
        ]);
        $report = MedicationReport::create([
            'patient_id' => $patient->id, 'report_date' => now()->toDateString(),
            'medication_taken' => true, 'has_side_effect' => true,
            'side_effect_category' => 'mual', 'side_effect_description' => 'Mual dan pusing setelah minum obat.',
            'status' => 'follow_up', 'server_received_at' => now(),
        ]);

        return [$admin, $report];
    }

    public function test_monitoring_returns_side_effect_description(): void
    {
        [$admin, $report] = $this->createReport();

        $this->actingAs($admin, 'sanctum')->getJson('/api/v1/medication-monitoring')
            ->assertOk()
            ->assertJsonPath('data.0.id', $report->id)
            ->assertJsonPath('data.0.side_effect_description', 'Mual dan pusing setelah minum obat.');
    }

    public function test_admin_can_edit_monitoring_report(): void
    {
        [$admin, $report] = $this->createReport();

        $this->actingAs($admin, 'sanctum')->putJson("/api/v1/medication-monitoring/{$report->id}", [
            'medication_taken' => false,
            'not_taken_reason' => 'Obat habis',
            'has_side_effect' => true,
            'side_effect_category' => 'pusing',
            'side_effect_description' => 'Pusing setelah minum obat.',
        ])->assertOk()->assertJsonPath('data.medication_taken', false);

        $this->assertDatabaseHas('medication_reports', [
            'id' => $report->id, 'medication_taken' => false, 'not_taken_reason' => 'Obat habis',
            'side_effect_category' => 'pusing', 'side_effect_description' => 'Pusing setelah minum obat.',
        ]);
    }

    public function test_admin_can_delete_monitoring_report(): void
    {
        [$admin, $report] = $this->createReport();

        $this->actingAs($admin, 'sanctum')->deleteJson("/api/v1/medication-monitoring/{$report->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('medication_reports', ['id' => $report->id]);
    }
}
