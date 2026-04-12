<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PeminjamanResource\Pages;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\User;
use BackedEnum;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use UnitEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\TextSize;

class PeminjamanResource extends Resource
{
    protected static ?string $model = Peminjaman::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Peminjaman';

    protected static ?string $pluralModelLabel = 'Peminjaman';

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'peminjamans';
    }



    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /** @var User|null $user */
        $user = Auth::user();
        
        if ($user?->isPeminjam()) {
            $query->where('id_user', $user->id_user);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Peminjaman')
                    ->description('Detail peminjam dan waktu peminjaman.')
                    ->schema([
                        Select::make('id_user')
                            ->label('Peminjam')
                            ->options(function () {
                                return User::where('user_role', 'peminjam')
                                    ->orderBy('email')
                                    ->get(['id_user', 'email'])
                                    ->mapWithKeys(function (User $peminjam) {
                                        $email = $peminjam->email ?? ('user-' . $peminjam->id_user);
                                        $label = explode('@', $email)[0];

                                        return [$peminjam->id_user => $label];
                                    })
                                    ->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->default(function() {
                                /** @var User|null $user */
                                $user = Auth::user();
                                return $user?->isPeminjam() ? $user->id_user : null;
                            })
                            ->disabled(function() {
                                /** @var User|null $user */
                                $user = Auth::user();
                                return $user?->isPeminjam() ?? false;
                            })
                            ->helperText('Gunakan kolom pencarian untuk cari akun peminjam.')
                            ->prefixIcon('heroicon-o-user'),
                        DatePicker::make('tanggal_pinjam')
                            ->label('Tanggal Pinjam')
                            ->required()
                            ->default(now()->toDateString())
                            ->minDate(fn (?Peminjaman $record) => $record?->tanggal_pinjam ?? now()->toDateString())
                            ->maxDate(fn (?Peminjaman $record) => $record?->tanggal_pinjam ?? now()->toDateString())
                            ->helperText('Tanggal pinjam otomatis hari ini dan tidak bisa dimundurkan.')
                            ->prefixIcon('heroicon-o-calendar'),
                        DatePicker::make('tanggal_kembali')
                            ->label('Tanggal Kembali (Rencana)')
                            ->required()
                            ->after('tanggal_pinjam')
                            ->rule('after:today')
                            ->minDate(fn (?Peminjaman $record) => $record?->tanggal_kembali ?? now()->addDay())
                            ->helperText('Tanggal kembali rencana harus lebih dari hari ini.')
                            ->prefixIcon('heroicon-o-calendar-days'),
                        Select::make('status')
                            ->options([
                                'menunggu' => 'Menunggu',
                                'disetujui' => 'Disetujui',
                                'ditolak' => 'Ditolak',
                                'dikembalikan' => 'Dikembalikan',
                            ])
                            ->default('menunggu')
                            ->disabled()
                            ->dehydrated()
                            ->prefixIcon('heroicon-o-information-circle'),
                    ])->columns([
                        'default' => 1,
                        'xl' => 2,
                    ]),

                Section::make('Detail Alat yang Dipinjam')
                    ->description('Daftar alat yang akan dipinjam.')
                    ->schema([
                        Repeater::make('detailPeminjaman')
                            ->relationship()
                            ->schema([
                                Select::make('id_alat')
                                    ->label('Alat')
                                    ->options(
                                        Alat::where('status', 'tersedia')
                                            ->where('stok', '>', 0)
                                            ->get()
                                            ->mapWithKeys(fn($alat) => [
                                                $alat->id_alat => "{$alat->nama_alat} (Stok: {$alat->stok})"
                                            ])
                                    )
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->distinct()
                                    ->columnSpan(2)
                                    ->prefixIcon('heroicon-o-wrench'),
                                TextInput::make('jumlah_pinjam')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(fn(Get $get) => Alat::find($get('id_alat'))?->stok ?? 1)
                                    ->columnSpan(1)
                                    ->prefixIcon('heroicon-o-calculator'),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Alat')
                            ->reorderable(false)
                            ->required()
                            ->minItems(1),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Status Peminjaman')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('status')
                                    ->label('Status Terkini')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'menunggu' => 'warning',
                                        'disetujui' => 'success',
                                        'ditolak' => 'danger',
                                        'dikembalikan' => 'info',
                                        default => 'gray',
                                    })
                                    ->size(TextSize::Large),
                                TextEntry::make('user.email')
                                    ->label('Peminjam')
                                    ->icon('heroicon-o-user'),
                                TextEntry::make('tanggal_pinjam')
                                    ->label('Tgl Pinjam')
                                    ->date('d F Y')
                                    ->icon('heroicon-o-calendar'),
                                TextEntry::make('tanggal_kembali')
                                    ->label('Rencana Kembali')
                                    ->date('d F Y')
                                    ->icon('heroicon-o-calendar-days'),
                            ]),
                    ]),

                Section::make('Daftar Alat Dipinjam')
                    ->schema([
                        RepeatableEntry::make('detailPeminjaman')
                            ->label('')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('alat.nama_alat')
                                            ->label('Alat')
                                            ->weight('bold')
                                            ->icon('heroicon-o-wrench'),
                                        TextEntry::make('jumlah_pinjam')
                                            ->label('Jumlah')
                                            ->badge(),
                                        TextEntry::make('alat.kondisi')
                                            ->label('Kondisi Alat'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_peminjaman')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Peminjam')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_pinjam')
                    ->label('Tgl Pinjam')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_kembali')
                    ->label('Tgl Kembali (Rencana)')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'menunggu' => 'warning',
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        'dikembalikan' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('estimasi_denda')
                    ->label('Estimasi Denda')
                    ->getStateUsing(function (Peminjaman $record) {
                        if (!$record->isApproved()) return '—';
                        $hariTerlambat = $record->hari_terlambat;
                        if ($hariTerlambat <= 0) return '—';
                        return 'Rp ' . number_format($record->estimasi_denda, 0, ',', '.') . " ($hariTerlambat hari)";
                    })
                    ->badge()
                    ->color(fn ($state) => $state === '—' ? 'gray' : 'danger')
                    ->visible(function() {
                        /** @var User|null $user */
                        $user = Auth::user();
                        return ($user?->isStaff() ?? false) || ($user?->isPeminjam() ?? false);
                    }),
                Tables\Columns\TextColumn::make('approver.email')
                    ->label('Disetujui Oleh')
                    ->default('-'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'menunggu' => 'Menunggu',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        'dikembalikan' => 'Dikembalikan',
                    ]),
                Tables\Filters\SelectFilter::make('id_user')
                    ->label('Peminjam')
                    ->options(User::where('user_role', 'peminjam')->pluck('email', 'id_user')),
                Tables\Filters\Filter::make('tanggal_kembali')
                    ->form([
                        DatePicker::make('from')
                            ->label('Rencana Kembali (Dari)'),
                        DatePicker::make('until')
                            ->label('Rencana Kembali (Sampai)'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_kembali', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_kembali', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn(Peminjaman $record): bool => $record->isPending()),


                Action::make('setujui')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Peminjaman')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui peminjaman ini? Stok alat akan berkurang secara otomatis.')
                    ->visible(function(Peminjaman $record): bool {
                        /** @var User|null $user */
                        $user = Auth::user();
                        return $record->isPending() && ($user?->isStaff() ?? false);
                    })
                    ->action(function (Peminjaman $record) {

                        foreach ($record->detailPeminjaman as $detail) {
                            if ($detail->alat->stok < $detail->jumlah_pinjam) {
                                Notification::make()
                                    ->title('Stok tidak mencukupi')
                                    ->body("Stok {$detail->alat->nama_alat} tidak mencukupi (tersedia: {$detail->alat->stok}, diminta: {$detail->jumlah_pinjam})")
                                    ->danger()
                                    ->send();
                                return;
                            }
                        }

                        $record->approve(Auth::id());

                        Notification::make()
                            ->title('Peminjaman Disetujui')
                            ->body('Peminjaman berhasil disetujui dan stok telah dikurangi.')
                            ->success()
                            ->send();
                    }),


                Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Peminjaman')
                    ->modalDescription('Apakah Anda yakin ingin menolak peminjaman ini?')
                    ->visible(function(Peminjaman $record): bool {
                        /** @var User|null $user */
                        $user = Auth::user();
                        return $record->isPending() && ($user?->isStaff() ?? false);
                    })
                    ->action(function (Peminjaman $record) {
                        $record->reject();

                        Notification::make()
                            ->title('Peminjaman Ditolak')
                            ->body('Peminjaman berhasil ditolak.')
                            ->warning()
                            ->send();
                    }),


                Action::make('pendingkan')
                    ->label('Set Pending')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Set Status Menjadi Pending')
                    ->modalDescription('Apakah Anda yakin ingin mengubah status peminjaman ini menjadi pending/menunggu?')
                    ->visible(function (Peminjaman $record): bool {
                        /** @var User|null $user */
                        $user = Auth::user();

                        return $record->isRejected() && ($user?->isStaff() ?? false);
                    })
                    ->action(function (Peminjaman $record) {
                        $record->setPending();

                        Notification::make()
                            ->title('Status Berhasil Diubah')
                            ->body('Peminjaman berhasil diubah ke status pending.')
                            ->success()
                            ->send();
                    }),


                Action::make('kembalikan')
                    ->label('Kembalikan')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('info')
                    ->visible(function (Peminjaman $record): bool {
                        /** @var User|null $user */
                        $user = Auth::user();

                        if (! $record->isApproved() || ! $user) {
                            return false;
                        }

                        if ($user->isStaff()) {
                            return true;
                        }

                        return $user->isPeminjam() && (int) $record->id_user === (int) $user->id_user;
                    })
                    ->form([
                        DatePicker::make('tanggal_kembali')
                            ->label('Tanggal Kembali')
                            ->required()
                            ->default(now()->toDateString())
                            ->maxDate(now())
                            ->disabled(function (): bool {
                                /** @var User|null $user */
                                $user = Auth::user();

                                return $user?->isPeminjam() ?? false;
                            }),
                        TextInput::make('kondisi_kembali')
                            ->label('Kondisi Alat saat Dikembalikan')
                            ->required(function (): bool {
                                /** @var User|null $user */
                                $user = Auth::user();

                                return $user?->isStaff() ?? false;
                            })
                            ->visible(function (): bool {
                                /** @var User|null $user */
                                $user = Auth::user();

                                return $user?->isStaff() ?? false;
                            })
                            ->default('Baik'),
                        Placeholder::make('info_denda')
                            ->label('Informasi Denda')
                            ->content(function(Peminjaman $record, Get $get) {
                                $tglKembali = $get('tanggal_kembali') ?? now()->format('Y-m-d');
                                $plannedDate = $record->tanggal_kembali;
                                $daysLate = \Carbon\Carbon::parse($tglKembali)->diffInDays($plannedDate, false);
                                
                                $pesan = "Rencana kembali: " . \Carbon\Carbon::parse($plannedDate)->format('d M Y') . ". ";
                                
                                if ($daysLate < 0) {
                                    $denda = abs($daysLate) * 5000;
                                    return $pesan . "Terlambat " . abs($daysLate) . " hari. Denda: Rp " . number_format($denda, 0, ',', '.');
                                }
                                
                                return $pesan . "Tepat waktu (tidak ada denda).";
                            }),
                    ])
                    ->action(function (Peminjaman $record, array $data) {
                        /** @var User|null $user */
                        $user = Auth::user();

                        $tanggalKembali = $data['tanggal_kembali'] ?? now()->toDateString();
                        if ($user?->isPeminjam()) {
                            $tanggalKembali = now()->toDateString();
                        }

                        $kondisiKembali = $data['kondisi_kembali'] ?? 'Menunggu pemeriksaan petugas';

                        $pengembalian = $record->returnItems(
                            $tanggalKembali,
                            $kondisiKembali
                        );

                        if ($pengembalian) {
                            $message = $user?->isPeminjam()
                                ? 'Pengembalian berhasil dicatat. Kondisi alat akan diverifikasi petugas/admin.'
                                : 'Pengembalian berhasil dicatat.';

                            if ($pengembalian->denda > 0) {
                                $message .= " Denda: Rp " . number_format($pengembalian->denda, 0, ',', '.');
                            }

                            Notification::make()
                                ->title('Pengembalian Berhasil')
                                ->body($message)
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Pengembalian Gagal')
                            ->body('Hanya peminjaman dengan status disetujui yang dapat dikembalikan.')
                            ->danger()
                            ->send();
                    }),

                DeleteAction::make()
                    ->visible(fn(Peminjaman $record): bool => $record->isPending()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeminjamans::route('/'),
            'create' => Pages\CreatePeminjaman::route('/create'),
            'view' => Pages\ViewPeminjaman::route('/{record}'),
            'edit' => Pages\EditPeminjaman::route('/{record}/edit'),
        ];
    }
}
