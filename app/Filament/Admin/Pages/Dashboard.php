<?php

namespace App\Filament\Admin\Pages;

use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
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

        $role = $user->user_role ?? $user->role;

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

        $role = $user->user_role ?? $user->role;

        return match ($role) {
            'admin' => 'Anda memiliki akses penuh ke semua fitur sistem.',
            'petugas' => 'Anda dapat mengelola peminjaman dan pengembalian alat.',
            'peminjam' => 'Anda dapat melihat dan mengajukan peminjaman alat.',
            default => null,
        };
    }

    protected function getHeaderActions(): array
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $user->isStaff()) {
            return [];
        }

        return [
            Action::make('cetakLaporanAlat')
                ->label('Cetak PDF Alat')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('primary')
                ->form($this->getDateRangeForm())
                ->modalHeading('Cetak Laporan Alat')
                ->modalDescription('Pilih periode tanggal (opsional). Kosongkan untuk mencetak semua data.')
                ->action(function (array $data): void {
                    $url = $this->buildReportUrl('alat', $data);

                    $this->js("window.open('{$url}', '_blank')");
                }),
            Action::make('cetakLaporanPeminjaman')
                ->label('Cetak PDF Peminjaman')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('success')
                ->form($this->getDateRangeForm())
                ->modalHeading('Cetak Laporan Peminjaman')
                ->modalDescription('Filter berdasarkan tanggal pinjam (opsional).')
                ->action(function (array $data): void {
                    $url = $this->buildReportUrl('peminjaman', $data);

                    $this->js("window.open('{$url}', '_blank')");
                }),
            Action::make('cetakLaporanPengembalian')
                ->label('Cetak PDF Pengembalian')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->form($this->getDateRangeForm())
                ->modalHeading('Cetak Laporan Pengembalian')
                ->modalDescription('Filter berdasarkan tanggal kembali (opsional).')
                ->action(function (array $data): void {
                    $url = $this->buildReportUrl('pengembalian', $data);

                    $this->js("window.open('{$url}', '_blank')");
                }),
        ];
    }

    /**
     * @return array<int, \Filament\Forms\Components\DatePicker>
     */
    protected function getDateRangeForm(): array
    {
        return [
            DatePicker::make('from')
                ->label('Dari Tanggal'),
            DatePicker::make('until')
                ->label('Sampai Tanggal')
                ->rule('after_or_equal:from'),
        ];
    }

    protected function buildReportUrl(string $type, array $data): string
    {
        $params = ['type' => $type];

        if (! empty($data['from'])) {
            $params['from'] = $data['from'];
        }

        if (! empty($data['until'])) {
            $params['until'] = $data['until'];
        }

        return route('reports.pdf', $params);
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
