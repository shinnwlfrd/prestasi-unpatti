<?php

/**
 * SSO Diagnostic Script
 * 
 * Run: php diagnose_sso.php
 * 
 * This script checks common SSO configuration issues
 */

echo "=================================================================\n";
echo "  SSO DIAGNOSTIC TOOL\n";
echo "=================================================================\n\n";

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$errors = [];
$warnings = [];
$success = [];

// ============================================================================
// 1. CHECK ENVIRONMENT VARIABLES
// ============================================================================
echo "1. Checking SSO Environment Variables...\n";
echo "-----------------------------------\n";

$requiredVars = [
    'SIAKAD_SSO_URL' => 'SSO Base URL',
    'SIAKAD_SSO_CLIENT_ID' => 'SSO Client ID',
    'SIAKAD_SSO_CLIENT_SECRET' => 'SSO Client Secret',
    'SIAKAD_SSO_REDIRECT_URI' => 'SSO Redirect URI',
    'SIAKAD_SSO_SCOPES' => 'SSO Scopes',
];

foreach ($requiredVars as $var => $description) {
    $value = env($var);
    if (empty($value)) {
        echo "  ❌ $description: NOT SET\n";
        $errors[] = "$var is not set";
    } else {
        $masked = in_array($var, ['SIAKAD_SSO_CLIENT_SECRET']) 
            ? substr($value, 0, 10) . '...' 
            : $value;
        echo "  ✓ $description: $masked\n";
        $success[] = "$description configured";
    }
}

// ============================================================================
// 2. CHECK SESSION CONFIGURATION
// ============================================================================
echo "\n\n2. Checking Session Configuration...\n";
echo "-----------------------------------\n";

$sessionDriver = config('session.driver');
$sessionLifetime = config('session.lifetime');

echo "Session Driver: $sessionDriver\n";
echo "Session Lifetime: $sessionLifetime minutes\n";

if ($sessionDriver === 'database') {
    try {
        $hasSessionsTable = Schema::hasTable('sessions');
        if ($hasSessionsTable) {
            echo "✓ Sessions table exists\n";
            $success[] = "Sessions table exists";
        } else {
            echo "❌ Sessions table NOT found\n";
            $errors[] = "Sessions table missing - run: php artisan session:table && php artisan migrate";
        }
    } catch (\Exception $e) {
        echo "❌ Cannot check sessions table: " . $e->getMessage() . "\n";
        $errors[] = "Database connection failed";
    }
} else {
    echo "✓ Using $sessionDriver driver\n";
    $success[] = "Session driver configured";
}

// ============================================================================
// 3. CHECK SSO ROUTES
// ============================================================================
echo "\n\n3. Checking SSO Routes...\n";
echo "-----------------------------------\n";

$requiredRoutes = [
    'sso.redirect' => 'SSO Redirect',
    'sso.callback' => 'SSO Callback',
    'sso.logout' => 'SSO Logout',
];

foreach ($requiredRoutes as $routeName => $description) {
    try {
        $route = route($routeName, [], false);
        echo "✓ $description: $route\n";
        $success[] = "$description route exists";
    } catch (\Exception $e) {
        echo "❌ $description: NOT FOUND\n";
        $errors[] = "$description route missing";
    }
}

// ============================================================================
// 4. CHECK SSO CONTROLLER
// ============================================================================
echo "\n\n4. Checking SSO Controller...\n";
echo "-----------------------------------\n";

$controllerClass = 'App\Http\Controllers\SSO\AuthController';

if (class_exists($controllerClass)) {
    echo "✓ SSO AuthController exists\n";
    $success[] = "SSO AuthController exists";
    
    $reflection = new ReflectionClass($controllerClass);
    $requiredMethods = ['redirect', 'callback', 'logout'];
    
    foreach ($requiredMethods as $method) {
        if ($reflection->hasMethod($method)) {
            echo "  ✓ Method: $method()\n";
        } else {
            echo "  ❌ Method: $method() NOT FOUND\n";
            $errors[] = "Method $method() missing in AuthController";
        }
    }
} else {
    echo "❌ SSO AuthController NOT found\n";
    $errors[] = "SSO AuthController class missing";
}

