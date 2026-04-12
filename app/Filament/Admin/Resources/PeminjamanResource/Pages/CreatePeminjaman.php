<?php

namespace App\Filament\Admin\Resources\PeminjamanResource\Pages;

use App\Filament\Admin\Resources\PeminjamanResource;
use App\Models\User;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreatePeminjaman extends CreateRecord
{
    protected static string $resource = PeminjamanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['tanggal_kembali']) && Carbon::parse($data['tanggal_kembali'])->lte(now()->startOfDay())) {
            throw ValidationException::withMessages([
                'tanggal_kembali' => 'Tanggal kembali rencana harus lebih dari hari ini.',
            ]);
        }

        /** @var User|null $user */
        $user = Auth::user();

        if ($user?->isPeminjam()) {
            $data['id_user'] = $user->id_user;
        }

        $data['tanggal_pinjam'] = now()->toDateString();

        $data['status'] = 'menunggu';

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
