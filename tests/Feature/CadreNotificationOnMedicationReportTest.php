<?php

namespace Tests\Feature;

use App\Models\Cadre;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CadreNotificationOnMedicationReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    private function scaffoldPatientWithCadre(): array
    {
        $cadreUser = User::factory()->create(['role' => 'kader', 'is_active' => true]);
        $cadre = Cadre::create([
            'user_id'   => $cadreUser->id,
            'full_name' => 'Kader Uji',
            'nik'       => fake()->unique()->numerify('################'),
            'birth_place' => '',
            'birth_date' => '1985-01-01',
            'gender'    => 'P',
            'phone'     => fake()->unique()->numerify('628#########'),
            'rt' => '001', 'rw' => '001',
            'full_address' => 'Alamat Kader',
            'is_active' => true,
        ]);

        $patientUser = User::factory()->create(['role' => 'pasien', 'is_active' => true]);
        $placeId = DB::table('treatment_places')->insertGetId([
            'name' => 'Puskesmas Uji', 'type' => 'Puskesmas', 'address' => 'Alamat Puskesmas',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $patient = Patient::create([
            'user_id'            => $patientUser->id,
            'full_name'          => 'Pasien Uji',
            'nik'                => fake()->unique()->numerify('################'),
            'birth_date'         => '1990-06-01',
            'gender'             => 'L',
            'phone'              => fake()->unique()->numerify('628#########'),
            'rt' => '001', 'rw' => '001',
            'full_address'       => 'Alamat Pasien',
            'treatment_place_id' => $placeId,
            'cadre_id'           => $cadre->id,
            'treatment_start_date' => '2026-01-01',
            'status'             => 'active',
        ]);

        // Create medication plan + schedule
        $medicationId = DB::table('medications')->insertGetId([
            'name' => 'Rifampisin', 'unit' => 'tablet',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $planId = DB::table('patient_medication_plans')->insertGetId([
            'patient_id' => $patient->id,
            'medication_id' => $medicationId,
            'dose' => '150',
            'dose_unit' => 'mg',
            'frequency_per_day' => 1,
            'start_date' => now()->toDateString(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $scheduleId = DB::table('medication_schedule_times')->insertGetId([
            'patient_medication_plan_id' => $planId,
            'time_of_day' => '08:00',
            'sequence' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$cadreUser, $cadre, $patientUser, $patient, $scheduleId];
    }

    public function test_cadre_receives_notification_when_patient_reports_taken(): void
    {
        Storage::fake('public');
        [$cadreUser, $cadre, $patientUser, $patient, $scheduleId] = $this->scaffoldPatientWithCadre();

        $this->actingAs($patientUser, 'sanctum')
            ->postJson('/api/v1/me/medication/reports', [
                'schedule_time_id' => $scheduleId,
                'medication_taken' => true,
                'has_side_effect'  => false,
                'latitude'         => '-6.200000',
                'longitude'        => '106.816666',
                'gps_accuracy'     => '10',
                'photo'            => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertCreated()
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $cadreUser->id,
            'type'    => 'medication_report',
            'is_read' => false,
        ]);

        $notif = DB::table('user_notifications')
            ->where('user_id', $cadreUser->id)
            ->where('type', 'medication_report')
            ->first();

        $this->assertStringContainsString('Pasien Uji', $notif->message);
        $this->assertStringContainsString('sudah minum obat', $notif->message);
    }

    public function test_cadre_receives_notification_when_patient_reports_not_taken(): void
    {
        Storage::fake('public');
        [$cadreUser, $cadre, $patientUser, $patient, $scheduleId] = $this->scaffoldPatientWithCadre();

        $this->actingAs($patientUser, 'sanctum')
            ->postJson('/api/v1/me/medication/reports', [
                'schedule_time_id' => $scheduleId,
                'medication_taken' => false,
                'not_taken_reason' => 'Lupa',
                'has_side_effect'  => false,
                'latitude'         => '-6.200000',
                'longitude'        => '106.816666',
                'gps_accuracy'     => '10',
                'photo'            => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertCreated();

        $notif = DB::table('user_notifications')
            ->where('user_id', $cadreUser->id)
            ->where('type', 'medication_report')
            ->first();

        $this->assertNotNull($notif);
        $this->assertStringContainsString('tidak minum obat', $notif->message);
    }

    public function test_no_notification_sent_when_patient_has_no_cadre(): void
    {
        Storage::fake('public');

        $patientUser = User::factory()->create(['role' => 'pasien', 'is_active' => true]);
        $placeId = DB::table('treatment_places')->insertGetId([
            'name' => 'Puskesmas B', 'type' => 'Puskesmas', 'address' => 'Alamat B',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $patient = Patient::create([
            'user_id'            => $patientUser->id,
            'full_name'          => 'Tanpa Kader',
            'nik'                => fake()->unique()->numerify('################'),
            'birth_date'         => '1992-03-15',
            'gender'             => 'P',
            'phone'              => fake()->unique()->numerify('628#########'),
            'rt' => '001', 'rw' => '001',
            'full_address'       => 'Alamat Pasien B',
            'treatment_place_id' => $placeId,
            'cadre_id'           => null,
            'treatment_start_date' => '2026-01-01',
            'status'             => 'active',
        ]);
        $medicationId = DB::table('medications')->insertGetId([
            'name' => 'Rifampisin', 'unit' => 'tablet',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $planId = DB::table('patient_medication_plans')->insertGetId([
            'patient_id' => $patient->id,
            'medication_id' => $medicationId,
            'dose' => '150',
            'dose_unit' => 'mg',
            'frequency_per_day' => 1,
            'start_date' => now()->toDateString(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $scheduleId = DB::table('medication_schedule_times')->insertGetId([
            'patient_medication_plan_id' => $planId,
            'time_of_day' => '08:00',
            'sequence' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($patientUser, 'sanctum')
            ->postJson('/api/v1/me/medication/reports', [
                'schedule_time_id' => $scheduleId,
                'medication_taken' => true,
                'has_side_effect'  => false,
                'latitude'         => '-6.200000',
                'longitude'        => '106.816666',
                'gps_accuracy'     => '10',
                'photo'            => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertCreated();

        $this->assertEquals(0, DB::table('user_notifications')->where('type', 'medication_report')->count());
    }
}
