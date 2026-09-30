<?php

namespace Tests\Feature;

use App\Models\Cadre;
use App\Models\MedicationReport;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CadreDailyMedicationReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_cadre_can_view_each_assigned_patient_status_for_selected_date(): void
    {
        $cadreUser = User::factory()->create(['role' => 'kader']);
        $cadre = Cadre::create([
            'user_id' => $cadreUser->id,
            'nik' => '3174000000000011',
            'full_name' => 'Kader Harian',
            'birth_place' => 'Jakarta',
            'birth_date' => '1980-01-01',
            'gender' => 'P',
            'phone' => '081200000011',
            'rt' => '001',
            'rw' => '002',
            'full_address' => 'Petukangan Utara',
            'is_active' => true,
        ]);
        $otherCadreUser = User::factory()->create(['role' => 'kader']);
        $otherCadre = Cadre::create([
            'user_id' => $otherCadreUser->id,
            'nik' => '3174000000000012',
            'full_name' => 'Kader Lain',
            'birth_place' => 'Jakarta',
            'birth_date' => '1981-01-01',
            'gender' => 'P',
            'phone' => '081200000012',
            'rt' => '003',
            'rw' => '004',
            'full_address' => 'Wilayah Lain',
            'is_active' => true,
        ]);
        $placeId = DB::table('treatment_places')->insertGetId([
            'name' => 'Puskesmas Test',
            'type' => 'Puskesmas',
            'address' => 'Alamat Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $takenPatient = $this->createPatient($cadre->id, $placeId, 'Pasien Sudah', '3174000000001011', '081300000011');
        $notTakenPatient = $this->createPatient($cadre->id, $placeId, 'Pasien Belum', '3174000000001012', '081300000012');
        $unreportedPatient = $this->createPatient($cadre->id, $placeId, 'Pasien Tanpa Laporan', '3174000000001013', '081300000013');
        $otherPatient = $this->createPatient($otherCadre->id, $placeId, 'Pasien Kader Lain', '3174000000001014', '081300000014');

        MedicationReport::create([
            'patient_id' => $takenPatient->id,
            'report_date' => '2026-09-29',
            'scheduled_time' => '08:00:00',
            'medication_taken' => true,
            'has_side_effect' => false,
            'status' => 'verified',
            'server_received_at' => '2026-09-29 08:12:00',
        ]);
        MedicationReport::create([
            'patient_id' => $notTakenPatient->id,
            'report_date' => '2026-09-29',
            'scheduled_time' => '08:00:00',
            'medication_taken' => false,
            'not_taken_reason' => 'Obat habis',
            'has_side_effect' => false,
            'status' => 'submitted',
            'server_received_at' => '2026-09-29 08:20:00',
        ]);
        MedicationReport::create([
            'patient_id' => $otherPatient->id,
            'report_date' => '2026-09-29',
            'medication_taken' => true,
            'has_side_effect' => false,
            'status' => 'verified',
            'server_received_at' => '2026-09-29 08:10:00',
        ]);

        $response = $this->actingAs($cadreUser, 'sanctum')
            ->getJson('/api/v1/kader/medication-reports/daily?date=2026-09-29')
            ->assertOk()
            ->assertJsonPath('data.date', '2026-09-29')
            ->assertJsonPath('data.summary.total', 3)
            ->assertJsonPath('data.summary.taken', 1)
            ->assertJsonPath('data.summary.not_taken', 1)
            ->assertJsonPath('data.summary.unreported', 1)
            ->assertJsonCount(3, 'data.patients');

        $patients = collect($response->json('data.patients'))->keyBy('full_name');
        $this->assertSame('taken', $patients['Pasien Sudah']['daily_status']);
        $this->assertSame('08:12', $patients['Pasien Sudah']['reported_time']);
        $this->assertSame('not_taken', $patients['Pasien Belum']['daily_status']);
        $this->assertSame('Obat habis', $patients['Pasien Belum']['not_taken_reason']);
        $this->assertSame('unreported', $patients['Pasien Tanpa Laporan']['daily_status']);
        $this->assertFalse($patients->has('Pasien Kader Lain'));
    }

    private function createPatient(int $cadreId, int $placeId, string $name, string $nik, string $phone): Patient
    {
        $user = User::factory()->create(['role' => 'pasien']);

        return Patient::create([
            'user_id' => $user->id,
            'cadre_id' => $cadreId,
            'full_name' => $name,
            'nik' => $nik,
            'birth_date' => '1990-01-01',
            'gender' => 'L',
            'phone' => $phone,
            'rt' => '001',
            'rw' => '001',
            'full_address' => 'Alamat Test',
            'treatment_place_id' => $placeId,
            'treatment_start_date' => '2026-01-01',
            'status' => 'active',
        ]);
    }
}
