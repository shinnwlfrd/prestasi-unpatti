<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OfflineFeaturesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function layouts_include_pwa_assets_and_offline_banner(): void
    {
        $user = User::forceCreate([
            'name' => 'Test User',
            'email' => 'test@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);

        $role = UserRole::create([
            'user_id' => $user->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'admin',
            ])
            ->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('manifest.json');
        $response->assertSee('service-worker.js');
        $response->assertSee('offline-status-banner');
        $response->assertSee('offline-status.js');
    }

    #[Test]
    public function student_submit_page_includes_recovery_script(): void
    {
        $blade = file_get_contents(resource_path('views/student/submit.blade.php'));
        $this->assertStringContainsString('student-submit-recovery.js', $blade);

        $js = file_get_contents(public_path('js/student-submit-recovery.js'));
        $this->assertStringContainsString('simapres_student_submit_draft', $js);
        $this->assertStringContainsString('localStorage', $js);
    }

    #[Test]
    public function public_pwa_files_are_accessible(): void
    {
        $this->assertFileExists(public_path('manifest.json'));
        $this->assertFileExists(public_path('service-worker.js'));
        $this->assertFileExists(public_path('offline.html'));
    }
}
