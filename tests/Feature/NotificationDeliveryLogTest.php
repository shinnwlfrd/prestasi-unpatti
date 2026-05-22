<?php

namespace Tests\Feature;

use App\Models\NotificationDeliveryLog;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationDeliveryLogTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_list_notification_delivery_logs_with_filter(): void
    {
        $admin = User::forceCreate([
            'name' => 'Admin Log',
            'email' => 'admin.log@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
            'is_active' => true,
        ]);

        $role = UserRole::create([
            'user_id' => $admin->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
        ]);

        NotificationDeliveryLog::create([
            'sa_id' => 1001,
            'student_id' => '20260001',
            'user_id' => $admin->id,
            'notification_type' => 'TestNotification',
            'action' => 'faculty_approved',
            'status' => 'sent',
            'channel' => 'mail,database',
        ]);

        NotificationDeliveryLog::create([
            'sa_id' => 1002,
            'student_id' => '20260002',
            'user_id' => $admin->id,
            'notification_type' => 'TestNotification',
            'action' => 'faculty_rejected',
            'status' => 'failed',
            'channel' => 'mail,database',
            'error_message' => 'SMTP timeout',
        ]);

        $response = $this->actingAs($admin)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'admin',
            ])
            ->getJson(route('admin.notification-delivery-logs.index', [
                'status' => 'failed',
            ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.status', 'failed')
            ->assertJsonPath('data.data.0.student_id', '20260002');
    }

    #[Test]
    public function admin_notification_log_page_shows_retry_summary_cards(): void
    {
        $admin = User::forceCreate([
            'name' => 'Admin Log UI',
            'email' => 'admin.log.ui@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
            'is_active' => true,
        ]);

        $role = UserRole::create([
            'user_id' => $admin->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
        ]);

        NotificationDeliveryLog::create([
            'sa_id' => 1003,
            'student_id' => '20260003',
            'user_id' => $admin->id,
            'notification_type' => 'TestNotification',
            'action' => 'faculty_rejected',
            'status' => 'max_retries_exceeded',
            'channel' => 'mail,database',
            'retry_count' => 5,
            'last_retry_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'admin',
            ])
            ->get(route('admin.notification-delivery-logs.index'));

        $response->assertOk()
            ->assertSee('Max Retries Exceeded')
            ->assertSee('Retried Today');
    }

    #[Test]
    public function admin_can_read_notification_delivery_log_summary_endpoint(): void
    {
        $admin = User::forceCreate([
            'name' => 'Admin Summary',
            'email' => 'admin.summary@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
            'is_active' => true,
        ]);

        $role = UserRole::create([
            'user_id' => $admin->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
        ]);

        NotificationDeliveryLog::create([
            'sa_id' => 2001,
            'student_id' => '20260010',
            'user_id' => $admin->id,
            'notification_type' => 'TestNotification',
            'action' => 'faculty_approved',
            'status' => 'sent',
            'channel' => 'mail,database',
            'retry_count' => 1,
            'last_retry_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($admin)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'admin',
            ])
            ->getJson(route('admin.notification-delivery-logs.summary'));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('summary.total', 1)
            ->assertJsonPath('summary.sent', 1)
            ->assertJsonPath('summary.retried_today', 0);
    }
}
