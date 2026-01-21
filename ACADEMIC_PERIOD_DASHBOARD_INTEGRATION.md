# Academic Period Integration - Dashboard Monitoring

## Overview
Menambahkan fitur periode akademik ke dashboard monitoring prestasi dengan distribusi data ke 3 periode dan filter periode.

## Changes Made

### 1. Database Schema

#### Migration: add_academic_period_id_to_student_achievements_table
**File**: `database/migrations/2026_01_21_054039_add_academic_period_id_to_student_achievements_table.php`

```php
$table->unsignedBigInteger('academic_period_id')->nullable()->after('achievement_id');
$table->foreign('academic_period_id')->references('id')->on('academic_periods')->onDelete('set null');
```

#### Migration: add_year_semester_to_academic_periods_table
**File**: `database/migrations/2026_01_21_054255_add_year_semester_to_academic_periods_table.php`

```php
$table->string('year', 20)->nullable()->after('name');
$table->string('semester', 20)->nullable()->after('year');
```

### 2. Models Updated

#### StudentAchievement Model
**File**: `app/Models/StudentAchievement.php`

**Added to fillable**:
```php
'academic_period_id',
```

**Added relationship**:
```php
public function academicPeriod()
{
    return $this->belongsTo(AcademicPeriod::class);
}
```

#### AcademicPeriod Model
**File**: `app/Models/AcademicPeriod.php`

**Added to fillable**:
```php
'year',
'semester',
```

**Added relationship**:
```php
public function achievements()
{
    return $this->hasMany(StudentAchievement::class);
}
```

### 3. Academic Period Seeder

**File**: `database/seeders/AcademicPeriodSeeder.php`

**Creates 3 periods**:
1. Semester Ganjil 2023/2024 (Sep 2023 - Jan 2024) - Inactive
2. Semester Genap 2023/2024 (Feb 2024 - Jul 2024) - Inactive
3. Semester Ganjil 2024/2025 (Sep 2024 - Jan 2025) - Active

**Data Distribution**:
- 30% → Period 1
- 35% → Period 2
- 35% → Period 3

**Seeder Results**:
```
Semester Ganjil 2023/2024: 57 achievements
Semester Genap 2023/2024: 67 achievements
Semester Ganjil 2024/2025: 69 achievements (Active)
```

### 4. Service Layer Updates

**File**: `app/Services/AchievementApprovalService.php`

#### getApprovalStatistics()
**Before**:
```php
public function getApprovalStatistics(): array
```

**After**:
```php
public function getApprovalStatistics(?int $periodId = null): array
```

**Changes**:
- Added optional `$periodId` parameter
- Filters all queries by period if provided
- Uses query cloning for multiple counts

#### getMonthlyTrend()
**Before**:
```php
public function getMonthlyTrend(int $months = 6): array
```

**After**:
```php
public function getMonthlyTrend(int $months = 6, ?int $periodId = null): array
```

**Changes**:
- Added optional `$periodId` parameter
- Filters submitted and approved queries by period

#### getLevelDistribution()
**Before**:
```php
public function getLevelDistribution(): array
```

**After**:
```php
public function getLevelDistribution(?int $periodId = null): array
```

**Changes**:
- Added optional `$periodId` parameter
- Filters level distribution by period

### 5. Controller Updates

**File**: `app/Http/Controllers/AchievementDashboardController.php`

#### index() Method
**New Features**:
- Get period filter from request
- Load all periods for dropdown
- Auto-select active period if no filter
- Pass period filter to all service methods
- Filter pending review by period
- Pass periods and selectedPeriod to view

**Code**:
```php
public function index(Request $request)
{
    // Get period filter
    $periodId = $request->input('period');
    
    // Get all periods for dropdown
    $periods = \App\Models\AcademicPeriod::ordered()->get();
    
    // Get active period if no filter
    if (!$periodId) {
        $activePeriod = \App\Models\AcademicPeriod::active()->first();
        $periodId = $activePeriod?->id;
    }
    
    $selectedPeriod = $periodId ? \App\Models\AcademicPeriod::find($periodId) : null;
    
    // Get statistics with period filter
    $statistics = $this->approvalService->getApprovalStatistics($periodId);
    $monthlyTrend = $this->approvalService->getMonthlyTrend(6, $periodId);
    $levelDistribution = $this->approvalService->getLevelDistribution($periodId);

    // Recent achievements requiring attention
    $query = StudentAchievement::with(['student', 'achievement.category'])
        ->pending()
        ->latest('submitted_at');
    
    if ($periodId) {
        $query->where('academic_period_id', $periodId);
    }
    
    $pendingReview = $query->take(10)->get();

    return view('admin.achievements.dashboard', compact(
        'statistics',
        'monthlyTrend',
        'levelDistribution',
        'pendingReview',
        'periods',
        'selectedPeriod'
    ));
}
```

### 6. Dashboard View Updates

**File**: `resources/views/admin/achievements/dashboard.blade.php`

#### Period Filter Dropdown
**Location**: Header section, next to Export button

**Features**:
- Dropdown with all periods
- Auto-submit on change
- Shows active period indicator
- "Semua Periode" option

