<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DokumentasiKurbanResource\Pages;
use App\Models\DokumentasiKurban;
use App\Models\LaporanKurban;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DokumentasiKurbanResource extends Resource
{
    protected static ?string $model = DokumentasiKurban::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Kurban';

    protected static ?string $navigationLabel = 'Dokumentasi Kurban';

    protected static ?string $modelLabel = 'Dokumentasi Kurban';

    protected static ?string $pluralModelLabel = 'Dokumentasi Kurban';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->recordAction('view')
            ->persistFiltersInSession()
            ->columns([
                Tables\Columns\TextColumn::make('laporanKurban.unit.unit_name')
                    ->label('Nama UPZ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('laporanKurban.tahun_masehi')
                    ->label('Tahun')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('file_path')
                    ->label('File')
                    ->disk('cloudinary')
                    ->size(40)
                    ->visible(fn ($record) => $record && $record->mime_type && str_starts_with($record->mime_type, 'image/')),
                Tables\Columns\IconColumn::make('file_path')
                    ->label('File')
                    ->icon('heroicon-o-document')
                    ->visible(fn ($record) => $record && $record->mime_type && ! str_starts_with($record->mime_type, 'image/'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('file_size')
                    ->label('Ukuran')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 1).' KB' : '-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Tipe File')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diupload')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('jenis')
                    ->label('Jenis')
                    ->options(fn () => DokumentasiKurban::distinct()->pluck('jenis', 'jenis')->filter()),
                SelectFilter::make('laporan_kurban_id')
                    ->label('Laporan Kurban')
                    ->options(fn () => LaporanKurban::join('units', 'units.id', 'laporan_kurbans.unit_id')->pluck('units.unit_name', 'laporan_kurbans.id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->infolist([
                        Infolists\Components\TextEntry::make('laporanKurban.unit.unit_name')
                            ->label('Nama UPZ'),
                        Infolists\Components\TextEntry::make('laporanKurban.tahun_masehi')
                            ->label('Tahun Masehi'),
                        Infolists\Components\TextEntry::make('laporanKurban.tahun_hijriah')
                            ->label('Tahun Hijriah'),
                        Infolists\Components\TextEntry::make('jenis')
                            ->label('Jenis')
                            ->badge(),
                        Infolists\Components\TextEntry::make('file_size')
                            ->label('Ukuran File')
                            ->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 1).' KB' : '-'),
                        Infolists\Components\TextEntry::make('mime_type')
                            ->label('Tipe File'),
                        Infolists\Components\ImageEntry::make('file_path')
                            ->label('File')
                            ->disk('cloudinary')
                            ->height(300)
                            ->visible(fn ($record) => $record && $record->mime_type && str_starts_with($record->mime_type, 'image/')),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada dokumentasi kurban')
            ->emptyStateDescription('Dokumentasi kurban akan ditampilkan di sini.');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDokumentasiKurbans::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
