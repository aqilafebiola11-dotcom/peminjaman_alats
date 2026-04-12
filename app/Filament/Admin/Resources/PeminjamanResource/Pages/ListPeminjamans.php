<?php

namespace App\Filament\Admin\Resources\PeminjamanResource\Pages;

use App\Filament\Admin\Resources\PeminjamanResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListPeminjamans extends ListRecords
{
    protected static string $resource = PeminjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('cetakPdfPeminjaman')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->visible(function (): bool {
                    /** @var User|null $user */
                    $user = Auth::user();

                    return $user?->isStaff() ?? false;
                })
                ->form($this->getDateRangeForm())
                ->modalHeading('Cetak Laporan Peminjaman')
                ->modalDescription('Filter berdasarkan tanggal pinjam (opsional).')
                ->action(function (array $data): void {
                    $url = $this->buildReportUrl('peminjaman', $data);

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
}
