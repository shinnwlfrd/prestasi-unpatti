<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AchievementLevel;
use App\Models\DashboardAggregation;
use App\Models\ExecutiveSetting;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\UserRole;
use App\Notifications\SlaBreachDetected;
use App\Services\Admin\DashboardService;
use App\Services\Validator\ValidatorDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardAndSlaTest extends TestCase
{
    use RefreshDatabase;

    protected AcademicPeriod $period;

    protected AchievementCategory $category;

    protected AchievementLevel $level;

    protected Student $student;

    protected Achievement $achievementTemplate;

    protected User $operatorUser;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Base Setup
        $this->category = AchievementCategory::create(['name' => 'Akademik', 'is_active' => true]);
        $this->level = AchievementLevel::create(['name' => 'Nasional', 'points' => 15, 'is_active' => true]);

        $this->period = AcademicPeriod::create([
            'name' => 'Genap 2023/2024',
            'code' => '20232',
            'year' => '2023',
            'semester' => 'Genap',
            'start_date' => '2024-03-01',
            'end_date' => '2024-08-31',
            'submission_deadline' => now()->addDays(10),
            'validation_deadline' => now()->addDays(20),
            'is_active' => true,
        ]);

        $this->student = Student::forceCreate([
            'student_id' => 'S123',
            'name' => 'Mahasiswa Test',
            'email' => 'mhs@test.com',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Ekonomi',
            'department_id' => '10',
            'department' => 'Manajemen',
            'program_study_id' => '100',
            'program_study' => 'S1 Manajemen',
            'gpa' => 3.5,
            'angkatan' => '2021',
        ]);

        $this->achievementTemplate = Achievement::create([
            'name' => 'Lomba Akademik',
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        // 2. Setup Users & Roles
        $this->operatorUser = User::forceCreate([
            'name' => 'Operator FE',
            'email' => 'op.fe@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Validator',
            'faculty' => 'Fakultas Ekonomi',
            'faculty_id' => '1',
        ]);
        UserRole::create([
            'user_id' => $this->operatorUser->id,
            'role' => 'operator',
            'level' => 'faculty',
            'faculty_id' => '1',
            'faculty_name' => 'Fakultas Ekonomi',
            'is_active' => true,
        ]);

        $this->adminUser = User::forceCreate([
            'name' => 'Admin Univ',
            'email' => 'admin@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);
        UserRole::create([
            'user_id' => $this->adminUser->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
        ]);
    }

    #[Test]
    public function student_achievement_modifications_bust_cache_and_trigger_rebuild(): void
    {
        Cache::forever('dashboard_cache_version', 1);
        $initialVersion = StudentAchievement::getDashboardCacheVersion();
        $this->assertEquals(1, $initialVersion);

        // 1. Create achievement (should increment version and run rebuild)
        $achievement = StudentAchievement::create([
            'student_id' => $this->student->student_id,
            'achievement_id' => $this->achievementTemplate->id,
            'academic_period_id' => $this->period->id,
            'event_name' => 'Lomba Nasional',
            'organizer' => 'Unpatti',
            'level' => 'Nasional',
            'event_date' => now(),
            'validation_status' => StudentAchievement::STATUS_SUBMITTED,
        ]);

        $versionAfterCreate = StudentAchievement::getDashboardCacheVersion();
        $this->assertGreaterThan($initialVersion, $versionAfterCreate);

        // Check if DB Aggregation has been populated
        $this->assertDatabaseHas('dashboard_aggregations', [
            'academic_period_id' => $this->period->id,
            'validation_status' => StudentAchievement::STATUS_SUBMITTED,
            'faculty_id' => '1',
            'total_count' => 1,
        ]);

        // 2. Update achievement (should increment version again)
        $achievement->update([
            'validation_status' => StudentAchievement::STATUS_FACULTY_APPROVED,
        ]);

        $versionAfterUpdate = StudentAchievement::getDashboardCacheVersion();
        $this->assertGreaterThan($versionAfterCreate, $versionAfterUpdate);

        $this->assertDatabaseHas('dashboard_aggregations', [
            'academic_period_id' => $this->period->id,
            'validation_status' => StudentAchievement::STATUS_FACULTY_APPROVED,
            'faculty_id' => '1',
            'total_count' => 1,
        ]);

        // 3. Delete achievement (should increment version again)
        $achievement->delete();

        $versionAfterDelete = StudentAchievement::getDashboardCacheVersion();
        $this->assertGreaterThan($versionAfterUpdate, $versionAfterDelete);

        // Aggregation should be rebuilt and the row count for active achievements updated
        $this->assertDatabaseMissing('dashboard_aggregations', [
            'academic_period_id' => $this->period->id,
            'validation_status' => StudentAchievement::STATUS_FACULTY_APPROVED,
            'faculty_id' => '1',
            'total_count' => 1,
        ]);
    }

    #[Test]
    public function dashboard_service_retrieves_data_via_aggregation_and_cache(): void
    {
        // Setup initial achievement
        StudentAchievement::create([
            'student_id' => $this->student->student_id,
            'achievement_id' => $this->achievementTemplate->id,
            'academic_period_id' => $this->period->id,
            'event_name' => 'Lomba Nasional',
            'organizer' => 'Unpatti',
            'level' => 'Nasional',
            'event_date' => now(),
            'validation_status' => StudentAchievement::STATUS_UNIVERSITY_APPROVED,
        ]);

        // Force rebuild to ensure aggregation table has the correct data
        DashboardAggregation::rebuild();

        $service = app(DashboardService::class);

        // 1. Get dashboard data
        $data = $service->getDashboardData($this->period->id);
        $this->assertIsArray($data);
        $this->assertEquals(1, $data['stats']['approved']);

        // 2. Test cache functionality
        $version = StudentAchievement::getDashboardCacheVersion();
        $cacheKey = "admin_dashboard_data_{$this->period->id}_v{$version}";
        $this->assertTrue(Cache::has($cacheKey));

        // Let's modify DB without hook (to see cache isolation)
        $aggregation = DashboardAggregation::first();
        $aggregation->total_count = 99;
        $aggregation->saveQuietly();

        // Should return cached data (1, not 99)
        $dataCached = $service->getDashboardData($this->period->id);
        $this->assertEquals(1, $dataCached['stats']['approved']);

        // Invalidate cache
        StudentAchievement::invalidateDashboardCache();

        // Should now return fresh data from aggregations (99)
        $dataFresh = $service->getDashboardData($this->period->id);
        $this->assertEquals(99, $dataFresh['stats']['approved']);
    }

    #[Test]
    public function validator_dashboard_service_caches_by_role_scope(): void
    {
        // Authenticate the user for direct service call
        auth()->login($this->operatorUser);

        // Mock current validator session context
        session([
            'active_role_id' => $this->operatorUser->activeRoles()->first()->id,
            'active_role_type' => 'operator',
            'operator_level' => 'faculty',
            'operator_faculty_id' => '1',
        ]);

        StudentAchievement::create([
            'student_id' => $this->student->student_id,
            'achievement_id' => $this->achievementTemplate->id,
            'academic_period_id' => $this->period->id,
            'event_name' => 'Lomba Nasional',
            'organizer' => 'Unpatti',
            'level' => 'Nasional',
            'event_date' => now(),
            'validation_status' => StudentAchievement::STATUS_SUBMITTED,
        ]);

        $service = app(ValidatorDashboardService::class);

        // Fetch data
        $request = new Request();
        $data = $service->getDashboardData($request);

        $this->assertIsArray($data);
        $this->assertEquals(1, $data['totalAchievements']);

        // Verify it was cached using validator key pattern
        $version = StudentAchievement::getDashboardCacheVersion();
        $scope = $service->getDashboardScope();
        $selectedPeriods = $service->getSelectedPeriods($request);
        $periodsKey = implode('-', $selectedPeriods) ?: 'none';

        $isPimpinan = $scope['isPimpinan'] ? '1' : '0';
        $level = $scope['level'] ?? 'none';
        $facultyId = $scope['facultyId'] ?? 'none';
        $departmentId = $scope['departmentId'] ?? 'none';
        $programStudyId = $scope['programStudyId'] ?? 'none';
        $position = $scope['position'] ?? 'none';

        $userId = $this->operatorUser->id;
        $contextString = "{$userId}_{$isPimpinan}_{$level}_{$facultyId}_{$departmentId}_{$programStudyId}_{$position}_{$periodsKey}";
        $contextHash = md5($contextString);
        $cacheKey = "validator_dashboard_{$contextHash}_v{$version}";

        $this->assertTrue(Cache::has($cacheKey));
    }

    #[Test]
    public function sla_check_breaches_command_detects_and_notifies_properly(): void
    {
        Notification::fake();

        // Configure SLA threshold setting using updateOrCreate
        ExecutiveSetting::updateOrCreate(
            ['key' => 'sla_validation_days'],
            [
                'value' => '7',
                'label' => 'SLA Validation Days',
                'input_type' => 'integer',
                'category' => 'executive_panel',
            ]
        );

        // 1. Create achievement for Faculty Stage Breach (submitted_at > 7 days ago)
        $facultyBreach = StudentAchievement::create([
            'student_id' => $this->student->student_id,
            'achievement_id' => $this->achievementTemplate->id,
            'academic_period_id' => $this->period->id,
            'event_name' => 'Lomba Fakultas Breached',
            'organizer' => 'Unpatti',
            'level' => 'Nasional',
            'event_date' => now(),
            'validation_status' => StudentAchievement::STATUS_SUBMITTED,
            'submitted_at' => now()->subDays(10),
        ]);

        // 2. Create achievement for University Stage Breach (faculty_validated_at > 7 days ago)
        $univBreach = StudentAchievement::create([
            'student_id' => $this->student->student_id,
            'achievement_id' => $this->achievementTemplate->id,
            'academic_period_id' => $this->period->id,
            'event_name' => 'Lomba Univ Breached',
            'organizer' => 'Unpatti',
            'level' => 'Nasional',
            'event_date' => now(),
            'validation_status' => StudentAchievement::STATUS_FACULTY_APPROVED,
            'faculty_validated_at' => now()->subDays(10),
        ]);

        // Run the command
        $this->artisan('sla:check-breaches')->assertExitCode(0);

        // Verify Notifications sent
        // Faculty breach goes to operator level with same faculty ID (1)
        Notification::assertSentTo(
            [$this->operatorUser],
            SlaBreachDetected::class,
            function ($notification, $channels) {
                return in_array('mail', $channels) && in_array('database', $channels);
            }
        );

        // University breach goes to university admin/operators
        Notification::assertSentTo(
            [$this->adminUser],
            SlaBreachDetected::class,
            function ($notification, $channels) {
                return in_array('mail', $channels) && in_array('database', $channels);
            }
        );
    }
}
