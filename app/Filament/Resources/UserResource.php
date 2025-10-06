<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Filament\Imports\UserImporter;
use App\Models\District;
use App\Models\Village;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    protected static ?int $navigationSort = 3;

    protected static ?string $label = 'User';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Name'),
                Forms\Components\TextInput::make('email')
                    ->required()
                    ->email()
                    ->label('Email'),
                Forms\Components\TextInput::make('password')
                    ->required()
                    ->password()
                    ->label('Password'),
                Forms\Components\Select::make('role_id')
                    ->relationship('roles', 'name')
                    ->required()
                    ->label('Role'),
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
                            ->mapWithKeys(function ($village) {
                                return [$village->id => $village->name];
                            })
                            : []
                    )
                    ->label('Desa')
                    ->live()
                    ->preload()
                    ->searchable()
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        // Ambil village_code dari database berdasarkan village_id yang dipilih
                        $village = Village::find($get('village_id'));
                        if ($village) {
                            // Gabungkan village_code dengan angka random 1-100
                            $randomNumber = rand(1, 100);
                            $noRegister = $village->village_code . $randomNumber;
                            $set('no_register', $noRegister);
                        }
                    })
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => ucwords(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('district.name')
                    ->label('Kecamatan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('village.name')
                    ->label('Desa')
                    ->searchable()
                    ->sortable(),
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
            ->headerActions([
                ImportAction::make()->importer(UserImporter::class),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
