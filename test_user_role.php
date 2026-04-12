<?php
require 'vendor/autoload.php';
$app = require_once('bootstrap/app.php');
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = DB::table('users')->get();
echo "Users in database:\n";
foreach($users as $user) {
    echo "ID: {$user->id_user}, Email: {$user->email}, Role: {$user->user_role}\n";
}

// Test auth
echo "\n\nTesting Auth::attempt:\n";
$result = Auth::attempt(['email' => 'admin@peminjaman.local', 'password' => 'password']);
if ($result) {
    $user = Auth::user();
    echo "✓ Auth succeeded for: {$user->email}\n";
    echo "  Role: {$user->user_role}\n";
    echo "  User ID: {$user->id_user}\n";
    
    // Test redirect path
    $role = $user->user_role ?? $user->role ?? 'peminjam';
    $path = '/' . strtolower($role) . '/dashboard';
    echo "  Redirect path: {$path}\n";
} else {
    echo "✗ Auth failed\n";
}
