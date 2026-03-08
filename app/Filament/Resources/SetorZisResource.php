<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SetorZisResource\Pages;
use App\Filament\Resources\SetorZisResource\RelationManagers;
use App\Models\District;
use App\Models\SetorZis;
use App\Models\UnitZis;
use App\Models\User;
use App\Models\Village;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class SetorZisResource extends Resource
{
    protected static ?string $model = SetorZis::class;

    protected static ?string $navigationIcon = 'heroicon-o-percent-badge';

    /**
     * Generate a signed Cloudinary URL for raw files (PDFs) or images.
     */
    private static function getCloudinaryUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        try {
            $publicId = $path;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                $parsed = parse_url($path, PHP_URL_PATH);
                if (preg_match('#/raw/upload/v\d+/(.+)$#', $parsed, $matches)) {
                    $publicId = $matches[1];
                } elseif (preg_match('#/(?:image|video|raw)/upload/(?:v\d+/)?(.+)$#', $parsed, $matches)) {
                    $publicId = $matches[1];
                }
            }
            $cloudinary = app(\Cloudinary\Cloudinary::class);
            return (string) $cloudinary->raw($publicId)->signUrl()->toUrl();
        } catch (\Exception $e) {
            return str_starts_with($path, 'http') ? $path : null;
        }
    }

    /**
     * Generate a Cloudinary URL that forces file download (fl_attachment).
     */
    private static function getCloudinaryDownloadUrl(?string $path): ?string
    {
        $url = self::getCloudinaryUrl($path);
        if (!$url) {
            return null;
        }
        $separator = str_contains($url, '?') ? '&' : '?';
        return $url . $separator . 'fl_attachment=true';
    }

    protected static ?string $navigationGroup = 'Rekap & Transaksi';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('unit_id')
                    ->label('ID UPZ')
                    ->readOnly()
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('trx_date')
                    ->label('Tanggal Transaksi')
                    ->readOnly()
                    ->required(),
                Forms\Components\TextInput::make('zf_amount_deposit')
                    ->label('Setor Zakat Fitrah (Uang)')
                    ->readOnly()
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('zf_rice_deposit')
                    ->label('Setor Zakat Fitrah (Beras)')
                    ->readOnly()
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('zf_rice_sold_amount')
                    ->label('Konversi Beras ke Uang (Rp)')
                    ->readOnly()
                    ->numeric(),
                Forms\Components\TextInput::make('zf_rice_sold_price')
                    ->label('Harga Beras per Kg (Rp)')
                    ->readOnly()
                    ->numeric(),
                Forms\Components\TextInput::make('zm_amount_deposit')
                    ->label('Setor Zakat Mal (Uang)')
                    ->readOnly()
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('ifs_amount_deposit')
                    ->label('Setor Infaq Sedekah (Uang)')
                    ->readOnly()
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('total_deposit')
                    ->label('Total Setor')
                    ->readOnly()
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('deposit_destination')
                    ->label('Tujuan Setoran')
                    ->readOnly(),
                Forms\Components\TextInput::make('status')
                    ->label('Status')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('validation')
                    ->label('Validasi'),
                Forms\Components\Placeholder::make('current_upload')
                    ->label('Gambar Bukti Setor Saat Ini')
                    ->content(function ($record) {
                        if (!$record || !$record->upload) {
                            return 'Belum ada gambar';
                        }
                        $url = \Illuminate\Support\Facades\Storage::disk('cloudinary')->url($record->upload);
                        return new \Illuminate\Support\HtmlString(
                            '<img src="' . e($url) . '" style="max-width: 400px; max-height: 300px; border-radius: 8px; object-fit: contain;" />'
                        );
                    })
                    ->visible(fn ($record) => $record !== null),
                Forms\Components\FileUpload::make('upload')
                    ->label('Upload Bukti Setor Baru')
                    ->disk('cloudinary')
                    ->directory('sisfo/bukti_setor')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->maxSize(5120)
                    ->openable()
                    ->downloadable()
                    ->saveUploadedFileUsing(function (\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file, Forms\Get $get): string {
                        $unitId = $get('unit_id');
                        $unit = $unitId ? \App\Models\UnitZis::find($unitId) : null;
                        $noRegister = $unit ? $unit->no_register : 'admin';
                        $namaUnit = $unit ? \Illuminate\Support\Str::slug($unit->unit_name) : 'admin';
                        $tanggal = date('Ymd');
                        $timestamp = time();
                        $extension = $file->getClientOriginalExtension();
                        $customName = "setor_{$tanggal}_{$noRegister}_{$namaUnit}_{$timestamp}";
                        $folder = 'sisfo/bukti_setor';

                        $cloudinary = app(\Cloudinary\Cloudinary::class);
                        $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                            'folder' => $folder,
                            'public_id' => $customName,
                            'resource_type' => 'auto',
                        ]);

                        return $result['public_id'] . '.' . ($result['format'] ?? $extension);
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->recordAction('view')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unit.unit_name')
                    ->label('Nama UPZ')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('district.name')
                    ->label('Kecamatan')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('village.name')
                    ->label('Desa')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('trx_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('zf_amount_deposit')
                    ->label('Setor ZF (Uang)')
                    ->numeric()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Setor Fitrah (Uang)')),
                Tables\Columns\TextColumn::make('zf_rice_deposit')
                    ->label('Setor ZF (Beras)')
                    ->numeric()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Setor Fitrah (Beras)')),
                Tables\Columns\TextColumn::make('zf_rice_sold_amount')
                    ->label('Konversi Beras (Rp)')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Konversi')),
                Tables\Columns\TextColumn::make('zf_rice_sold_price')
                    ->label('Harga/Kg')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->summarize(Tables\Columns\Summarizers\Average::make()->label('Rata-rata Harga/Kg')),
                Tables\Columns\TextColumn::make('zm_amount_deposit')
                    ->label('Setor ZM (Uang)')
                    ->numeric()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Setor Mal')),
                Tables\Columns\TextColumn::make('ifs_amount_deposit')
                    ->label('Setor IFS (Uang)')
                    ->numeric()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Setor Infak')),
                Tables\Columns\TextColumn::make('total_deposit')
                    ->label('Total Setor')
                    ->numeric()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Setor')),
                Tables\Columns\TextColumn::make('deposit_destination')
                    ->label('Tujuan')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
         'upz_desa' => 'success',
         'upz_kecamatan' => 'info',
         default => 'gray',
     })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'upz_desa' => 'UPZ Desa',
                        'upz_kecamatan' => 'UPZ Kecamatan',
                        default => '-',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Jenis Setor')
                    ->searchable(),
                Tables\Columns\SelectColumn::make('validation')
                    ->label('Validasi')
                    ->options([
                        'Valid' => 'Valid',
                        'Tidak Valid' => 'Tidak Valid',
                    ])
                    ->selectablePlaceholder(false)
                    ->disabled(fn () => !User::currentIsSuperAdmin() && !User::currentIsAdmin())
                    ->searchable(),
                Tables\Columns\ImageColumn::make('upload')
                    ->label('Bukti Setor')
                    ->disk('cloudinary')
                    ->searchable(),
                Tables\Columns\IconColumn::make('zf_rice_sold_proof')
                    ->label('BA Penjualan')
                    ->icon(fn (?string $state): string => $state ? 'heroicon-o-document-check' : 'heroicon-o-minus')
                    ->color(fn (?string $state): string => $state ? 'success' : 'gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('trx_year')
                    ->label('Tahun')
                    ->options(function () {
                        $currentYear = now()->year;
                        $years = [];
                        for ($year = $currentYear; $year >= 2020; $year--) {
                            $years[$year] = (string) $year;
                        }
                        return $years;
                    })
                    ->default(now()->year)
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when($data['value'] ?? null, fn(Builder $q, $year) => $q->whereYear('trx_date', $year))
                    ),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(fn() => SetorZis::distinct()->pluck('status', 'status')->filter()),
                SelectFilter::make('validation')
                    ->label('Validasi')
                    ->options(fn() => SetorZis::distinct()->pluck('validation', 'validation')->filter()),
                SelectFilter::make('deposit_destination')
                    ->label('Tujuan Setoran')
                    ->options([
                        'upz_desa' => 'UPZ Desa',
                        'upz_kecamatan' => 'UPZ Kecamatan',
                    ]),
                SelectFilter::make('rice_status')
                    ->label('Status Beras')
                    ->options([
                        'unsold' => 'Belum Terjual',
                        'sold' => 'Sudah Terjual',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'] ?? null, function (Builder $q, $status) {
                            if ($status === 'unsold') {
                                $q->where('zf_rice_deposit', '>', 0)->where('zf_rice_sold_amount', 0);
                            } elseif ($status === 'sold') {
                                $q->where('zf_rice_sold_amount', '>', 0);
                            }
                        });
                    }),
                SelectFilter::make('district')
                    ->label('Kecamatan')
                    ->options(fn() => District::pluck('name', 'id'))
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when(
                            $data['value'] ?? null,
                            fn(Builder $q, $districtId) =>
                            $q->whereHas('unit', fn($q) => $q->where('district_id', $districtId))
                        )
                    )
                    ->visible(fn() => User::currentIsSuperAdmin() || User::currentIsAdmin()),
                SelectFilter::make('village')
                    ->label('Desa')
                    ->options(function () {
                        $user = User::current();
                        if ($user && $user->isUpzKecamatan() && $user->district_id) {
                            return Village::where('district_id', $user->district_id)->pluck('name', 'id');
                        }
                        return Village::pluck('name', 'id');
                    })
                    ->searchable()
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when(
                            $data['value'] ?? null,
                            fn(Builder $q, $villageId) =>
                            $q->whereHas('unit', fn($q) => $q->where('village_id', $villageId))
                        )
                    )
                    ->visible(fn() => User::currentIsSuperAdmin() || User::currentIsAdmin() || User::currentIsUpzKecamatan()),
                SelectFilter::make('unit_id')
                    ->label('Unit UPZ')
                    ->options(function () {
                        $user = User::current();
                        if ($user && $user->isUpzKecamatan() && $user->district_id) {
                            return UnitZis::where('district_id', $user->district_id)->pluck('unit_name', 'id');
                        }
                        return UnitZis::pluck('unit_name', 'id');
                    })
                    ->searchable()
                    ->visible(fn() => !User::currentIsUpzDesa()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->infolist([
                        Infolists\Components\TextEntry::make('unit.unit_name')
                            ->label('Nama UPZ'),
                        Infolists\Components\TextEntry::make('trx_date')
                            ->label('Tanggal Transaksi')
                            ->date(),
                        Infolists\Components\TextEntry::make('zf_amount_deposit')
                            ->label('Setor Zakat Fitrah (Uang)')
                            ->numeric(),
                        Infolists\Components\TextEntry::make('zf_rice_deposit')
                            ->label('Setor Zakat Fitrah (Beras)')
                            ->numeric(),
                        Infolists\Components\TextEntry::make('zf_rice_sold_amount')
                            ->label('Konversi Beras ke Uang (Rp)')
                            ->numeric()
                            ->visible(fn ($record) => $record->zf_rice_sold_amount > 0),
                        Infolists\Components\TextEntry::make('zf_rice_sold_price')
                            ->label('Harga Beras per Kg (Rp)')
                            ->numeric()
                            ->visible(fn ($record) => $record->zf_rice_sold_price > 0),
                        Infolists\Components\TextEntry::make('zm_amount_deposit')
                            ->label('Setor Zakat Mal (Uang)')
                            ->numeric(),
                        Infolists\Components\TextEntry::make('ifs_amount_deposit')
                            ->label('Setor Infaq Sedekah (Uang)')
                            ->numeric(),
                        Infolists\Components\TextEntry::make('total_deposit')
                            ->label('Total Setor')
                            ->numeric(),
                        Infolists\Components\TextEntry::make('deposit_destination')
                            ->label('Tujuan Setoran')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'upz_desa' => 'UPZ Desa',
                                'upz_kecamatan' => 'UPZ Kecamatan',
                                default => '-',
                            }),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge(),
                        Infolists\Components\TextEntry::make('validation')
                            ->label('Validasi'),
                        Infolists\Components\TextEntry::make('upload')
                            ->label('Bukti Setor')
                            ->formatStateUsing(fn ($state) => $state ? 'Download Bukti Setor' : '-')
                            ->url(fn ($state) => self::getCloudinaryDownloadUrl($state), shouldOpenInNewTab: true)
                            ->color('primary')
                            ->icon(fn ($state) => $state ? 'heroicon-m-arrow-down-tray' : null),
                        Infolists\Components\TextEntry::make('zf_rice_sold_proof')
                            ->label('Berita Acara Penjualan Beras')
                            ->formatStateUsing(fn ($state) => $state ? 'Download Berita Acara' : '-')
                            ->url(fn ($state) => self::getCloudinaryDownloadUrl($state), shouldOpenInNewTab: true)
                            ->color('primary')
                            ->icon(fn ($state) => $state ? 'heroicon-m-arrow-down-tray' : null)
                            ->visible(fn ($record) => !empty($record->zf_rice_sold_proof)),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    // =========================================================
                    // BulkAction: Jual Beras
                    // Desa/Kecamatan menjual beras dari unit-unit yang menyetor
                    // =========================================================
                    Tables\Actions\BulkAction::make('jual_beras')
                        ->label('Jual Beras')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('warning')
                        ->visible(fn () => User::currentIsSuperAdmin() || User::currentIsAdmin() || User::currentIsUpzKecamatan() || User::currentIsUpzDesa())
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->modalHeading('Jual Beras')
                        ->modalDescription(fn (Collection $records) => 'Anda akan menjual beras dari ' . $records->count() . ' record. Total beras: ' . number_format($records->sum('zf_rice_deposit'), 2, ',', '.') . ' Kg')
                        ->form([
                            Forms\Components\TextInput::make('harga_per_kg')
                                ->label('Harga Beras per Kg (Rp)')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->prefix('Rp')
                                ->helperText('Masukkan harga jual beras per kilogram'),
                            Forms\Components\FileUpload::make('berita_acara')
                                ->label('Berita Acara Penjualan')
                                ->required()
                                ->disk('cloudinary')
                                ->directory('sisfo/bap')
                                ->visibility('public')
                                ->acceptedFileTypes(['image/*', 'application/pdf'])
                                ->maxSize(5120)
                                ->helperText('Upload dokumen berita acara penjualan beras')
                                ->saveUploadedFileUsing(function (\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file): string {
                                    $user = \Illuminate\Support\Facades\Auth::user();
                                    $unit = $user ? $user->unitZis : null;
                                    $noRegister = $unit ? $unit->no_register : 'admin';
                                    $namaUnit = $unit ? \Illuminate\Support\Str::slug($unit->unit_name) : 'admin';
                                    $tanggal = date('Ymd');
                                    $timestamp = time();
                                    $extension = $file->getClientOriginalExtension();
                                    $customName = "bap_{$tanggal}_{$noRegister}_{$namaUnit}_{$timestamp}";
                                    $folder = 'sisfo/bap';

                                    $cloudinary = app(\Cloudinary\Cloudinary::class);
                                    $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                                        'folder' => $folder,
                                        'public_id' => $customName,
                                        'resource_type' => 'auto',
                                    ]);

                                    return $result['public_id'] . '.' . ($result['format'] ?? $extension);
                                }),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $hargaPerKg = (int) $data['harga_per_kg'];
                            $beritaAcaraUrl = $data['berita_acara'];

                            // Filter hanya record yang berasnya belum terjual
                            $eligibleRecords = $records->filter(fn ($record) => $record->zf_rice_deposit > 0 && $record->zf_rice_sold_amount == 0);

                            if ($eligibleRecords->isEmpty()) {
                                Notification::make()
                                    ->warning()
                                    ->title('Tidak ada beras yang bisa dijual')
                                    ->body('Semua record yang dipilih sudah terjual atau tidak memiliki beras.')
                                    ->send();
                                return;
                            }

                            $totalKg = $eligibleRecords->sum('zf_rice_deposit');
                            $totalRp = (int) round($totalKg * $hargaPerKg);

                            DB::transaction(function () use ($eligibleRecords, $hargaPerKg, $beritaAcaraUrl) {
                                foreach ($eligibleRecords as $record) {
                                    $soldAmount = (int) round($record->zf_rice_deposit * $hargaPerKg);

                                    $record->update([
                                        'zf_rice_sold_amount' => $soldAmount,
                                        'zf_rice_sold_price' => $hargaPerKg,
                                        'zf_rice_sold_proof' => $beritaAcaraUrl,
                                        'zf_rice_deposit' => 0,
                                        'total_deposit' => $record->total_deposit + $soldAmount,
                                    ]);
                                }
                            });

                            Notification::make()
                                ->success()
                                ->title('Beras berhasil dijual')
                                ->body(
                                    $eligibleRecords->count() . ' record diproses. ' .
                                    'Total: ' . number_format($totalKg, 2, ',', '.') . ' Kg = Rp ' . number_format($totalRp, 0, ',', '.')
                                )
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSetorZis::route('/'),
            'create' => Pages\CreateSetorZis::route('/create'),

        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
