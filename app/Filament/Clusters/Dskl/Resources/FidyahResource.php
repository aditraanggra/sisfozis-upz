<?php

namespace App\Filament\Clusters\Dskl\Resources;

use App\Filament\Clusters\Dskl;
use App\Filament\Clusters\Dskl\Resources\FidyahResource\Pages;
use App\Models\Fidyah;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FidyahResource extends Resource
{
    protected static ?string $model = Fidyah::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Fidyah';

    protected static ?string $cluster = Dskl::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('unit_id')
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('trx_date')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('total_day')
                    ->required()
                    ->numeric()
                    ->default(1),
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
                Tables\Columns\TextColumn::make('unit_id')
                    ->label('ID UPZ')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trx_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.no_register')
                    ->label('No Register')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.unit_name')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_day')
                    ->label('Total Hari')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah (Rp)')
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
                Tables\Actions\EditAction::make(),
            ])
            ->emptyStateHeading('Belum ada data fidyah')
            ->emptyStateDescription('Isi data fidyah untuk menampilkan informasi di sini.')
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
            'index' => Pages\ListFidyahs::route('/'),
            'create' => Pages\CreateFidyah::route('/create'),
            'edit' => Pages\EditFidyah::route('/{record}/edit'),
        ];
    }
}