// ============================================================================
// 5. TEST SSO SERVER CONNECTIVITY
// ============================================================================
echo "\n\n5. Testing SSO Server Connectivity...\n";
echo "-----------------------------------\n";

$ssoUrl = env('SIAKAD_SSO_URL');

if ($ssoUrl) {
    try {
        echo "Testing: $ssoUrl\n";
        
        $response = Http::timeout(10)->get($ssoUrl);
        
        if ($response->successful() || $response->status() === 404) {
            echo "✓ SSO server is reachable\n";
            $success[] = "SSO server reachable";
        } else {
            echo "⚠ SSO server returned status: " . $response->status() . "\n";
            $warnings[] = "SSO server returned non-200 status";
        }
    } catch (\Exception $e) {
        echo "❌ Cannot reach SSO server: " . $e->getMessage() . "\n";
        $errors[] = "SSO server unreachable";
    }
} else {
    echo "⚠ SSO URL not configured, skipping connectivity test\n";
    $warnings[] = "SSO URL not configured";
}

// ============================================================================
// 6. CHECK REDIRECT URI FORMAT
// ============================================================================
echo "\n\n6. Validating Redirect URI...\n";
echo "-----------------------------------\n";

$redirectUri = env('SIAKAD_SSO_REDIRECT_URI');

if ($redirectUri) {
    // Check if it's a valid URL
    if (filter_var($redirectUri, FILTER_VALIDATE_URL)) {
        echo "✓ Redirect URI is valid URL: $redirectUri\n";
        $success[] = "Redirect URI format valid";
        
        // Check if it matches route
        try {
            $routeUri = route('sso.callback');
            if (str_contains($redirectUri, parse_url($routeUri, PHP_URL_PATH))) {
                echo "✓ Redirect URI matches callback route\n";
                $success[] = "Redirect URI matches route";
            } else {
                echo "⚠ Redirect URI might not match callback route\n";
                echo "  Configured: $redirectUri\n";
                echo "  Route: $routeUri\n";
                $warnings[] = "Redirect URI mismatch with route";
            }
        } catch (\Exception $e) {
            echo "⚠ Cannot verify route match\n";
        }
    } else {
        echo "❌ Redirect URI is not a valid URL: $redirectUri\n";
        $errors[] = "Invalid redirect URI format";
    }
} else {
    echo "❌ Redirect URI not configured\n";
    $errors[] = "Redirect URI not set";
}

// ============================================================================
// 7. CHECK SIAKAD API (for post-SSO data fetch)
// ============================================================================
echo "\n\n7. Checking SIAKAD API Configuration...\n";
echo "-----------------------------------\n";

$siakadUrl = env('SIAKAD_API_URL');
$siakadKey = env('SIAKAD_API_KEY');

if ($siakadUrl && $siakadKey) {
    echo "✓ SIAKAD API URL: $siakadUrl\n";
    echo "✓ SIAKAD API Key: " . substr($siakadKey, 0, 10) . "...\n";
    $success[] = "SIAKAD API configured";
    
    // Test connectivity
    try {
        $response = Http::timeout(10)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $siakadKey,
                'Accept' => 'application/json',
            ])
            ->get($siakadUrl . '/mahasiswa', [
                'page' => 1,
                'page.size' => 1
            ]);
        
        if ($response->successful()) {
            echo "✓ SIAKAD API is accessible\n";
            $success[] = "SIAKAD API accessible";
        } else {
            echo "⚠ SIAKAD API returned status: " . $response->status() . "\n";
            $warnings[] = "SIAKAD API returned non-200 status";
        }
    } catch (\Exception $e) {
        echo "❌ Cannot reach SIAKAD API: " . $e->getMessage() . "\n";
        $warnings[] = "SIAKAD API unreachable (non-critical for SSO)";
    }
} else {
    echo "⚠ SIAKAD API not fully configured\n";
    $warnings[] = "SIAKAD API not configured (will use SSO data as fallback)";
}

