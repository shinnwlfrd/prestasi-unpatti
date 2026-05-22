<?php

namespace Tests\Feature;

use App\Models\IntegrationHealthCheck;
use App\Models\User;
use App\Models\UserRole;
use App\Services\SiakadApiService;
use App\Services\SigapApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IntegrationHealthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_read_integration_health_status(): void
    {
        $admin = User::forceCreate([
            'name' => 'Admin Health',
            'email' => 'admin.health@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);

        $role = UserRole::create([
            'user_id' => $admin->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
        ]);

        IntegrationHealthCheck::create([
            'service' => 'SIAKAD',
            'status' => 'down',
            'message' => 'Timeout 1',
            'checked_at' => now()->subMinutes(15),
        ]);

        IntegrationHealthCheck::create([
            'service' => 'SIAKAD',
            'status' => 'down',
            'message' => 'Timeout 2',
            'checked_at' => now()->subMinutes(10),
        ]);

        IntegrationHealthCheck::create([
            'service' => 'SIAKAD',
            'status' => 'down',
            'message' => 'Timeout 3',
            'checked_at' => now()->subMinutes(5),
        ]);

        $sigap = \Mockery::mock(SigapApiService::class);
        $sigap->shouldReceive('getHealthStatus')->once()->andReturn([
            'service' => 'SIGAP',
            'status' => 'up',
            'message' => 'SIGAP sehat',
            'base_url' => 'https://sigap.test',
            'meta' => [],
            'checked_at' => now()->toIso8601String(),
        ]);

        $siakad = \Mockery::mock(SiakadApiService::class);
        $siakad->shouldReceive('getHealthStatus')->once()->andReturn([
            'service' => 'SIAKAD',
            'status' => 'down',
            'message' => 'SIAKAD timeout',
            'base_url' => 'https://siakad.test',
            'meta' => [],
            'checked_at' => now()->toIso8601String(),
        ]);

        $this->app->instance(SigapApiService::class, $sigap);
        $this->app->instance(SiakadApiService::class, $siakad);

        $response = $this->actingAs($admin)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'admin',
            ])
            ->getJson(route('admin.api.integration-health'));

        $response->assertOk()
            ->assertJsonPath('overall_status', 'degraded')
            ->assertJsonPath('services.0.service', 'SIGAP')
            ->assertJsonPath('services.1.service', 'SIAKAD')
            ->assertJsonPath('services.1.status', 'down')
            ->assertJsonPath('alert_summary.total_services', 2)
            ->assertJsonPath('alert_summary.down_services', 1)
            ->assertJsonPath('alert_summary.services_requiring_attention', 1)
            ->assertJsonPath('alert_summary.affected_services.0.service', 'SIAKAD')
            ->assertJsonPath('alert_summary.affected_services.0.consecutive_failures', 3)
            ->assertJsonPath('alert_summary.affected_services.0.attention_level', 'critical');
    }
}
