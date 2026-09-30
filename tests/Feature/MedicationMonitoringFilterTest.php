<?php

namespace Tests\Feature;

use App\Models\Cadre;
use App\Models\MedicationReport;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MedicationMonitoringFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_monitoring_index_filters_by_date_default_today(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $placeId = DB::table('treatment_places')->insertGetId([
            'name' => 'Puskesmas Filter', 'type' => 'Puskesmas', 'address' => 'Alamat Filter',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $patient = Patient::create([
            'user_id' => User::factory()->create(['role' => 'pasien'])->id,
            'full_name' => 'Pasien Filter', 'nik' => '1234567890123456',
            'birth_place' => '', 'birth_date' => '1990-01-01', 'gender' => 'L',
            'phone' => '081234567890', 'rt' => '001', 'rw' => '001',
            'full_address' => 'Alamat Pasien', 'treatment_place_id' => $placeId,
            'treatment_start_date' => '2026-01-01', 'status' => 'active',
        ]);

        $todayStr = now()->toDateString();
        $yesterdayStr = now()->subDay()->toDateString();

        // Today report
        MedicationReport::create([
            'patient_id' => $patient->id,
            'report_date' => $todayStr,
            'medication_taken' => true,
            'status' => 'submitted',
            'server_received_at' => now(),
        ]);

        // Yesterday report
        MedicationReport::create([
            'patient_id' => $patient->id,
            'report_date' => $yesterdayStr,
            'medication_taken' => true,
            'status' => 'submitted',
            'server_received_at' => now()->subDay(),
        ]);

        // Default today
        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/medication-monitoring');
        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($todayStr, $response->json('data.0.report_date'));

        // Filter yesterday
        $responseYesterday = $this->actingAs($admin, 'sanctum')->getJson("/api/v1/medication-monitoring?date={$yesterdayStr}");
        $responseYesterday->assertOk();
        $this->assertCount(1, $responseYesterday->json('data'));
        $this->assertEquals($yesterdayStr, $responseYesterday->json('data.0.report_date'));
    }

    public function test_unreported_patients_filters_by_date_default_today(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $placeId = DB::table('treatment_places')->insertGetId([
            'name' => 'Puskesmas Filter 2', 'type' => 'Puskesmas', 'address' => 'Alamat Filter 2',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $patient = Patient::create([
            'user_id' => User::factory()->create(['role' => 'pasien'])->id,
            'full_name' => 'Pasien Belum Lapor Filter', 'nik' => '9876543210123456',
            'birth_place' => '', 'birth_date' => '1992-01-01', 'gender' => 'P',
            'phone' => '089876543210', 'rt' => '001', 'rw' => '001',
            'full_address' => 'Alamat Pasien 2', 'treatment_place_id' => $placeId,
            'treatment_start_date' => '2026-01-01', 'status' => 'active',
        ]);

        $todayStr = now()->toDateString();
        $yesterdayStr = now()->subDay()->toDateString();

        // Patient reported yesterday, but not today
        MedicationReport::create([
            'patient_id' => $patient->id,
            'report_date' => $yesterdayStr,
            'medication_taken' => true,
            'status' => 'submitted',
            'server_received_at' => now()->subDay(),
        ]);

        // Default today -> patient is in unreported list
        $resToday = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/medication-monitoring/unreported');
        $resToday->assertOk();
        $this->assertTrue(collect($resToday->json('data'))->contains('id', $patient->id));

        // Filter yesterday -> patient is NOT in unreported list
        $resYesterday = $this->actingAs($admin, 'sanctum')->getJson("/api/v1/medication-monitoring/unreported?date={$yesterdayStr}");
        $resYesterday->assertOk();
        $this->assertFalse(collect($resYesterday->json('data'))->contains('id', $patient->id));
    }
}
