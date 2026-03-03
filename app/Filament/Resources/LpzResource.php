<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LpzResource\Pages;
use App\Models\Lpz;
use App\Models\UnitZis;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Tables\Filters\SelectFilter;

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
     * Generate a signed Cloudinary URL for raw files (PDFs).
     * Uses the Cloudinary SDK locally — no HTTP API calls.
     */
    private static function getCloudinaryUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        try {
            // Extract public ID from the stored value
            $publicId = $path;

            // If stored as full URL, extract the public ID from it
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                // URL format: https://res.cloudinary.com/{cloud}/raw/upload/v{version}/{publicId}
                $parsed = parse_url($path, PHP_URL_PATH);
                // Remove /raw/upload/vXXX/ prefix to get the public ID
                if (preg_match('#/raw/upload/v\d+/(.+)$#', $parsed, $matches)) {
                    $publicId = $matches[1];
                } elseif (preg_match('#/(?:image|video|raw)/upload/(?:v\d+/)?(.+)$#', $parsed, $matches)) {
                    $publicId = $matches[1];
                }
            }

            // Remove file extension (Cloudinary stores public IDs without extension for raw files)
            $info = pathinfo($publicId);
            if (isset($info['extension'])) {
                $publicId = $info['dirname'] . '/' . $info['filename'];
            }

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
    private static function getCloudinaryDownloadUrl(?string $path): ?string
    {
        $url = self::getCloudinaryUrl($path);
        if (!$url) {
            return null;
        }

        // Append fl_attachment flag to force download
        $separator = str_contains($url, '?') ? '&' : '?';
        return $url . $separator . 'fl_attachment=true';
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
                    ->downloadable(),
                Forms\Components\FileUpload::make('form102')
                    ->label('Form 102')
                    ->disk('cloudinary')
                    ->directory('sisfo/form')
                    ->visibility('public')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240)
                    ->openable()
                    ->downloadable(),
                Forms\Components\FileUpload::make('lpz')
                    ->label('LPZ')
                    ->disk('cloudinary')
                    ->directory('sisfo/documents')
                    ->visibility('public')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240)
                    ->openable()
                    ->downloadable(),
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
