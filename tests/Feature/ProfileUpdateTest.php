<?php

namespace Tests\Feature;

use App\Models\Cadre;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]), 'sanctum');
    }

    private function profile(string $type): Patient|Cadre
    {
        $data = [
            'full_name' => 'Original Name', 'nik' => '1234567890123456',
            'birth_date' => '1990-01-01', 'gender' => 'L', 'phone' => '081234567890',
            'password' => 'old-password', 'rt' => '001', 'rw' => '002',
            'full_address' => 'Original address',
        ];
        if ($type === 'patients') {
            $data += [
                'treatment_place_id' => DB::table('treatment_places')->insertGetId([
                    'name' => 'Test clinic', 'type' => 'puskesmas', 'address' => 'Test address',
                ]),
                'treatment_start_date' => '2026-01-01', 'tb_diagnosis' => 'TB Paru',
                'diagnosis_type' => 'Klinis', 'daily_dose_frequency' => 1,
            ];
        } else {
            $data += ['birth_place' => 'Original city', 'working_area' => 'Original area'];
        }
        $id = $this->postJson("/api/v1/{$type}", $data)->assertCreated()->json('data.id');
        $profile = $type === 'patients' ? Patient::findOrFail($id) : Cadre::findOrFail($id);
        Storage::disk('public')->put("{$type}/old.jpg", 'old photo');
        $profile->update(['photo_path' => "{$type}/old.jpg"]);
        return $profile;
    }

    public static function profileTypes(): array
    {
        return [['patients'], ['cadres']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('profileTypes')]
    public function test_duplicate_normalized_phone_returns_validation_error_without_changes(string $type): void
    {
        $profile = $this->profile($type);
        User::factory()->create(['phone' => '6281234567891']);
        foreach (['081234567891', '+62 812-3456-7891', '6281234567891'] as $phone) {
            $this->post("/api/v1/{$type}/{$profile->id}", [
                '_method' => 'PUT', 'phone' => $phone, 'full_name' => 'Must not change',
                'password' => 'new-password', 'photo' => UploadedFile::fake()->image('new.png'),
            ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('phone');
            $profile->refresh();
            $this->assertSame('Original Name', $profile->full_name);
            $this->assertSame('6281234567890', $profile->phone);
            $this->assertSame('Original Name', $profile->user->name);
            $this->assertTrue(Hash::check('old-password', $profile->user->password));
            $this->assertSame(["{$type}/old.jpg"], Storage::disk('public')->allFiles($type));
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('profileTypes')]
    public function test_optional_password_and_photo_preserve_existing_values_and_own_phone_is_allowed(string $type): void
    {
        $profile = $this->profile($type);
        $password = $profile->user->password;
        foreach ([[], ['password' => ''], ['password' => null, 'photo' => null]] as $optional) {
            $this->post("/api/v1/{$type}/{$profile->id}", $optional + [
                '_method' => 'PUT', 'phone' => '+62 812-3456-7890',
                'birth_date' => '1991-11-20', 'nik' => $profile->nik,
            ], ['Accept' => 'application/json'])->assertOk();
            $profile->refresh();
            $this->assertSame($password, $profile->user->password);
            $this->assertSame("{$type}/old.jpg", $profile->photo_path);
            $this->assertSame('6281234567890', $profile->phone);
            $this->assertSame('1991-11-20', substr((string) $profile->birth_date, 0, 10));
            if ($profile instanceof Patient) {
                $this->assertSame('2026-01-01', $profile->treatment_start_date->toDateString());
            }
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('profileTypes')]
    public function test_invalid_optional_fields_reject_update_without_changes(string $type): void
    {
        $profile = $this->profile($type);
        foreach ([
            ['password' => 'short'],
            ['photo' => UploadedFile::fake()->create('not-image.pdf', 10, 'application/pdf')],
            ['photo' => UploadedFile::fake()->image('large.jpg')->size(2049)],
            ['birth_date' => '31/02/1990'],
        ] as $invalid) {
            $this->post("/api/v1/{$type}/{$profile->id}", $invalid + [
                '_method' => 'PUT', 'full_name' => 'Must not change',
            ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors(array_keys($invalid));
            $this->assertSame('Original Name', $profile->fresh()->full_name);
            $this->assertSame(["{$type}/old.jpg"], Storage::disk('public')->allFiles($type));
        }
        $this->putJson("/api/v1/{$type}/{$profile->id}", ['phone' => '1234'])->assertUnprocessable();
        $this->assertSame('6281234567890', $profile->fresh()->phone);
    }

    public function test_cadre_working_area_can_be_cleared(): void
    {
        $cadre = $this->profile('cadres');
        $this->putJson("/api/v1/cadres/{$cadre->id}", ['working_area' => ''])->assertOk();
        $this->assertNull($cadre->fresh()->working_area);
        $this->putJson("/api/v1/cadres/{$cadre->id}", ['working_area' => str_repeat('x', 256)])
            ->assertUnprocessable()->assertJsonValidationErrors('working_area');
    }

    public function test_cadre_multipart_update_accepts_creation_fields_and_updates_account(): void
    {
        $cadre = $this->profile('cadres');
        $this->post("/api/v1/cadres/{$cadre->id}", [
            '_method' => 'PUT', 'full_name' => 'Updated Cadre', 'nik' => '1234567890123457',
            'birth_place' => 'New city', 'birth_date' => '25/12/1992', 'gender' => 'P',
            'phone' => '+62 812-3456-7891', 'password' => 'new-password',
            'rt' => '003', 'rw' => '004', 'full_address' => 'New address',
            'working_area' => 'New area', 'photo' => UploadedFile::fake()->image('new.png'),
        ], ['Accept' => 'application/json'])->assertOk();
        $cadre->refresh();
        $this->assertDatabaseHas('cadres', [
            'id' => $cadre->id, 'full_name' => 'Updated Cadre', 'nik' => '1234567890123457',
            'birth_place' => 'New city', 'birth_date' => '1992-12-25', 'gender' => 'P',
            'phone' => '6281234567891', 'rt' => '003', 'rw' => '004',
            'full_address' => 'New address', 'working_area' => 'New area',
        ]);
        $this->assertSame('Updated Cadre', $cadre->user->name);
        $this->assertSame('6281234567891', $cadre->user->phone);
        $this->assertTrue(Hash::check('new-password', $cadre->user->password));
        $this->assertNotSame('cadres/old.jpg', $cadre->photo_path);
        Storage::disk('public')->assertExists($cadre->photo_path);
    }

    public function test_patient_multipart_update_accepts_creation_fields_and_updates_account(): void
    {
        $patient = $this->profile('patients');
        $this->post("/api/v1/patients/{$patient->id}", [
            '_method' => 'PUT', 'full_name' => 'Updated Patient', 'nik' => '1234567890123457',
            'birth_date' => '25/12/1992', 'gender' => 'P', 'phone' => '+62 812-3456-7891',
            'password' => 'new-password', 'rt' => '003', 'rw' => '004',
            'full_address' => 'New address', 'treatment_place_id' => $patient->treatment_place_id,
            'cadre_id' => null, 'treatment_start_date' => '2026-02-02',
            'tb_diagnosis' => 'TB Ekstraparu', 'diagnosis_type' => 'Bakteriologis',
            'daily_dose_frequency' => 2, 'photo' => UploadedFile::fake()->image('new.png'),
            'birth_place' => 'Ignored city',
        ], ['Accept' => 'application/json'])->assertOk()->assertJsonMissingPath('data.birth_place');
        $patient->refresh();
        $this->assertSame('1992-12-25', $patient->birth_date->toDateString());
        $this->assertSame('2026-02-02', $patient->treatment_start_date->toDateString());
        $this->assertDatabaseHas('patients', [
            'id' => $patient->id, 'full_name' => 'Updated Patient', 'phone' => '6281234567891',
            'nik' => '1234567890123457', 'gender' => 'P', 'rt' => '003', 'rw' => '004',
            'full_address' => 'New address', 'tb_diagnosis' => 'TB Ekstraparu',
            'diagnosis_type' => 'Bakteriologis', 'daily_dose_frequency' => 2, 'birth_place' => '',
        ]);
        $this->assertSame('Updated Patient', $patient->user->name);
        $this->assertSame('6281234567891', $patient->user->phone);
        $this->assertTrue(Hash::check('new-password', $patient->user->password));
        $this->assertNotSame('patients/old.jpg', $patient->photo_path);
        Storage::disk('public')->assertExists($patient->photo_path);
    }
}
