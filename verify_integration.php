<?php

/**
 * Integration Verification Script
 * 
 * This script verifies that all integrations (SSO, SIAKAD, SIGAP) are properly configured
 * Run: php verify_integration.php
 */

echo "=================================================================\n";
echo "  INTEGRATION VERIFICATION - SSO, SIAKAD, SIGAP\n";
echo "=================================================================\n\n";

$errors = [];
$warnings = [];
$success = [];

// Check if running in Laravel context
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die("❌ Error: vendor/autoload.php not found. Run 'composer install' first.\n");
}

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "✓ Laravel application bootstrapped\n\n";

// ============================================================================
// 1. CHECK ENVIRONMENT VARIABLES
// ============================================================================
echo "1. Checking Environment Variables...\n";
echo "-----------------------------------\n";

$requiredEnvVars = [
    'SSO' => [
        'SIAKAD_SSO_URL',
        'SIAKAD_SSO_CLIENT_ID',
        'SIAKAD_SSO_CLIENT_SECRET',
        'SIAKAD_SSO_REDIRECT_URI',
    ],
    'SIAKAD API' => [
        'SIAKAD_API_URL',
        'SIAKAD_API_KEY',
    ],
];

foreach ($requiredEnvVars as $service => $vars) {
    echo "\n[$service]\n";
    foreach ($vars as $var) {
        $value = env($var);
        if (empty($value)) {
            echo "  ❌ $var: NOT SET\n";
            $errors[] = "$var is not set in .env file";
        } else {
            $masked = $var === 'SIAKAD_SSO_CLIENT_SECRET' || $var === 'SIAKAD_API_KEY' 
                ? substr($value, 0, 10) . '...' 
                : $value;
            echo "  ✓ $var: $masked\n";
            $success[] = "$var is configured";
        }
    }
}

// ============================================================================
// 2. CHECK CONFIGURATION FILES
// ============================================================================
echo "\n\n2. Checking Configuration Files...\n";
echo "-----------------------------------\n";

$configFiles = [
    'config/sso.php' => 'SSO Configuration',
    'config/services.php' => 'Services Configuration',
];

foreach ($configFiles as $file => $description) {
    if (file_exists($file)) {
        echo "✓ $description: EXISTS\n";
        $success[] = "$description file exists";
    } else {
        echo "❌ $description: NOT FOUND\n";
        $errors[] = "$description file not found";
    }
}

// Check services.php has siakad config
$servicesConfig = config('services.siakad');
if ($servicesConfig) {
    echo "✓ SIAKAD service configured in services.php\n";
    $success[] = "SIAKAD service configured";
} else {
    echo "❌ SIAKAD service NOT configured in services.php\n";
    $errors[] = "SIAKAD service not configured";
}

// ============================================================================
// 3. CHECK SERVICE CLASSES
// ============================================================================
echo "\n\n3. Checking Service Classes...\n";
echo "-----------------------------------\n";

$serviceClasses = [
    'App\Services\SiakadApiService' => 'SIAKAD API Service',
    'App\Services\SigapApiService' => 'SIGAP API Service',
];

foreach ($serviceClasses as $class => $description) {
    if (class_exists($class)) {
        echo "✓ $description: EXISTS\n";
        $success[] = "$description class exists";
        
        // Check methods
        $reflection = new ReflectionClass($class);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        echo "  Methods: " . count($methods) . "\n";
        
        if ($class === 'App\Services\SiakadApiService') {
            $requiredMethods = ['searchMahasiswa', 'getMahasiswaByNim', 'getMahasiswaById', 'transformToStudentData'];
            foreach ($requiredMethods as $method) {
                if ($reflection->hasMethod($method)) {
                    echo "    ✓ $method()\n";
                } else {
                    echo "    ❌ $method() NOT FOUND\n";
                    $errors[] = "$method() not found in $class";
                }
            }
        }
        
        if ($class === 'App\Services\SigapApiService') {
            $requiredMethods = ['getFaculties', 'getDepartments', 'getStudyPrograms'];
            foreach ($requiredMethods as $method) {
                if ($reflection->hasMethod($method)) {
                    echo "    ✓ $method()\n";
                } else {
                    echo "    ❌ $method() NOT FOUND\n";
                    $errors[] = "$method() not found in $class";
                }
            }
        }
    } else {
        echo "❌ $description: NOT FOUND\n";
        $errors[] = "$description class not found";
    }
}

// ============================================================================
// 4. CHECK CONTROLLERS
// ============================================================================
echo "\n\n4. Checking Controllers...\n";
echo "-----------------------------------\n";

$controllers = [
    'App\Http\Controllers\SSO\AuthController' => 'SSO Auth Controller',
    'App\Http\Controllers\Api\SiakadMahasiswaController' => 'SIAKAD Mahasiswa API Controller',
    'App\Http\Controllers\Admin\AdminAchievementController' => 'Admin Achievement Controller',
];

foreach ($controllers as $class => $description) {
    if (class_exists($class)) {
        echo "✓ $description: EXISTS\n";
        $success[] = "$description exists";
    } else {
        echo "❌ $description: NOT FOUND\n";
        $errors[] = "$description not found";
    }
}

// ============================================================================
// 5. CHECK ROUTES
// ============================================================================
echo "\n\n5. Checking Routes...\n";
echo "-----------------------------------\n";

