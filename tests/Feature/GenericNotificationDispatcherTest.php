<?php

namespace Tests\Feature;

use App\Models\StudentAchievement;
use App\Models\User;
use App\Notifications\AchievementStatusChanged;
use App\Support\GenericNotificationDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GenericNotificationDispatcherTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function generic_dispatcher_sends_notification_and_logs(): void
    {
        $user = User::forceCreate([
            'name' => 'Test User',
            'email' => 'test@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);

        \Illuminate\Support\Facades\Notification::fake();

        $notification = new AchievementStatusChanged(
            new StudentAchievement(['sa_id' => 1, 'event_name' => 'Test Event', 'level' => 'Nasional']),
            'test_action'
        );

        $log = GenericNotificationDispatcher::send($user, $notification, [
            'action' => 'test_action',
            'student_id' => '20260001',
        ]);

        $this->assertNotNull($log);
        $this->assertEquals('sent', $log->status);
        $this->assertEquals('test_action', $log->action);
        $this->assertEquals('20260001', $log->student_id);

        \Illuminate\Support\Facades\Notification::assertSentTo($user, AchievementStatusChanged::class);
    }
}
