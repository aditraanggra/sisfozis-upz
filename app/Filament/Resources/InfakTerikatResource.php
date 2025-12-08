<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InfakTerikatResource\Pages;
use App\Models\District;
use App\Models\InfakTerikat;
use App\Models\MasterProgram;
use App\Models\UnitZis;
use App\Models\User;
use App\Models\Village;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InfakTerikatResource extends Resource
{
    protected static ?string $model = InfakTerikat::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'Rekap & Transaksi';

    protected static ?int $navigationSort = 4;

    protected static ?string $label = 'Infak Terikat';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Forms\Components\Select::make('unit_id')
                    ->label('Unit UPZ')
                    ->relationship('unit', 'unit_name')
                    ->required(),

                Forms\Components\Select::make('program_id')
                    ->label('Program')
                    ->relationship('program', 'name')
                    ->required(),

                Forms\Components\DatePicker::make('trx_date')
                    ->label('Tanggal Transaksi')
                    ->required(),

                Forms\Components\TextInput::make('munfiq_name')
                    ->label('Nama Munfiq')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('amount')
                    ->label('Jumlah (Rp)')
                    ->numeric()
                    ->required(),

                Forms\Components\Textarea::make('desc')
                    ->label('Deskripsi')
                    ->maxLength(65535),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('unit.unit_name')->label('Unit UPZ')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('program.name')->label('Program')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('trx_date')->label('Tanggal Transaksi')->date()->sortable(),
                Tables\Columns\TextColumn::make('munfiq_name')->label('Nama Munfiq')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('amount')->label('Jumlah (Rp)')->money('idr', true)->sortable(),
                Tables\Columns\TextColumn::make('desc')->label('Deskripsi')->sortable()->searchable(),
            ])
            ->filters([
                SelectFilter::make('trx_year')
                    ->label('Tahun')
                    ->options(function () {
                        $currentYear = now()->year;
                        $years = [];
                        for ($year = $currentYear; $year >= 2020; $year--) {
                            $years[$year] = (string) $year;
                        }
                        return $years;
                    })
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when($data['value'], fn(Builder $q, $year) => $q->whereYear('trx_date', $year))
                    ),
                SelectFilter::make('program_id')
                    ->label('Program')
                    ->options(fn() => MasterProgram::pluck('name', 'id')),
                SelectFilter::make('district')
                    ->label('Kecamatan')
                    ->options(fn() => District::pluck('name', 'id'))
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when(
                            $data['value'],
                            fn(Builder $q, $districtId) =>
                            $q->whereHas('unit', fn($q) => $q->where('district_id', $districtId))
                        )
                    )
                    ->visible(fn() => User::currentIsSuperAdmin() || User::currentIsAdmin()),
                SelectFilter::make('village')
                    ->label('Desa')
                    ->options(function () {
                        $user = User::current();
                        if ($user && $user->isUpzKecamatan() && $user->district_id) {
                            return Village::where('district_id', $user->district_id)->pluck('name', 'id');
                        }
                        return Village::pluck('name', 'id');
                    })
                    ->searchable()
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when(
                            $data['value'],
                            fn(Builder $q, $villageId) =>
                            $q->whereHas('unit', fn($q) => $q->where('village_id', $villageId))
                        )
                    )
                    ->visible(fn() => User::currentIsSuperAdmin() || User::currentIsAdmin() || User::currentIsUpzKecamatan()),
                SelectFilter::make('unit_id')
                    ->label('Unit UPZ')
                    ->options(function () {
                        $user = User::current();
                        if ($user && $user->isUpzKecamatan() && $user->district_id) {
                            return UnitZis::where('district_id', $user->district_id)->pluck('unit_name', 'id');
                        }
                        return UnitZis::pluck('unit_name', 'id');
                    })
                    ->searchable()
                    ->visible(fn() => !User::currentIsUpzDesa()),
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
            'index' => Pages\ListInfakTerikats::route('/'),
            'create' => Pages\CreateInfakTerikat::route('/create'),
            'edit' => Pages\EditInfakTerikat::route('/{record}/edit'),
        ];
    }
}
