<?php

namespace App\Filament\Resources;

use App\Filament\Imports\ZmImporter;
use App\Filament\Resources\ZmResource\Pages;
use App\Models\District;
use App\Models\UnitZis;
use App\Models\User;
use App\Models\Village;
use App\Models\Zm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ZmResource extends Resource
{
    protected static ?string $model = Zm::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Rekap & Transaksi';

    protected static ?int $navigationSort = 2;

    protected static ?string $label = 'Zakat Mal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('unit_id')
                    ->relationship('unit', 'unit_name')
                    ->getOptionLabelFromRecordUsing(fn (UnitZis $record) => "{$record->no_register}-{$record->unit_name}")
                    ->searchable()
                    ->preload()
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
                Forms\Components\TextInput::make('no_telp')
                    ->label('No. Telepon')
                    ->string(),
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
                Tables\Columns\TextColumn::make('no_telp')
                    ->label('No. Telepon')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Zakat Mal')->money('IDR')),
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
                        fn (Builder $query, array $data): Builder => $query->when($data['value'], fn (Builder $q, $year) => $q->whereYear('trx_date', $year))
                    ),
                SelectFilter::make('category_maal')
                    ->label('Kategori Maal')
                    ->options(fn () => Zm::distinct()->pluck('category_maal', 'category_maal')->filter()),
                SelectFilter::make('district')
                    ->label('Kecamatan')
                    ->options(fn () => District::pluck('name', 'id'))
                    ->query(
                        fn (Builder $query, array $data): Builder => $query->when(
                            $data['value'],
                            fn (Builder $q, $districtId) => $q->whereHas('unit', fn ($q) => $q->where('district_id', $districtId))
                        )
                    )
                    ->visible(fn () => User::currentIsSuperAdmin() || User::currentIsAdmin()),
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
                        fn (Builder $query, array $data): Builder => $query->when(
                            $data['value'],
                            fn (Builder $q, $villageId) => $q->whereHas('unit', fn ($q) => $q->where('village_id', $villageId))
                        )
                    )
                    ->visible(fn () => User::currentIsSuperAdmin() || User::currentIsAdmin() || User::currentIsUpzKecamatan()),
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
                    ->visible(fn () => ! User::currentIsUpzDesa()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
            /* ImportAction::make()
                    ->importer(ZmImporter::class) */])
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
