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

class IntegrationHealthHistoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function health_check_command_persists_history_records(): void
    {
        $sigap = \Mockery::mock(SigapApiService::class);
        $sigap->shouldReceive('getHealthStatus')->once()->andReturn([
            'service' => 'SIGAP',
            'status' => 'up',
            'message' => 'SIGAP sehat',
            'base_url' => 'https://sigap.test',
            'meta' => ['http_status' => 200],
            'checked_at' => now()->toIso8601String(),
        ]);

        $siakad = \Mockery::mock(SiakadApiService::class);
        $siakad->shouldReceive('getHealthStatus')->once()->andReturn([
            'service' => 'SIAKAD',
            'status' => 'down',
            'message' => 'SIAKAD timeout',
            'base_url' => 'https://siakad.test',
            'meta' => ['http_status' => 504],
            'checked_at' => now()->toIso8601String(),
        ]);

        $this->app->instance(SigapApiService::class, $sigap);
        $this->app->instance(SiakadApiService::class, $siakad);

        $this->artisan('integrations:check-health')
            ->expectsOutput('Health check integrasi selesai.')
            ->assertExitCode(0);

        $this->assertDatabaseCount('integration_health_checks', 2);
        $this->assertDatabaseHas('integration_health_checks', [
            'service' => 'SIGAP',
            'status' => 'up',
        ]);
        $this->assertDatabaseHas('integration_health_checks', [
            'service' => 'SIAKAD',
            'status' => 'down',
        ]);
    }

    #[Test]
    public function admin_health_endpoint_includes_recent_history(): void
    {
        $admin = User::forceCreate([
            'name' => 'Admin Health History',
            'email' => 'admin.health.history@unpatti.ac.id',
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
            'service' => 'SIGAP',
            'status' => 'up',
            'message' => 'Histori SIGAP',
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
            'status' => 'up',
            'message' => 'SIAKAD sehat',
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
            ->assertJsonPath('recent_history.SIGAP.0.message', 'Histori SIGAP');
    }
}
