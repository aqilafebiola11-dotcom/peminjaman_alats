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

    protected string $view = 'filament.pages.laporan-page';

    // Livewire properties for preview state
    public ?string $previewUrl = null;

    public ?string $downloadUrl = null;

    public ?string $previewTitle = null;

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
                ->modalSubmitActionLabel('Tampilkan Preview')
                ->action(function (array $data): void {
                    $this->showPreview('alat', $data, 'Laporan Alat');
                }),

            Action::make('cetakLaporanPeminjaman')
                ->label('Cetak Laporan Peminjaman')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('success')
                ->form($this->getDateRangeForm())
                ->modalHeading('Cetak Laporan Peminjaman')
                ->modalDescription('Filter berdasarkan tanggal pinjam (opsional).')
                ->modalSubmitActionLabel('Tampilkan Preview')
                ->action(function (array $data): void {
                    $this->showPreview('peminjaman', $data, 'Laporan Peminjaman');
                }),

            Action::make('cetakLaporanPengembalian')
                ->label('Cetak Laporan Pengembalian')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->form($this->getDateRangeForm())
                ->modalHeading('Cetak Laporan Pengembalian')
                ->modalDescription('Filter berdasarkan tanggal kembali (opsional).')
                ->modalSubmitActionLabel('Tampilkan Preview')
                ->action(function (array $data): void {
                    $this->showPreview('pengembalian', $data, 'Laporan Pengembalian');
                }),
        ];
    }

    public function showPreview(string $type, array $data, string $title): void
    {
        $this->previewUrl = $this->buildReportUrl('reports.preview', $type, $data);
        $this->downloadUrl = $this->buildReportUrl('reports.pdf', $type, $data);
        $this->previewTitle = $title;
    }

    public function closePreview(): void
    {
        $this->previewUrl = null;
        $this->downloadUrl = null;
        $this->previewTitle = null;
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

    protected function buildReportUrl(string $routeName, string $type, array $data): string
    {
        $params = ['type' => $type];

        if (! empty($data['from'])) {
            $params['from'] = $data['from'];
        }

        if (! empty($data['until'])) {
            $params['until'] = $data['until'];
        }

        return route($routeName, $params);
    }
}
