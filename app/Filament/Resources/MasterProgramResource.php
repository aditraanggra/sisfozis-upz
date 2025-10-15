<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterProgramResource\Pages;
use App\Models\MasterProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MasterProgramResource extends Resource
{
    protected static ?string $model = MasterProgram::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';

    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    protected static ?int $navigationSort = 6;

    protected static ?string $label = 'Master Program';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Forms\Components\TextInput::make('name')
                    ->label('Nama Program')
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
                Tables\Columns\TextColumn::make('name')->label('Nama Program')->sortable()->searchable(),
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
            'index' => Pages\ListMasterPrograms::route('/'),
            'create' => Pages\CreateMasterProgram::route('/create'),
            'edit' => Pages\EditMasterProgram::route('/{record}/edit'),
        ];
    }
}
