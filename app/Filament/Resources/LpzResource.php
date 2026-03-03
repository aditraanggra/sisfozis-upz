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
     * Construct Cloudinary URL locally to avoid expensive Admin API calls.
     * PDFs are stored as 'raw' resource type in Cloudinary.
     */
    private static function getCloudinaryUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $cloudinaryUrl = env('CLOUDINARY_URL');
        $cloudName = parse_url($cloudinaryUrl, PHP_URL_HOST);

        if (!$cloudName) {
            return null;
        }

        // Remove file extension from path for the public ID (Cloudinary convention)
        $info = pathinfo($path);
        $publicId = $info['dirname'] . '/' . $info['filename'];

        return "https://res.cloudinary.com/{$cloudName}/raw/upload/v1/{$publicId}";
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
                            ->formatStateUsing(fn ($state) => $state ? 'Lihat Dokumen' : '-')
                            ->url(fn ($state) => self::getCloudinaryUrl($state), shouldOpenInNewTab: true),
                        Infolists\Components\TextEntry::make('form102')
                            ->label('Form 102')
                            ->formatStateUsing(fn ($state) => $state ? 'Lihat Dokumen' : '-')
                            ->url(fn ($state) => self::getCloudinaryUrl($state), shouldOpenInNewTab: true),
                        Infolists\Components\TextEntry::make('lpz')
                            ->label('LPZ')
                            ->formatStateUsing(fn ($state) => $state ? 'Lihat Dokumen' : '-')
                            ->url(fn ($state) => self::getCloudinaryUrl($state), shouldOpenInNewTab: true),
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
                    ->formatStateUsing(fn ($state) => $state ? 'Lihat' : '-')
                    ->url(fn ($state) => self::getCloudinaryUrl($state))
                    ->openUrlInNewTab()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('form102')
                    ->label('Form 102')
                    ->formatStateUsing(fn ($state) => $state ? 'Lihat' : '-')
                    ->url(fn ($state) => self::getCloudinaryUrl($state))
                    ->openUrlInNewTab()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('lpz')
                    ->label('LPZ')
                    ->formatStateUsing(fn ($state) => $state ? 'Lihat' : '-')
                    ->url(fn ($state) => self::getCloudinaryUrl($state))
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
