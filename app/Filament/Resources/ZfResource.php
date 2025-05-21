<?php

namespace App\Filament\Resources;

use App\Filament\Imports\ZfImporter;
use App\Filament\Resources\ZfResource\Pages;
use App\Filament\Resources\ZfResource\RelationManagers;
use App\Models\Zf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ZfResource extends Resource
{
    protected static ?string $model = Zf::class;

    protected static ?string $navigationIcon = 'heroicon-o-moon';

    protected static ?string $navigationGroup = 'Rekap & Transaksi';

    protected static ?int $navigationSort = 1;

    protected static ?string $label = 'Zakat Fitrah';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('unit_id')
                    ->relationship('unit', 'unit_name')
                    ->required(),
                Forms\Components\DatePicker::make('trx_date')
                    ->native()
                    ->required(),
                Forms\Components\TextInput::make('muzakki_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('zf_rice')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('zf_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total_muzakki')
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('unit.no_register')
                    ->label('No Register')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.unit_name')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trx_date')
                    ->label('Tanggal Transaksi')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('muzakki_name')
                    ->label('Nama Muzakki')
                    ->searchable()
                    ->summarize(Count::make()->label('Total Transaksi')),
                Tables\Columns\TextColumn::make('zf_rice')
                    ->label('Beras')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Zakat Fitrah Beras')),
                Tables\Columns\TextColumn::make('zf_amount')
                    ->label('Uang')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Zakat Fitrah Uang')->money('IDR')),
                Tables\Columns\TextColumn::make('total_muzakki')
                    ->label('Total Muzakki')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Muzakki')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(ZfImporter::class)
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('trx_date', 'desc');
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
            'index' => Pages\ListZfs::route('/'),
            'create' => Pages\CreateZf::route('/create'),
            'edit' => Pages\EditZf::route('/{record}/edit'),
        ];
    }
}
