<?php

namespace App\Filament\Admin\Resources\PeminjamanResource\Pages;

use App\Filament\Admin\Resources\PeminjamanResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditPeminjaman extends EditRecord
{
    protected static string $resource = PeminjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['tanggal_kembali']) && Carbon::parse($data['tanggal_kembali'])->lte(now()->startOfDay())) {
            throw ValidationException::withMessages([
                'tanggal_kembali' => 'Tanggal kembali rencana harus lebih dari hari ini.',
            ]);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