$requiredRoutes = [
    'sso.redirect' => 'SSO Redirect',
    'sso.callback' => 'SSO Callback',
    'api.siakad.mahasiswa.search' => 'SIAKAD Search API',
    'api.siakad.mahasiswa.show' => 'SIAKAD Detail API',
];

foreach ($requiredRoutes as $routeName => $description) {
    try {
        $route = route($routeName, ['id' => 'test'], false);
        echo "✓ $description: $route\n";
        $success[] = "$description route exists";
    } catch (\Exception $e) {
        echo "❌ $description: NOT FOUND\n";
        $errors[] = "$description route not found";
    }
}

// ============================================================================
// 6. CHECK DATABASE MIGRATION
// ============================================================================
echo "\n\n6. Checking Database Schema...\n";
echo "-----------------------------------\n";

try {
    $columns = DB::getSchemaBuilder()->getColumnListing('students');
    
    $requiredColumns = [
        'student_id',
        'id_mahasiswa',
        'name',
        'email',
        'foto_url',
        'ipk',
        'angkatan',
        'faculty_id',
        'faculty',
        'department_id',
        'department',
        'program_study_id',
        'program_study',
    ];
    
    echo "Students table columns:\n";
    foreach ($requiredColumns as $column) {
        if (in_array($column, $columns)) {
            echo "  ✓ $column\n";
        } else {
            echo "  ❌ $column: NOT FOUND\n";
            $errors[] = "Column $column not found in students table";
        }
    }
    
    $success[] = "Students table structure verified";
    
} catch (\Exception $e) {
    echo "❌ Error checking database: " . $e->getMessage() . "\n";
    $errors[] = "Database check failed: " . $e->getMessage();
}

// ============================================================================
// 7. CHECK VIEW COMPONENTS
// ============================================================================
echo "\n\n7. Checking View Components...\n";
echo "-----------------------------------\n";

$viewComponents = [
    'resources/views/components/siakad-student-select.blade.php' => 'SIAKAD Student Select Component',
];

foreach ($viewComponents as $file => $description) {
    if (file_exists($file)) {
        echo "✓ $description: EXISTS\n";
        $success[] = "$description exists";
    } else {
        echo "❌ $description: NOT FOUND\n";
        $errors[] = "$description not found";
    }
}

// ============================================================================
// 8. TEST API CONNECTIVITY (Optional)
// ============================================================================
echo "\n\n8. Testing API Connectivity...\n";
echo "-----------------------------------\n";

// Test SIGAP API
echo "\n[SIGAP API]\n";
try {
    $sigapService = app(\App\Services\SigapApiService::class);
    $faculties = $sigapService->getFaculties();
    
    if (count($faculties) > 0) {
        echo "✓ SIGAP API: Connected (Found " . count($faculties) . " faculties)\n";
        $success[] = "SIGAP API is accessible";
    } else {
        echo "⚠ SIGAP API: Connected but no data returned\n";
        $warnings[] = "SIGAP API returned no faculties";
    }
} catch (\Exception $e) {
    echo "❌ SIGAP API: Connection failed - " . $e->getMessage() . "\n";
    $warnings[] = "SIGAP API connection failed (non-critical)";
}

// Test SIAKAD API (if configured)
echo "\n[SIAKAD API]\n";
if (env('SIAKAD_API_KEY')) {
    try {
        $siakadService = app(\App\Services\SiakadApiService::class);
        $result = $siakadService->searchMahasiswa('test', null, null, 1, 1);
        
        if (isset($result['success'])) {
            echo "✓ SIAKAD API: Connected\n";
            $success[] = "SIAKAD API is accessible";
        } else {
            echo "⚠ SIAKAD API: Response format unexpected\n";
            $warnings[] = "SIAKAD API response format unexpected";
        }
    } catch (\Exception $e) {
        echo "❌ SIAKAD API: Connection failed - " . $e->getMessage() . "\n";
        $warnings[] = "SIAKAD API connection failed";
    }
} else {
    echo "⚠ SIAKAD API: API key not configured (skipping test)\n";
    $warnings[] = "SIAKAD API key not configured";
}

// ============================================================================
// SUMMARY
// ============================================================================
echo "\n\n";
echo "=================================================================\n";
echo "  VERIFICATION SUMMARY\n";
echo "=================================================================\n\n";

echo "✓ Success: " . count($success) . "\n";
echo "⚠ Warnings: " . count($warnings) . "\n";
echo "❌ Errors: " . count($errors) . "\n\n";

if (count($errors) > 0) {
    echo "ERRORS:\n";
    foreach ($errors as $i => $error) {
        echo "  " . ($i + 1) . ". $error\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "WARNINGS:\n";
    foreach ($warnings as $i => $warning) {
        echo "  " . ($i + 1) . ". $warning\n";
    }
    echo "\n";
}

if (count($errors) === 0) {
    echo "✅ ALL CRITICAL CHECKS PASSED!\n";
    echo "   Integration is ready for testing.\n\n";
    
    echo "NEXT STEPS:\n";
    echo "1. Configure API credentials in .env file\n";
    echo "2. Test SSO login flow\n";
    echo "3. Test SIAKAD API search\n";
    echo "4. Test admin submit achievement\n";
    echo "5. Verify data is stored correctly\n";
} else {
    echo "❌ INTEGRATION INCOMPLETE\n";
    echo "   Please fix the errors above before proceeding.\n";
}

echo "\n=================================================================\n";
