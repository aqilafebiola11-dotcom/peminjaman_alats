<?php
require 'vendor/autoload.php';
$app = require_once('bootstrap/app.php');
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Simulate what happens after login
Auth::attempt(['email' => 'admin@peminjaman.local', 'password' => 'password']);

if (Auth::check()) {
    $user = Auth::user();
    session()->regenerate();
    
    $role = $user->user_role ?? $user->role ?? 'peminjam';
    $path = '/' . strtolower($role) . '/dashboard';
    
    echo "Auth Check: OK\n";
    echo "User: {$user->email}\n";
    echo "Role: {$user->user_role}\n";
    echo "Redirect path: {$path}\n";
    echo "Can access panel: ";
    
    // Test canAccessPanel
    $adminPanel = new \Filament\Panel('admin');
    $adminPanel->id('admin');
    
    $canAccess = $user->canAccessPanel($adminPanel);
    echo ($canAccess ? "YES" : "NO") . "\n";
    
} else {
    echo "Auth failed\n";
}
