<?php

namespace App\Filament\Admin\Pages;

use App\Models\User;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -2;

    public function getHeading(): string
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return 'Dashboard';
        }

        $greeting = 'Selamat Datang, ' . $user->getFilamentName();

        $role = $user->role;

        return match ($role) {
            'admin' => $greeting . ' (Administrator)',
            'petugas' => $greeting . ' (Petugas)',
            'peminjam' => $greeting . ' (Peminjam)',
            default => $greeting,
        };
    }

    public function getSubheading(): ?string
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        $role = $user->role;

        return match ($role) {
            'admin' => 'Anda memiliki akses penuh ke semua fitur sistem.',
            'petugas' => 'Anda dapat mengelola peminjaman dan pengembalian alat.',
            'peminjam' => 'Anda dapat melihat dan mengajukan peminjaman alat.',
            default => null,
        };
    }

    public function getWidgets(): array
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return [
                AccountWidget::class,
            ];
        }

        $widgets = [
            AccountWidget::class,
        ];


        if ($user->isAdmin()) {
            $widgets[] = \App\Filament\Admin\Widgets\StatsOverviewWidget::class;
        }

        if ($user->isStaff()) {
            $widgets[] = \App\Filament\Admin\Widgets\PendingLoansWidget::class;
        }

        if ($user->isPeminjam()) {
            $widgets[] = \App\Filament\Admin\Widgets\MyLoansWidget::class;
        }

        return $widgets;
    }
}
