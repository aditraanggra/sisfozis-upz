<?php

namespace App\Filament\Resources;

use App\Filament\Imports\ZmImporter;
use App\Filament\Resources\ZmResource\Pages;
use App\Filament\Resources\ZmResource\RelationManagers;
use App\Models\Zm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\ImportAction; // Ensure this is the correct namespace for ImportAction

class ZmResource extends Resource
{
    protected static ?string $model = Zm::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Penerimaan ZIS';

    protected static ?string $label = 'Zakat Mal';

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
                Forms\Components\TextInput::make('category_maal')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('muzakki_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0),
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
                Tables\Columns\TextColumn::make('category_maal')
                    ->label('Kategori Maal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('muzakki_name')
                    ->label('Muzakki')
                    ->searchable()
                    ->summarize(Count::make()->label('Total Transaksi')),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Zakat Mal')->money('IDR')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(ZmImporter::class)
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
            'index' => Pages\ListZms::route('/'),
            'create' => Pages\CreateZm::route('/create'),
            'edit' => Pages\EditZm::route('/{record}/edit'),
        ];
    }
}