**Code**:
```blade
<select name="period" onchange="this.form.submit()" 
    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg...">
    <option value="">Semua Periode</option>
    @foreach($periods as $period)
        <option value="{{ $period->id }}" {{ $selectedPeriod && $selectedPeriod->id == $period->id ? 'selected' : '' }}>
            {{ $period->name }} {{ $period->is_active ? '(Aktif)' : '' }}
        </option>
    @endforeach
</select>
```

#### Period Info Badge
**Location**: Below header, above statistics cards

**Features**:
- Shows selected period name
- Shows date range
- Shows active indicator with pulse animation
- Purple theme
- Only visible when period is selected

**Code**:
```blade
@if($selectedPeriod)
<div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200...">
    <div class="flex items-center gap-3">
        <div class="p-2 bg-purple-100 dark:bg-purple-900/40 rounded-lg">
            <svg class="w-5 h-5 text-purple-600...">...</svg>
        </div>
        <div>
            <p class="text-sm font-medium...">Menampilkan data untuk periode:</p>
            <p class="text-lg font-bold...">{{ $selectedPeriod->name }}</p>
        </div>
    </div>
    <div class="text-right">
        <p class="text-xs...">{{ $selectedPeriod->start_date->format('d M Y') }} - {{ $selectedPeriod->end_date->format('d M Y') }}</p>
        @if($selectedPeriod->is_active)
            <span class="inline-flex items-center gap-1...">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                Periode Aktif
            </span>
        @endif
    </div>
</div>
@endif
```

#### Export Button Update
**Change**: Added period parameter to export URL
```blade
href="{{ route('admin.achievements.dashboard.export', ['format' => 'excel', 'period' => $selectedPeriod?->id]) }}"
```

## Features

### 1. Period Filter
- Dropdown di header dashboard
- Auto-submit on change
- Persists selection across page
- Shows active period by default

### 2. Period Info Badge
- Visual indicator of selected period
- Shows date range
- Active period indicator with animation
- Purple theme matching dashboard

### 3. Filtered Statistics
- Total prestasi per periode
- Pending review per periode
- Approval rate per periode
- Average time to approve per periode

### 4. Filtered Charts
- Monthly trend filtered by period
- Level distribution filtered by period
- Data accurate per period

### 5. Filtered Lists
- Pending review list filtered by period
- Recent submissions filtered by period

## Data Distribution

### Seeder Distribution:
```
Period 1 (Ganjil 2023/2024): 30% = 57 achievements
Period 2 (Genap 2023/2024): 35% = 67 achievements
Period 3 (Ganjil 2024/2025): 35% = 69 achievements (Active)
```

### Total: 193 achievements distributed

## Testing Scenarios

### Test 1: Default View (Active Period)
1. Navigate to dashboard
2. **Expected**: Shows data for active period (Ganjil 2024/2025)
3. **Expected**: Period info badge visible
4. **Expected**: Statistics show 69 achievements

### Test 2: Change Period
1. Select "Semester Genap 2023/2024" from dropdown
2. **Expected**: Page reloads with new data
3. **Expected**: Statistics show 67 achievements
4. **Expected**: Period info badge updates

### Test 3: All Periods
1. Select "Semua Periode" from dropdown
2. **Expected**: Shows all 193 achievements
3. **Expected**: Period info badge hidden
4. **Expected**: Charts show all data

### Test 4: Export with Period
1. Select a period
2. Click "Export Data"
3. **Expected**: Export includes period parameter
4. **Expected**: Exported data filtered by period

### Test 5: Charts Update
1. Change period
2. **Expected**: Monthly trend chart updates
3. **Expected**: Level distribution chart updates
4. **Expected**: Data accurate for selected period

## Benefits

### 1. Better Data Organization
- Achievements organized by academic period
- Easy to compare periods
- Historical data preserved

### 2. Accurate Reporting
- Statistics per period
- Trends per period
- Comparisons possible

### 3. User Experience
- Easy period selection
- Visual feedback
- Auto-select active period

### 4. Data Integrity
- Foreign key relationship
- Cascade on delete (set null)
- No orphaned data

## Files Modified/Created

### Created:
1. `database/migrations/2026_01_21_054039_add_academic_period_id_to_student_achievements_table.php`
2. `database/migrations/2026_01_21_054255_add_year_semester_to_academic_periods_table.php`
3. `database/seeders/AcademicPeriodSeeder.php`
4. `ACADEMIC_PERIOD_DASHBOARD_INTEGRATION.md`

### Modified:
1. `app/Models/StudentAchievement.php` - Added fillable, relationship
2. `app/Models/AcademicPeriod.php` - Added fillable, relationship
3. `app/Services/AchievementApprovalService.php` - Added period filter to all methods
4. `app/Http/Controllers/AchievementDashboardController.php` - Added period filter logic
5. `resources/views/admin/achievements/dashboard.blade.php` - Added period filter UI

## Status: COMPLETE ✅

All features implemented:
- ✅ Database schema updated
- ✅ Models updated with relationships
- ✅ Data distributed to 3 periods
- ✅ Service layer supports period filter
- ✅ Controller handles period filter
- ✅ Dashboard UI with period dropdown
- ✅ Period info badge
- ✅ Export with period filter
- ✅ No diagnostics errors

---

**Implementation Date**: January 21, 2026  
**Status**: Production Ready ✅
