<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

echo "=== COMPREHENSIVE LOGIN TEST ===\n\n";

// 1. Check if user exists
echo "1. Checking if user 'admin@peminjaman.local' exists...\n";
$user = User::where('email', 'admin@peminjaman.local')->first();
if ($user) {
    echo "   ✓ User found: {$user->nama} (ID: {$user->id_user})\n";
} else {
    echo "   ✗ User NOT found\n";
}

// 2. Test Auth::attempt()
echo "\n2. Testing Auth::attempt() with email & password...\n";
$attempt = Auth::attempt(['email' => 'admin@peminjaman.local', 'password' => 'password']);
echo "   Result: " . ($attempt ? "✓ SUCCESS" : "✗ FAILED") . "\n";

if ($attempt) {
    echo "   Logged in user: " . Auth::user()->nama . "\n";
    echo "   User role: " . Auth::user()->user_role . "\n";
    
    // 3. Test redirect calculation
    echo "\n3. Testing redirect logic...\n";
    $role = Auth::user()->user_role ?? Auth::user()->role ?? 'peminjam';
    $redirectPath = '/' . strtolower($role) . '/dashboard';
    echo "   Redirect path: " . $redirectPath . "\n";
    
    // 4. Check route exists
    echo "\n4. Checking if route exists...\n";
    $routeExists = \Illuminate\Support\Facades\Route::has(strtolower($role) . '.dashboard');
    echo "   Route exists: " . ($routeExists ? "✓ YES" : "✗ NO") . "\n";
    
    Auth::logout();
} else {
    echo "   Checking why authentication failed...\n";
    echo "   - User exists: " . ($user ? "YES" : "NO") . "\n";
    if ($user) {
        $passwordCorrect = \Illuminate\Support\Facades\Hash::check('password', $user->password);
        echo "   - Password correct: " . ($passwordCorrect ? "YES" : "NO") . "\n";
    }
}
