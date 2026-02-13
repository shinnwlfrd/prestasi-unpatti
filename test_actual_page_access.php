<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "=== TEST AKSES HALAMAN ADMIN UNIVERSITY ===\n\n";

// 1. Cari user admin
$admin = User::where('role', 'Admin')->first();

if (!$admin) {
    echo "✗ Tidak ada user dengan role Admin!\n";
    echo "Mencari user dengan role lain...\n";
    
    // Cek semua user
    $users = User::all();
    echo "\nDaftar semua user:\n";
    foreach ($users as $user) {
        echo "  - ID: {$user->id}, Name: {$user->name}, Role: {$user->role}, Email: {$user->email}\n";
    }
    exit;
}

echo "✓ User admin ditemukan:\n";
echo "  ID: {$admin->id}\n";
echo "  Name: {$admin->name}\n";
echo "  Email: {$admin->email}\n";
echo "  Role: {$admin->role}\n\n";

// 2. Simulasi login
Auth::login($admin);
echo "✓ Simulasi login berhasil\n\n";

// 3. Buat request simulasi
$request = \Illuminate\Http\Request::create('/admin/university/pending', 'GET');
$request->setUserResolver(function () use ($admin) {
    return $admin;
});

echo "3. SIMULASI REQUEST KE CONTROLLER\n";
echo "   ================================\n";

try {
    // Panggil controller
    $controller = app(\App\Http\Controllers\Admin\UniversityValidationController::class);
    
    // Gunakan reflection untuk memanggil method index
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('index');
    $method->setAccessible(true);
    
    // Panggil method
    $response = $method->invoke($controller, $request);
    
    echo "   ✓ Controller method berhasil dipanggil\n";
    echo "   Response type: " . get_class($response) . "\n";
    
    // Cek apakah response adalah view
    if ($response instanceof \Illuminate\View\View) {
        echo "   ✓ Response adalah View\n";
        
        $data = $response->getData();
        echo "\n   Data yang dikirim ke view:\n";
        echo "   - achievements: " . (isset($data['achievements']) ? get_class($data['achievements']) : 'NOT SET') . "\n";
        
        if (isset($data['achievements'])) {
            $achievements = $data['achievements'];
            if (method_exists($achievements, 'total')) {
                echo "     Total: {$achievements->total()}\n";
                echo "     Per Page: {$achievements->perPage()}\n";
                echo "     Current Page: {$achievements->currentPage()}\n";
                echo "     Items in current page: {$achievements->count()}\n";
            }
        }
        
        echo "   - categories: " . (isset($data['categories']) ? $data['categories']->count() . ' items' : 'NOT SET') . "\n";
        echo "   - faculties: " . (isset($data['faculties']) ? $data['faculties']->count() . ' items' : 'NOT SET') . "\n";
        echo "   - statistics: " . (isset($data['statistics']) ? 'SET' : 'NOT SET') . "\n";
        
        if (isset($data['statistics'])) {
            $stats = $data['statistics'];
            echo "     Pending: " . ($stats['pending'] ?? 'N/A') . "\n";
            echo "     Approved This Month: " . ($stats['approved_this_month'] ?? 'N/A') . "\n";
        }
        
        echo "   - filters: " . (isset($data['filters']) ? 'SET' : 'NOT SET') . "\n";
        
        if (isset($data['filters'])) {
            $filters = $data['filters'];
            echo "     Search: " . ($filters['search'] ?? 'null') . "\n";
            echo "     Faculty: " . ($filters['faculty'] ?? 'null') . "\n";
            echo "     Level: " . ($filters['level'] ?? 'null') . "\n";
            echo "     Category: " . ($filters['category'] ?? 'null') . "\n";
        }
        
    } else if ($response instanceof \Illuminate\Http\Response) {
        echo "   Response adalah HTTP Response\n";
        echo "   Status: " . $response->getStatusCode() . "\n";
    } else if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "   Response adalah Redirect\n";
        echo "   Target: " . $response->getTargetUrl() . "\n";
    }
    
} catch (\Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
    echo "\n   Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== KESIMPULAN ===\n";
echo "\nJika controller mengembalikan data dengan benar:\n";
echo "→ Masalah ada di BROWSER (cache, JavaScript, atau rendering)\n";
echo "→ Solusi: Clear browser cache, hard refresh (Ctrl+Shift+R)\n\n";

echo "Jika ada error:\n";
echo "→ Masalah ada di CONTROLLER atau MIDDLEWARE\n";
echo "→ Cek error message di atas\n";
