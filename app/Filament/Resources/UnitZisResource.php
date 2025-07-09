<?php

namespace App\Filament\Resources;

use App\Filament\Imports\UnitZisImporter;
use App\Filament\Resources\UnitZisResource\Pages;
use App\Models\District;
use App\Models\UnitZis;
use App\Models\Village;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UnitZisResource extends Resource
{
    protected static ?string $model = UnitZis::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Unit Pengumpul Zakat (UPZ)';

    protected static ?int $navigationSort = 9;

    // protected static ?int $navigationSort = ;

    protected static ?string $label = 'Daftar UPZ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Data Operator')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Operator')
                            ->options(function () {
                                $usedUserIds = UnitZis::pluck('user_id')->toArray();
                                return \App\Models\User::whereNotIn('id', $usedUserIds)->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('operator_phone')
                            ->label('Nomor Telepon Operator')
                            ->required()
                            ->maxLength(255),
                    ]),
                Fieldset::make('Data Unit Penumpul Zakat (UPZ)')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->label('Unit Kerja')
                            ->required(),
                        Forms\Components\TextInput::make('no_register')
                            ->label('Nomor Register')
                            ->readOnly()
                            //->default(fn(Get $get) => $get('village_code') . '-' . rand(1, 100))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('unit_name')
                            ->label('Nama Unit')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('address')
                            ->label('Alamat')
                            ->required()
                            ->maxLength(255),
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
                    ]),
                Fieldset::make('Data Pengurus')
                    ->schema([
                        Forms\Components\TextInput::make('no_sk')
                            ->label('Nomor SK')
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
                    ]),
                Fieldset::make('Data Lainnya')
                    ->schema([
                        Forms\Components\TextInput::make('rice_price')
                            ->label('Harga Beras')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_verified')
                            ->label('Terverifikasi')
                            ->required()
                            ->default(false),
                        Forms\Components\TextInput::make('profile_completion')
                            ->label('Indeks Profil')
                            ->numeric()
                            ->required()
                            ->maxLength(255)
                            ->minValue(0)
                            ->maxValue(100),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Operator')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Unit Kerja')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('district.name')
                    ->label('Kecamatan')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('village.name')
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
                Tables\Columns\TextColumn::make('profile_completion')
                    ->label('Indeks Profil')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori UPZ')
                    ->options([
                        4 => 'DKM',
                        3 => 'Desa/Kelurahan',
                        2 => 'Kecamatan',
                    ]),
                Tables\Filters\Filter::make('location')
                    ->form([
                        Forms\Components\Select::make('district_id')
                            ->label('Kecamatan')
                            ->options(District::all()->pluck('name', 'id'))
                            ->afterStateUpdated(fn(Forms\Set $set) => $set('village_id', null))
                            ->live()
                            ->searchable(),

                        Forms\Components\Select::make('village_id')
                            ->label('Desa/Kelurahan')
                            ->options(function (Forms\Get $get) {
                                return $get('district_id')
                                    ? Village::where('district_id', $get('district_id'))->pluck('name', 'id')
                                    : [];
                            })
                            ->searchable(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['district_id'], fn($q) => $q->where('district_id', $data['district_id']))
                            ->when($data['village_id'], fn($q) => $q->where('village_id', $data['village_id']));
                    })
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
                ImportAction::make()->importer(UnitZisImporter::class),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {

        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if (User::currentIsUpzKecamatan() && $user->district_id) {
            $query->where('district_id', $user->district_id);
        } elseif (User::currentIsUpzDesa() && $user->village_id) {
            $query->where('village_id', $user->village_id);
        } elseif (User::currentIsSuperAdmin()) {
            // Super admin can see everything, no filter applied
        } else {
            // Default: restrict to nothing
            $query->whereRaw('1 = 0');
        }

        return $query;
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
