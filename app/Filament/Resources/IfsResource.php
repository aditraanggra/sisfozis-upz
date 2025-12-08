<?php

namespace App\Filament\Resources;

use App\Filament\Imports\IfsImporter;
use App\Filament\Resources\IfsResource\Pages;
use App\Filament\Resources\IfsResource\RelationManagers;
use App\Models\District;
use App\Models\Ifs;
use App\Models\UnitZis;
use App\Models\User;
use App\Models\Village;
use Filament\Tables\Actions\ImportAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IfsResource extends Resource
{
    protected static ?string $model = Ifs::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';

    protected static ?string $navigationGroup = 'Rekap & Transaksi';

    protected static ?int $navigationSort = 3;

    protected static ?string $label = 'Infak Sedekah';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('unit_id')
                    ->relationship('unit', 'unit_name')
                    ->getOptionLabelFromRecordUsing(fn(UnitZis $record) =>
                    "{$record->no_register}-{$record->unit_name}")
                    ->searchable()
                    ->preload()
                    ->required(),
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
                Tables\Columns\TextColumn::make('unit.no_register')
                    ->label('Unit')
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
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                /*  ImportAction::make()
                    ->importer(IfsImporter::class) */])
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
