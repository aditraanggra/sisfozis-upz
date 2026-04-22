<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LpzResource\Pages;
use App\Models\District;
use App\Models\Lpz;
use App\Models\UnitZis;
use App\Models\Village;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LpzResource extends Resource
{
    protected static ?string $model = Lpz::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Laporan LPZ';

    protected static ?string $modelLabel = 'LPZ';

    protected static ?string $pluralModelLabel = 'Laporan LPZ';

    protected static ?string $navigationGroup = 'Rekap & Transaksi';

    protected static ?int $navigationSort = 5;

    /**
     * Generate a signed Cloudinary URL for image files.
     * Uses the Cloudinary SDK locally.
     */
    public static function getCloudinaryImageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        try {
            $publicId = $path;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                $parsed = parse_url($path, PHP_URL_PATH);
                // Match both versioned (v12345/) and signed (s--xxx--/) URL formats
                if (preg_match('#/image/upload/(?:v\d+/)?(?:s--[^/]+--/)?(.+)$#', $parsed, $matches)) {

                    $publicId = $matches[1];
                    // For images, we usually strip the extension from the public ID for Cloudinary SDK
                    $publicId = preg_replace('/\.[^.]+$/', '', $publicId);
                }
            }

            $cloudinary = app(\Cloudinary\Cloudinary::class);

            return (string) $cloudinary->image($publicId)->signUrl()->toUrl();
        } catch (\Exception $e) {
            return str_starts_with($path, 'http') ? $path : null;
        }
    }

    /**
     * Generate a signed Cloudinary URL for raw files (PDFs).
     * Uses the Cloudinary SDK locally — no HTTP API calls.
     */
    public static function getCloudinaryUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        try {
            // Extract public ID from the stored value
            $publicId = $path;

            // If stored as full URL, extract the public ID from it
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                // URL format: https://res.cloudinary.com/{cloud}/raw/upload/v{version}/{publicId}
                $parsed = parse_url($path, PHP_URL_PATH);
                // Remove /image/, /video/, or /raw/ upload prefix (with optional version v12345/ and/or signature s--xxx--/) to get the public ID
                if (preg_match('#/(?:image|video|raw)/upload/(?:v\d+/)?(?:s--[^/]+--/)?(.+)$#', $parsed, $matches)) {
                    $publicId = $matches[1];
                }
            }

            // NOTE: For raw files (PDFs), the extension IS part of the public ID.
            // Do NOT strip it, otherwise Cloudinary returns 404.

            // Use Cloudinary SDK to generate a signed URL (no HTTP calls)
            $cloudinary = app(\Cloudinary\Cloudinary::class);

            return (string) $cloudinary->raw($publicId)->signUrl()->toUrl();
        } catch (\Exception $e) {
            // Fallback: return the original path if it's already a URL
            return str_starts_with($path, 'http') ? $path : null;
        }
    }

    /**
     * Generate a Cloudinary URL that forces file download (fl_attachment).
     */
    public static function getCloudinaryDownloadUrl(?string $path): ?string
    {
        $url = self::getCloudinaryUrl($path);
        if (! $url) {
            return null;
        }

        // Append fl_attachment flag to force download
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.'fl_attachment=true';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('unit_id')
                    ->label('Unit UPZ')
                    ->options(UnitZis::pluck('unit_name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\DatePicker::make('trx_date')
                    ->label('Tanggal Laporan')
                    ->required(),
                Forms\Components\TextInput::make('lpz_year')
                    ->label('Tahun')
                    ->numeric()
                    ->required(),
                Forms\Components\FileUpload::make('form101')
                    ->label('Form 101')
                    ->disk('cloudinary')
                    ->directory('sisfo/form')
                    ->visibility('public')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240)
                    ->openable()
                    ->downloadable()
                    ->formatStateUsing(fn () => null)
                    ->dehydrated(fn ($state) => filled($state)),
                Forms\Components\FileUpload::make('form102')
                    ->label('Form 102')
                    ->disk('cloudinary')
                    ->directory('sisfo/form')
                    ->visibility('public')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240)
                    ->openable()
                    ->downloadable()
                    ->formatStateUsing(fn () => null)
                    ->dehydrated(fn ($state) => filled($state)),
                Forms\Components\FileUpload::make('lpz')
                    ->label('LPZ')
                    ->disk('cloudinary')
                    ->directory('sisfo/documents')
                    ->visibility('public')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240)
                    ->openable()
                    ->downloadable()
                    ->formatStateUsing(fn () => null)
                    ->dehydrated(fn ($state) => filled($state)),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi LPZ')
                    ->schema([
                        Infolists\Components\TextEntry::make('unit.unit_name')->label('Nama UPZ'),
                        Infolists\Components\TextEntry::make('trx_date')->label('Tanggal Laporan')->date(),
                        Infolists\Components\TextEntry::make('lpz_year')->label('Tahun'),
                        Infolists\Components\TextEntry::make('created_at')->label('Dibuat Pada')->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')->label('Diubah Pada')->dateTime(),
                    ])->columns(2),
                Infolists\Components\Section::make('Dokumen')
                    ->schema([
                        Infolists\Components\TextEntry::make('form101')
                            ->label('Form 101')
                            ->formatStateUsing(fn ($state) => $state ? 'Download Dokumen' : '-')
                            ->url(fn ($state) => self::getCloudinaryDownloadUrl($state), shouldOpenInNewTab: true),
                        Infolists\Components\TextEntry::make('form102')
                            ->label('Form 102')
                            ->formatStateUsing(fn ($state) => $state ? 'Download Dokumen' : '-')
                            ->url(fn ($state) => self::getCloudinaryDownloadUrl($state), shouldOpenInNewTab: true),
                        Infolists\Components\TextEntry::make('lpz')
                            ->label('LPZ')
                            ->formatStateUsing(fn ($state) => $state ? 'Download Dokumen' : '-')
                            ->url(fn ($state) => self::getCloudinaryDownloadUrl($state), shouldOpenInNewTab: true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('unit.unit_name')
                    ->label('Nama UPZ')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit.district.name')
                    ->label('Kecamatan')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unit.village.name')
                    ->label('Desa')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('trx_date')
                    ->label('Tanggal Laporan')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lpz_year')
                    ->label('Tahun')
                    ->sortable(),
                Tables\Columns\TextColumn::make('form101')
                    ->label('Form 101')
                    ->formatStateUsing(fn ($state) => $state ? 'Download' : '-')
                    ->url(fn ($state) => self::getCloudinaryDownloadUrl($state))
                    ->openUrlInNewTab()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('form102')
                    ->label('Form 102')
                    ->formatStateUsing(fn ($state) => $state ? 'Download' : '-')
                    ->url(fn ($state) => self::getCloudinaryDownloadUrl($state))
                    ->openUrlInNewTab()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('lpz')
                    ->label('LPZ')
                    ->formatStateUsing(fn ($state) => $state ? 'Download' : '-')
                    ->url(fn ($state) => self::getCloudinaryDownloadUrl($state))
                    ->openUrlInNewTab()
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
                SelectFilter::make('lpz_year')
                    ->label('Tahun')
                    ->options(function () {
                        $currentYear = now()->year;
                        $years = [];
                        for ($year = $currentYear; $year >= 2020; $year--) {
                            $years[$year] = (string) $year;
                        }

                        return $years;
                    }),
                SelectFilter::make('unit_id')
                    ->label('Unit UPZ')
                    ->options(UnitZis::pluck('unit_name', 'id'))
                    ->searchable(),
                SelectFilter::make('district_id')
                    ->label('Kecamatan')
                    ->options(District::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->query(function ($query, array $data) {
                        if (filled($data['value'])) {
                            $query->whereHas('unit', fn ($q) => $q->where('district_id', $data['value']));
                        }
                    }),
                SelectFilter::make('village_id')
                    ->label('Desa')
                    ->options(Village::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->query(function ($query, array $data) {
                        if (filled($data['value'])) {
                            $query->whereHas('unit', fn ($q) => $q->where('village_id', $data['value']));
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(\App\Filament\Exports\LpzExporter::class),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLpzs::route('/'),
            'create' => Pages\CreateLpz::route('/create'),
            'view' => Pages\ViewLpz::route('/{record}'),
            'edit' => Pages\EditLpz::route('/{record}/edit'),
        ];
    }
}
