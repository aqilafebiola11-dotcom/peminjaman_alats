<?php

use App\Http\Controllers\ReportController;
use App\Livewire\Auth\UnifiedLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', UnifiedLogin::class)->name('login');

Route::post('/login', function () {
    $credentials = [
        'email' => request()->input('email'),
        'password' => request()->input('password'),
    ];

    if (! Auth::attempt($credentials, request()->boolean('remember'))) {
        throw ValidationException::withMessages([
            'email' => 'Email atau password salah.',
        ]);
    }

    request()->session()->regenerate();

    $user = Auth::user();
    $role = $user->role ?? 'peminjam';
    return redirect()->to('/' . strtolower($role) . '/dashboard');
})->name('login.submit');

Route::middleware(['auth'])->group(function () {
    Route::get('/laporan/pdf/{type}', [ReportController::class, 'download'])
        ->name('reports.pdf');
    Route::get('/laporan/preview/{type}', [ReportController::class, 'stream'])
        ->name('reports.preview');
});
