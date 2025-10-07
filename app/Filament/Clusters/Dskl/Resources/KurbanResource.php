<?php

namespace App\Filament\Clusters\Dskl\Resources;

use App\Filament\Clusters\Dskl;
use App\Filament\Clusters\Dskl\Resources\KurbanResource\Pages;
use App\Filament\Clusters\Dskl\Resources\KurbanResource\RelationManagers;
use App\Models\Kurban;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KurbanResource extends Resource
{
    protected static ?string $model = Kurban::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationLabel = 'Kurban';

    protected static ?string $cluster = Dskl::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('unit_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('total_mudhohi')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('animal_types')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total_benef')
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
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.no_register')
                    ->label('No Register')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.unit_name')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_mudhohi')
                    ->label('Jumlah Mudhohi')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('animal_types')
                    ->label('Jenis Hewan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total Hewan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_benef')
                    ->label('Total Penerima Manfaat')
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
                Tables\Columns\TextColumn::make('deleted_at')
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
            ->emptyStateHeading('Belum ada data kurban')
            ->emptyStateDescription('Isi data kurban untuk menampilkan informasi di sini.')
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
            'index' => Pages\ListKurbans::route('/'),
            'create' => Pages\CreateKurban::route('/create'),
            'edit' => Pages\EditKurban::route('/{record}/edit'),
        ];
    }
}
