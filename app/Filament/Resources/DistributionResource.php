<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistributionResource\Pages;
use App\Models\Distribution;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DistributionResource extends Resource
{
    protected static ?string $model = Distribution::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Rekap & Transaksi';

    protected static ?int $navigationSort = 5;

    protected static ?string $label = 'Pendistribusian';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('unit_id')
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('trx_date')
                    ->required(),
                Forms\Components\TextInput::make('mustahik_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nik')
                    ->maxLength(255),
                Forms\Components\TextInput::make('fund_type')
                    ->maxLength(255),
                Forms\Components\TextInput::make('asnaf')
                    ->maxLength(255),
                Forms\Components\TextInput::make('program')
                    ->maxLength(255),
                Forms\Components\TextInput::make('total_rice')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('beneficiary')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\Textarea::make('desc')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('unit.no_register')
                    ->label('No. Register')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.unit_name')
                    ->label('Nama UPZ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trx_date')
                    ->label('Tgl. Transaksi')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mustahik_name')
                    ->label('Nama Mustahik')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fund_type')
                    ->label('Jenis Dana')
                    ->searchable(),
                Tables\Columns\TextColumn::make('asnaf')
                    ->label('Asnaf')
                    ->searchable(),
                Tables\Columns\TextColumn::make('program')
                    ->label('Program')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_rice')
                    ->label('Total Beras (kg)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Nominal (Rp)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('beneficiary')
                    ->label('Jumlah Penerima')
                    ->numeric()
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
                //
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListDistributions::route('/'),
            'create' => Pages\CreateDistribution::route('/create'),
            'edit' => Pages\EditDistribution::route('/{record}/edit'),
        ];
    }
}
