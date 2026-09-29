<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PatientBirthPlaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_be_created_and_updated_without_birth_place(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $placeId = DB::table('treatment_places')->insertGetId([
            'name' => 'Puskesmas Test', 'type' => 'puskesmas', 'address' => 'Alamat test',
        ]);
        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/patients', [
            'full_name' => 'Pasien Test', 'nik' => '1234567890123456',
            'birth_date' => '1990-01-01', 'gender' => 'L', 'phone' => '081234567890',
            'password' => 'password123', 'rt' => '001', 'rw' => '002',
            'full_address' => 'Alamat test', 'treatment_place_id' => $placeId,
            'treatment_start_date' => '2026-01-01', 'tb_diagnosis' => 'TB Paru',
            'diagnosis_type' => 'Klinis', 'daily_dose_frequency' => 1,
        ]);
        $response->assertCreated()->assertJsonMissingPath('data.birth_place');
        $id = $response->json('data.id');
        $this->assertDatabaseHas('patients', ['id' => $id, 'birth_place' => '']);

        DB::table('patients')->where('id', $id)->update(['birth_place' => 'Data lama']);
        $this->putJson("/api/v1/patients/{$id}", [
            'full_name' => 'Pasien Diperbarui', 'birth_place' => 'Tidak dipakai',
        ])->assertOk()->assertJsonPath('data.full_name', 'Pasien Diperbarui')
            ->assertJsonMissingPath('data.birth_place');
        $this->assertDatabaseHas('patients', ['id' => $id, 'birth_place' => 'Data lama']);
        $this->getJson("/api/v1/patients/{$id}")->assertOk()->assertJsonMissingPath('data.birth_place');
    }
}
