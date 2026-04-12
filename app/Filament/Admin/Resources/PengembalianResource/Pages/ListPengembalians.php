<?php

namespace App\Filament\Admin\Resources\PengembalianResource\Pages;

use App\Filament\Admin\Resources\PengembalianResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListPengembalians extends ListRecords
{
    protected static string $resource = PengembalianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetakPdfPengembalian')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->visible(function (): bool {
                    /** @var User|null $user */
                    $user = Auth::user();

                    return $user?->isStaff() ?? false;
                })
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
}
