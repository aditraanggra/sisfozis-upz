<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InfakTerikatResource\Pages;
use App\Filament\Resources\InfakTerikatResource\RelationManagers;
use App\Models\InfakTerikat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InfakTerikatResource extends Resource
{
    protected static ?string $model = InfakTerikat::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'Rekap & Transaksi';

    protected static ?int $navigationSort = 4;

    protected static ?string $label = 'Infak Terikat';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('unit.name')->label('Unit UPZ')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('program.name')->label('Program')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('trx_date')->label('Tanggal Transaksi')->date()->sortable(),
                Tables\Columns\TextColumn::make('munfiq_name')->label('Nama Munfiq')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('amount')->label('Jumlah (Rp)')->money('idr', true)->sortable(),
                Tables\Columns\TextColumn::make('desc')->label('Deskripsi')->sortable()->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInfakTerikats::route('/'),
            'create' => Pages\CreateInfakTerikat::route('/create'),
            'edit' => Pages\EditInfakTerikat::route('/{record}/edit'),
        ];
    }
}