// ============================================================================
// 8. CHECK COMMON ISSUES
// ============================================================================
echo "\n\n8. Checking Common Issues...\n";
echo "-----------------------------------\n";

// Check if callback route has auth middleware
try {
    $callbackRoute = Route::getRoutes()->getByName('sso.callback');
    if ($callbackRoute) {
        $middleware = $callbackRoute->middleware();
        if (in_array('auth', $middleware) || in_array('auth:web', $middleware)) {
            echo "❌ WARNING: Callback route has 'auth' middleware!\n";
            echo "   This will cause redirect loop. Remove auth middleware from callback route.\n";
            $errors[] = "Callback route has auth middleware (will cause loop)";
        } else {
            echo "✓ Callback route does not have auth middleware\n";
            $success[] = "Callback route middleware correct";
        }
    }
} catch (\Exception $e) {
    echo "⚠ Cannot check callback route middleware\n";
}

// Check APP_URL
$appUrl = env('APP_URL');
if ($appUrl && $redirectUri) {
    if (str_starts_with($redirectUri, $appUrl)) {
        echo "✓ Redirect URI matches APP_URL\n";
        $success[] = "Redirect URI matches APP_URL";
    } else {
        echo "⚠ Redirect URI does not start with APP_URL\n";
        echo "  APP_URL: $appUrl\n";
        echo "  Redirect URI: $redirectUri\n";
        $warnings[] = "Redirect URI mismatch with APP_URL";
    }
}

// ============================================================================
// SUMMARY
// ============================================================================
echo "\n\n";
echo "=================================================================\n";
echo "  DIAGNOSTIC SUMMARY\n";
echo "=================================================================\n\n";

echo "✓ Success: " . count($success) . "\n";
echo "⚠ Warnings: " . count($warnings) . "\n";
echo "❌ Errors: " . count($errors) . "\n\n";

if (count($errors) > 0) {
    echo "ERRORS (Must Fix):\n";
    foreach ($errors as $i => $error) {
        echo "  " . ($i + 1) . ". $error\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "WARNINGS (Should Check):\n";
    foreach ($warnings as $i => $warning) {
        echo "  " . ($i + 1) . ". $warning\n";
    }
    echo "\n";
}

if (count($errors) === 0 && count($warnings) === 0) {
    echo "✅ ALL CHECKS PASSED!\n";
    echo "   SSO configuration looks good.\n\n";
    
    echo "NEXT STEPS:\n";
    echo "1. Test SSO login in browser\n";
    echo "2. Check logs: tail -f storage/logs/laravel.log | grep SSO\n";
    echo "3. If issues persist, check SSO_TROUBLESHOOTING_GUIDE.md\n";
} else {
    echo "⚠ ISSUES FOUND\n";
    echo "   Please fix the errors above.\n";
    echo "   See SSO_TROUBLESHOOTING_GUIDE.md for detailed solutions.\n";
}

echo "\n=================================================================\n";

// ============================================================================
// GENERATE TEST URL
// ============================================================================
if (count($errors) === 0) {
    echo "\n📋 TEST SSO LOGIN:\n";
    echo "-----------------------------------\n";
    
    try {
        $testUrl = route('sso.redirect');
        echo "Visit this URL to test SSO login:\n";
        echo "$testUrl\n\n";
        
        echo "Expected flow:\n";
        echo "1. Redirect to SSO login page\n";
        echo "2. Enter SSO credentials\n";
        echo "3. Redirect back to application\n";
        echo "4. Session created, user logged in\n";
    } catch (\Exception $e) {
        echo "Cannot generate test URL\n";
    }
}

echo "\n";
