<?php

namespace App\Filament\Resources;

use App\Filament\Exports\VillageExporter;
use App\Filament\Resources\VillageResource\Pages;
use App\Filament\Resources\VillageResource\RelationManagers;
use App\Models\Village;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Support\Facades\Blade;

class VillageResource extends Resource
{
    protected static ?string $model = Village::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Kecamatan & Desa';

    protected static ?string $label = 'Rekap ZIS Per Desa';

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
                    ->label('Desa')
                    ->searchable(isIndividual: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('district.name')
                    ->label('Kecamatan')
                    ->searchable(isIndividual: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total ZIS')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_zf_amount') +
                            $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_zm_amount') +
                            $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_ifs_amount');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_zf_rice')
                    ->label('Zakat Fitrah (Beras)')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_zf_rice');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_zf_amount')
                    ->label('Zakat Fitrah (Uang)')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_zf_amount');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_zm_amount')
                    ->label('Zakat Mal')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_zm_amount');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_ifs_amount')
                    ->label('Infak Sedekah')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_ifs_amount');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_zf_muzakki')
                    ->label('Muzakki ZF')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_zf_muzakki');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_zm_muzakki')
                    ->label('Muzakki ZM')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_zm_muzakki');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_ifs_munfiq')
                    ->label('Munfiq')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_ifs_munfiq');
                    })
                    ->numeric(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([

                    Tables\Actions\Action::make('pdf')
                        ->label('Rekap DKM')
                        ->color('success')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn(Model $record) => route('village.pdf', $record))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('pdf')
                        ->label('Rekap Hak OP')
                        ->color('info')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn(Model $record) => route('op.pdf', $record))
                        ->openUrlInNewTab()
                ])
                    ->icon('heroicon-o-cloud-arrow-down')
                    ->size('lg')

            ], position: ActionsPosition::BeforeColumns)
            ->groups([
                Tables\Grouping\Group::make('district.name')
                    ->label('Kecamatan')
                    ->collapsible(),
            ])
            ->bulkActions([
                //
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(VillageExporter::class)
            ])
            ->recordUrl(null)
            ->defaultPaginationPageOption(25);
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
    public function view(Village $record)
    {
        return view('filament.resources.Village-resource.pages.view-Village', [
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
            'index' => Pages\ListVillages::route('/'),
            'create' => Pages\CreateVillage::route('/create'),
            'edit' => Pages\EditVillage::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
