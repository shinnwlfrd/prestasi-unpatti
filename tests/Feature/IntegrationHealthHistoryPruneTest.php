<?php

namespace Tests\Feature;

use App\Models\IntegrationHealthCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IntegrationHealthHistoryPruneTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function prune_command_deletes_only_records_older_than_retention_window(): void
    {
        IntegrationHealthCheck::create([
            'service' => 'SIGAP',
            'status' => 'down',
            'base_url' => 'https://sigap.example.test',
            'message' => 'Old record',
            'meta' => ['http_status' => 503],
            'checked_at' => now()->subDays(20),
        ]);

        IntegrationHealthCheck::create([
            'service' => 'SIAKAD',
            'status' => 'up',
            'base_url' => 'https://siakad.example.test',
            'message' => 'Recent record',
            'meta' => ['http_status' => 200],
            'checked_at' => now()->subDays(5),
        ]);

        $this->artisan('integrations:prune-health-history --days=14')
            ->expectsOutputToContain('Histori health check lama dihapus: 1 record.')
            ->assertSuccessful();

        $this->assertDatabaseCount('integration_health_checks', 1);
        $this->assertDatabaseMissing('integration_health_checks', [
            'service' => 'SIGAP',
            'message' => 'Old record',
        ]);
        $this->assertDatabaseHas('integration_health_checks', [
            'service' => 'SIAKAD',
            'message' => 'Recent record',
        ]);
    }
}
