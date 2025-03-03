<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitZisResource\Pages;
use App\Filament\Resources\UnitZisResource\RelationManagers;
use App\Models\District;
use App\Models\UnitZis;
use App\Models\Village;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use NunoMaduro\Collision\Adapters\Phpunit\State;

class UnitZisResource extends Resource
{
    protected static ?string $model = UnitZis::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Operator')
                    ->required(),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Unit Kerja')
                    ->required(),
                Forms\Components\Select::make('district_id')
                    ->options(fn() => District::all()->pluck('name', 'id'))
                    ->label('Kecamatan')
                    ->live()
                    ->preload()
                    ->searchable()
                    ->afterStateUpdated(function (Set $set) {
                        $set('village_id', null);
                    })
                    ->required(),
                Forms\Components\Select::make('village_id')
                    ->options(
                        fn(Get $get) =>
                        $get('district_id')
                            ? Village::query()
                            ->where('district_id', $get('district_id'))
                            ->get()
                            ->pluck('name', 'id')
                            : []
                    )
                    ->label('Desa')
                    ->live()
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('no_sk')
                    ->label('Nomor SK')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('unit_name')
                    ->label('Nama Unit')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('no_register')
                    ->label('Nomor Register')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address')
                    ->label('Alamat')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('unit_leader')
                    ->label('Ketua')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('unit_assistant')
                    ->label('Sekretaris')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('unit_finance')
                    ->label('Bendahara')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('operator_name')
                    ->label('Nama Operator')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('operator_phone')
                    ->label('Nomor Telepon Operator')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('rice_price')
                    ->label('Harga Beras')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_verified')
                    ->label('Terverifikasi')
                    ->required()
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->label('Operator')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('category_id')
                    ->label('Unit Kerja')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('district_id')
                    ->label('Kecamatan')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('village_id')
                    ->label('Desa')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_sk')
                    ->label('Nomor SK')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_name')
                    ->label('Nama Unit')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_register')
                    ->label('Nomor Register')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_leader')
                    ->label('Ketua')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_assistant')
                    ->label('Sekretaris')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_finance')
                    ->label('Bendahara')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('operator_name')
                    ->label('Nama Operator')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('operator_phone')
                    ->label('Nomor Telepon Operator')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('rice_price')
                    ->label('Harga Beras')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_verified')
                    ->label('Terverifikasi')
                    ->sortable()
                    ->searchable(),
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
            'index' => Pages\ListUnitZis::route('/'),
            'create' => Pages\CreateUnitZis::route('/create'),
            'edit' => Pages\EditUnitZis::route('/{record}/edit'),
        ];
    }
}
