<?php

namespace Tests\Feature;

use App\Models\IntegrationHealthCheck;
use App\Models\User;
use App\Models\UserRole;
use App\Notifications\IntegrationHealthDegraded;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IntegrationHealthAlertTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function repeated_failures_trigger_notification_to_university_admins(): void
    {
        Notification::fake();

        $admin = User::forceCreate([
            'name' => 'Admin Alert',
            'email' => 'admin.alert@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);

        UserRole::create([
            'user_id' => $admin->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
        ]);

        foreach ([3, 2, 1] as $hoursAgo) {
            IntegrationHealthCheck::create([
                'service' => 'SIGAP',
                'status' => 'down',
                'message' => 'SIGAP timeout',
                'checked_at' => now()->subHours($hoursAgo),
            ]);
        }

        $this->artisan('integrations:alert-health --threshold=3 --cooldown=360')
            ->assertExitCode(0);

        Notification::assertSentTo(
            [$admin],
            IntegrationHealthDegraded::class,
            function ($notification, $channels) {
                return in_array('mail', $channels)
                    && in_array('database', $channels);
            }
        );
    }

    #[Test]
    public function recent_recovery_clears_alert_flow(): void
    {
        Notification::fake();

        $admin = User::forceCreate([
            'name' => 'Admin Recovery',
            'email' => 'admin.recovery@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);

        UserRole::create([
            'user_id' => $admin->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
        ]);

        IntegrationHealthCheck::create([
            'service' => 'SIAKAD',
            'status' => 'up',
            'message' => 'Pulih',
            'checked_at' => now()->subHour(),
        ]);
        IntegrationHealthCheck::create([
            'service' => 'SIAKAD',
            'status' => 'down',
            'message' => 'Timeout',
            'checked_at' => now()->subHours(2),
        ]);
        IntegrationHealthCheck::create([
            'service' => 'SIAKAD',
            'status' => 'down',
            'message' => 'Timeout',
            'checked_at' => now()->subHours(3),
        ]);

        $this->artisan('integrations:alert-health --threshold=3 --cooldown=360')
            ->assertExitCode(0);

        Notification::assertNothingSent();
    }
}
