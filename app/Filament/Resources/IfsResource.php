<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IfsResource\Pages;
use App\Filament\Resources\IfsResource\RelationManagers;
use App\Models\Ifs;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IfsResource extends Resource
{
    protected static ?string $model = Ifs::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';

    protected static ?string $navigationGroup = 'Penerimaan ZIS';

    protected static ?string $label = 'Infak Sedekah';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'unit_name')
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('trx_date')
                    ->label('Tanggal Transaksi')
                    ->native()
                    ->required(),
                Forms\Components\TextInput::make('munfiq_name')
                    ->label('Nama Munfik')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('amount')
                    ->label('Jumlah')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Textarea::make('desc')
                    ->label('Keterangan')
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
                Tables\Columns\TextColumn::make('unit.unit_name')
                    ->label('Unit')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trx_date')
                    ->label('Tanggal Transaksi')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('munfiq_name')
                    ->label('Nama Munfik')
                    ->searchable()
                    ->summarize(Count::make()->label('Total Munfik')),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Infak Sedekah')->money('IDR')),
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
            'index' => Pages\ListIfs::route('/'),
            'create' => Pages\CreateIfs::route('/create'),
            'edit' => Pages\EditIfs::route('/{record}/edit'),
        ];
    }
}
