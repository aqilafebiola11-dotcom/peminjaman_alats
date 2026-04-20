<?php

namespace App\Filament\Admin\Pages;

use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class LaporanPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Laporan';

    protected static ?string $title = 'Laporan';

    protected static ?string $slug = 'laporan';

    protected static ?int $navigationSort = 10;

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user && $user->isStaff();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetakLaporanAlat')
                ->label('Cetak Laporan Alat')
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
                ->label('Cetak Laporan Peminjaman')
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
                ->label('Cetak Laporan Pengembalian')
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

    protected function getDateRangeForm(): array
    {
        return [
            DatePicker::make('from')
                ->label('Dari Tanggal')
                ->native(false)
                ->displayFormat('d/m/Y'),
            DatePicker::make('until')
                ->label('Sampai Tanggal')
                ->native(false)
                ->displayFormat('d/m/Y')
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
}
