<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ControlScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    private function schedule(): int
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin, 'sanctum');
        $user = User::factory()->create(['role' => 'pasien']);
        $place = DB::table('treatment_places')->insertGetId(['name' => 'Puskesmas Test', 'type' => 'Puskesmas', 'address' => 'Test']);
        $patient = Patient::create(['user_id' => $user->id, 'full_name' => 'Test', 'nik' => '1234567890123456', 'birth_date' => '1990-01-01', 'gender' => 'L', 'phone' => '6281234567890', 'rt' => '001', 'rw' => '001', 'full_address' => 'Test', 'treatment_place_id' => $place, 'treatment_start_date' => '2026-01-01']);
        return DB::table('control_schedules')->insertGetId(['patient_id' => $patient->id, 'treatment_place_id' => $place, 'control_date' => '2026-10-08', 'start_time' => '10:00', 'purpose' => 'Kontrol awal', 'notes' => 'Catatan lama', 'status' => 'scheduled', 'created_by' => $admin->id]);
    }

    public function test_staff_can_edit_schedule_details_without_changing_status(): void
    {
        $id = $this->schedule();
        $place = DB::table('control_schedules')->where('id', $id)->value('treatment_place_id');
        $this->putJson("/api/v1/control-schedules/{$id}", ['treatment_place_id' => $place, 'control_date' => '2026-10-09', 'start_time' => '11:30', 'purpose' => 'Kontrol lanjutan', 'notes' => 'Catatan baru'])
            ->assertOk()->assertJsonPath('data.purpose', 'Kontrol lanjutan');
        $this->assertDatabaseHas('control_schedules', ['id' => $id, 'control_date' => '2026-10-09', 'start_time' => '11:30', 'notes' => 'Catatan baru', 'status' => 'scheduled']);
        $this->getJson('/api/v1/control-schedules')->assertOk()->assertJsonPath('data.0.treatment_place_id', $place)->assertJsonPath('data.0.notes', 'Catatan baru');
    }

    public function test_admin_can_delete_schedule_and_missing_schedule_returns_404(): void
    {
        $id = $this->schedule();
        $this->deleteJson("/api/v1/control-schedules/{$id}")->assertOk();
        $this->assertDatabaseMissing('control_schedules', ['id' => $id]);
        $this->deleteJson("/api/v1/control-schedules/{$id}")->assertNotFound();
    }

    public function test_patient_cannot_edit_or_delete_schedule(): void
    {
        $id = $this->schedule();
        $this->actingAs(User::factory()->create(['role' => 'pasien']), 'sanctum');
        $this->putJson("/api/v1/control-schedules/{$id}", ['purpose' => 'Tidak boleh'])->assertForbidden();
        $this->deleteJson("/api/v1/control-schedules/{$id}")->assertForbidden();
        $this->assertDatabaseHas('control_schedules', ['id' => $id, 'purpose' => 'Kontrol awal']);
    }
}
