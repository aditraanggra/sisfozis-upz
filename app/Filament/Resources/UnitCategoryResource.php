<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitCategoryResource\Pages;
use App\Filament\Resources\UnitCategoryResource\RelationManagers;
use App\Models\UnitCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitCategoryResource extends Resource
{
    protected static ?string $model = UnitCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'UNIT PENGUMPUL ZAKAT (UPZ)';

    protected static ?string $label = 'Kategori UPZ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Forms\Components\TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('desc')
                    ->label('Deskripsi')
                    ->required()
                    ->maxLength(65535),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama Kategori')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('desc')->label('Deskripsi')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Updated At')->dateTime()->sortable(),
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
            'index' => Pages\ListUnitCategories::route('/'),
            'create' => Pages\CreateUnitCategory::route('/create'),
            'edit' => Pages\EditUnitCategory::route('/{record}/edit'),
        ];
    }
}
