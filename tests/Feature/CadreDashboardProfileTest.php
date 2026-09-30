<?php

namespace Tests\Feature;

use App\Models\Cadre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CadreDashboardProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_cadre_dashboard_returns_cadre_profile_photo(): void
    {
        $user = User::factory()->create([
            'name' => 'Djulaiha',
            'role' => 'kader',
        ]);

        Cadre::create([
            'user_id' => $user->id,
            'nik' => '3174000000000001',
            'full_name' => 'Djulaiha',
            'birth_place' => 'Jakarta',
            'birth_date' => '1980-01-01',
            'gender' => 'P',
            'phone' => '081200000001',
            'rt' => '001',
            'rw' => '002',
            'full_address' => 'Petukangan Utara',
            'photo_path' => 'cadres/djulaiha.jpg',
            'is_active' => true,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/kader/dashboard')
            ->assertOk()
            ->assertJsonPath('data.cadre.full_name', 'Djulaiha')
            ->assertJsonPath('data.cadre.photo_path', 'cadres/djulaiha.jpg');
    }
}
