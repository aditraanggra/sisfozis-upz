<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LpzResource\Pages;
use App\Models\Lpz;
use App\Models\UnitZis;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class LpzResource extends Resource
{
    protected static ?string $model = Lpz::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Laporan LPZ';
    protected static ?string $modelLabel = 'LPZ';
    protected static ?string $pluralModelLabel = 'Laporan LPZ';
    protected static ?string $navigationGroup = 'Rekap & Transaksi';
    protected static ?int $navigationSort = 5;

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
