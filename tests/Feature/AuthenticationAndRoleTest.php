<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAndRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_username(): void
    {
        User::create(['name' => 'Admin', 'username' => 'admin-test', 'password' => Hash::make('password123'), 'role' => 'admin', 'is_active' => true]);
        $this->postJson('/api/v1/auth/login', ['login' => 'admin-test', 'password' => 'password123'])
            ->assertOk()->assertJsonPath('success', true)->assertJsonPath('data.user.role', 'admin')->assertJsonStructure(['data' => ['token']]);
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::create(['name' => 'Inactive', 'username' => 'inactive-test', 'password' => Hash::make('password123'), 'role' => 'staff', 'is_active' => false]);
        $this->postJson('/api/v1/auth/login', ['login' => 'inactive-test', 'password' => 'password123'])->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_patient_cannot_open_staff_dashboard(): void
    {
        $user = User::create(['name' => 'Patient', 'phone' => '6281234567890', 'password' => Hash::make('password123'), 'role' => 'pasien', 'is_active' => true]);
        $this->actingAs($user, 'sanctum')->getJson('/api/v1/dashboard/summary')->assertForbidden();
    }

    public function test_only_admin_can_create_staff_user(): void
    {
        $staff = User::create(['name' => 'Staff', 'username' => 'staff-test', 'password' => Hash::make('password123'), 'role' => 'staff', 'is_active' => true]);
        $this->actingAs($staff, 'sanctum')->postJson('/api/v1/users', ['name' => 'Another', 'username' => 'another-user', 'password' => 'password123', 'role' => 'staff'])->assertForbidden();
    }
}
