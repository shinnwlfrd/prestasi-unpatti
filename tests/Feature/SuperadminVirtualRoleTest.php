<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\AchievementCategory;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SuperadminVirtualRoleTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup basic data
        AcademicPeriod::create([
            'name' => '2025/2026 Ganjil',
            'code' => '20251',
            'year' => '2025/2026',
            'semester' => 'Ganjil',
            'start_date' => '2025-09-01',
            'end_date' => '2026-01-31',
            'is_active' => true,
        ]);

        AchievementCategory::create(['name' => 'Akademik', 'is_active' => true]);

        // Create Super Admin
        $this->superAdmin = User::forceCreate([
            'name' => 'Super Admin Test',
            'email' => 'superadmin@test.com',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);

        UserRole::create([
            'user_id' => $this->superAdmin->id,
            'role' => 'super_admin',
            'level' => 'university',
            'is_active' => true,
        ]);
    }

    #[Test]
    public function test_superadmin_can_switch_to_virtual_pimpinan_role(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->post('/api/switch-role', [
            'role_id' => 'virtual_pimpinan_university',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect_url' => route('pimpinan.dashboard'),
            'role' => 'Pimpinan Universitas',
        ]);

        // Verify session state
        $this->assertEquals('virtual_pimpinan_university', session('active_role_id'));
        $this->assertEquals('pimpinan', session('active_role_type'));
        $this->assertEquals('university', session('pimpinan_level'));
        $this->assertEquals('super_admin', session('pimpinan_position'));

        // Verify role switching does not mutate legacy database role
        $this->superAdmin->refresh();
        $this->assertEquals('Admin', $this->superAdmin->role);

        // Verify access to pimpinan dashboard
        $response = $this->withSession([
            'active_role_id' => 'virtual_pimpinan_university',
            'active_role_type' => 'pimpinan',
            'pimpinan_level' => 'university',
            'pimpinan_position' => 'super_admin',
        ])->get('/pimpinan');
        $response->assertStatus(200);
    }

    #[Test]
    public function test_superadmin_can_switch_to_virtual_validator_role(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->post('/api/switch-role', [
            'role_id' => 'virtual_validator_university',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect_url' => route('validator.pending.index'),
            'role' => 'Super Operator',
        ]);

        // Verify session state
        $this->assertEquals('virtual_validator_university', session('active_role_id'));
        $this->assertEquals('operator', session('active_role_type'));
        $this->assertEquals('university', session('operator_level'));

        // Verify role switching does not mutate legacy database role
        $this->superAdmin->refresh();
        $this->assertEquals('Admin', $this->superAdmin->role);

        // Verify access to validator pending index
        $response = $this->withSession([
            'active_role_id' => 'virtual_validator_university',
            'active_role_type' => 'operator',
            'operator_level' => 'university',
        ])->get('/validator/pending');
        $response->assertStatus(200);
    }

    #[Test]
    public function test_switching_clears_old_session_variables(): void
    {
        $this->actingAs($this->superAdmin);

        // First switch to validator
        $this->post('/api/switch-role', ['role_id' => 'virtual_validator_university']);
        $this->assertEquals('university', session('operator_level'));

        // Then switch to pimpinan
        $this->post('/api/switch-role', ['role_id' => 'virtual_pimpinan_university']);

        // Old validator variables should be gone
        $this->assertFalse(session()->has('operator_level'));
        // New pimpinan variables should be present
        $this->assertEquals('university', session('pimpinan_level'));
    }
}
