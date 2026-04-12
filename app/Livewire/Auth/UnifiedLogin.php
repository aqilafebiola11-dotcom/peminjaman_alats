<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class UnifiedLogin extends Component
{
    public ?array $data = [];

    public function mount()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
    }

    public function authenticate()
    {
        $credentials = [
            'email' => $this->data['email'] ?? '',
            'password' => $this->data['password'] ?? '',
        ];

        if (! Auth::attempt($credentials, $this->data['remember'] ?? false)) {
            throw ValidationException::withMessages([
                'data.email' => 'Email atau password salah.',
            ]);
        }

        session()->regenerate();

        return $this->redirectBasedOnRole(Auth::user());
    }

    protected function redirectBasedOnRole($user)
    {
        $role = $user->role ?? 'peminjam';
        return redirect()->to('/' . strtolower($role) . '/dashboard');
    }

    public function render()
    {
        return view('filament.pages.auth.login');
    }
}
