<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistrictResource\Pages;
use App\Filament\Resources\DistrictResource\RelationManagers;
use App\Models\District;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DistrictResource extends Resource
{
    protected static ?string $model = District::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Kecamatan & Desa';

    protected static ?string $label = 'Rekap ZIS Per Kecamatan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor')
                    ->label('No')
                    ->rowIndex()
                    ->sortable(false),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kecamatan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total ZIS')
                    ->getStateUsing(function ($record) {
                        return
                            $record->rekapZis->sum('total_zf_amount') +
                            $record->rekapZis->sum('total_zm_amount') +
                            $record->rekapZis->sum('total_ifs_amount');
                    })
                    ->numeric()
                    ->sortable(query: function ($query) {
                        return $query->orderByRaw(
                            '(COALESCE(rekap_zis_total_zf_amount_sum, 0) + ' .
                                'COALESCE(rekap_zis_total_zm_amount_sum, 0) + ' .
                                'COALESCE(rekap_zis_total_ifs_amount_sum, 0)) ' .
                                'DESC'
                        );
                    }),

                Tables\Columns\TextColumn::make('total_zf_rice')
                    ->label('Zakat Fitrah (Beras)')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis->sum('total_zf_rice');
                    })
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_zf_amount')
                    ->label('Zakat Fitrah (Uang)')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis->sum('total_zf_amount');
                    })
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_zf_muzakki')
                    ->label('Muzakki ZF')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis->sum('total_zf_muzakki');
                    })
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_zm_amount')
                    ->label('Zakat Mal')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis->sum('total_zm_amount');
                    })
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_zm_muzakki')
                    ->label('Muzakki ZM')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis->sum('total_zm_muzakki');
                    })
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_ifs_amount')
                    ->label('Infak Sedekah')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis->sum('total_ifs_amount');
                    })
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_ifs_munfiq')
                    ->label('Munfiq')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis->sum('total_ifs_munfiq');
                    })
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail')
                    ->modalHeading(fn($record) => "Detail Rekap ZIS Kecamatan {$record->name}")
                    ->modalContent(function ($record) {
                        return view('filament.resources.district-resource.view', [
                            'record' => $record,
                            'rekapZis' => $record->rekapZis()->where('period', 'tahunan')
                                ->whereYear('period_date', 2025)
                                ->get()
                        ]);
                    })
            ])
            ->bulkActions([
                //
            ])
            //->defaultSort('total', 'desc')
            ->recordUrl(null);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withSum('rekapZis', 'total_zf_rice')
            ->withSum('rekapZis', 'total_zf_amount')
            ->withSum('rekapZis', 'total_zf_muzakki')
            ->withSum('rekapZis', 'total_zm_amount')
            ->withSum('rekapZis', 'total_zm_muzakki')
            ->withSum('rekapZis', 'total_ifs_amount')
            ->withSum('rekapZis', 'total_ifs_munfiq')
            ->with('rekapZis');
    }

    // Tambahkan method view untuk modal detail
    public function view(District $record)
    {
        return view('filament.resources.district-resource.pages.view-district', [
            'record' => $record,
            'rekapZis' => $record->rekapZis
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
            'index' => Pages\ListDistricts::route('/'),
            'create' => Pages\CreateDistrict::route('/create'),
            'edit' => Pages\EditDistrict::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
