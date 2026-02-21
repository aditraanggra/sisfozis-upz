<?php

namespace App\Filament\Resources;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Filament\Exports\DistrictExporter;
use App\Filament\Resources\DistrictResource\Pages;
use App\Filament\Resources\DistrictResource\RelationManagers;
use App\Models\District;
use App\Models\User;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\ExportAction as ActionsExportAction;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;

class DistrictResource extends Resource
{
    protected static ?string $model = District::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Rekap & Transaksi';

    protected static ?int $navigationSort = 8;

    protected static ?string $label = 'Rekap ZIS Per Kecamatan';

    protected const REKAP_PERIOD = 'tahunan';

    protected const TOTAL_ALIAS = 'total_zis_value';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    protected static function filteredRekap(District $district, ?string $periodDate = null): Collection
    {
        $periodDate = $periodDate ?? now()->format('Y') . '-01-01';

        $rekap = $district->relationLoaded('rekapZis')
            ? $district->rekapZis
            : $district->rekapZis()->get();

        return $rekap
            ->where('period', static::REKAP_PERIOD)
            ->where('period_date', $periodDate)
            ->values();
    }

    protected static function summarizeRekapColumn(QueryBuilder $districtQuery, string $column): float
    {
        $currentPeriodDate = now()->format('Y') . '-01-01';
        $expressions = [
            'total_zf_rice' => 'COALESCE(rekap_zis.total_zf_rice, 0)',
            'total_zf_amount' => 'COALESCE(rekap_zis.total_zf_amount, 0)',
            'total_zm_amount' => 'COALESCE(rekap_zis.total_zm_amount, 0)',
            'total_ifs_amount' => 'COALESCE(rekap_zis.total_ifs_amount, 0)',
            'total_zf_muzakki' => 'COALESCE(rekap_zis.total_zf_muzakki, 0)',
            'total_zm_muzakki' => 'COALESCE(rekap_zis.total_zm_muzakki, 0)',
            'total_ifs_munfiq' => 'COALESCE(rekap_zis.total_ifs_munfiq, 0)',
        ];

        if (! array_key_exists($column, $expressions)) {
            return 0.0;
        }

        return static::summarizeExpression($districtQuery, $expressions[$column], $currentPeriodDate);
    }

    protected static function summarizeTotal(QueryBuilder $districtQuery): float
    {
        $currentPeriodDate = now()->format('Y') . '-01-01';
        $expression = implode(' + ', [
            'COALESCE(rekap_zis.total_zf_amount, 0)',
            'COALESCE(rekap_zis.total_zm_amount, 0)',
            'COALESCE(rekap_zis.total_ifs_amount, 0)',
            'COALESCE(rekap_zis.total_zf_rice, 0) * COALESCE(unit_zis.rice_price, 0)',
        ]);

        return static::summarizeExpression($districtQuery, $expression, $currentPeriodDate);
    }

    protected static function summarizeExpression(QueryBuilder $districtQuery, string $expression, ?string $periodDate = null): float
    {
        $periodDate = $periodDate ?? now()->format('Y') . '-01-01';
        $baseQuery = clone $districtQuery;

        $summary = DB::query()
            ->fromSub($baseQuery, 'districts')
            ->leftJoin('unit_zis', 'unit_zis.district_id', '=', 'districts.id')
            ->leftJoin('rekap_zis', function ($join) use ($periodDate) {
                $join->on('rekap_zis.unit_id', '=', 'unit_zis.id')
                    ->where('rekap_zis.period', static::REKAP_PERIOD)
                    ->where('rekap_zis.period_date', $periodDate);
            })
            ->selectRaw("COALESCE(SUM({$expression}), 0) as aggregate")
            ->value('aggregate');

        return (float) $summary;
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
                    ->getStateUsing(function (District $record, $livewire) {
                        $selectedYear = $livewire->tableFilters['tahun']['value'] ?? date('Y');
                        $periodDate = $selectedYear . '-01-01';
                        $prefetchedTotal = $record->{static::TOTAL_ALIAS} ?? null;

                        // Use prefetch only if filter matches current year (default)
                        if ($prefetchedTotal !== null && $selectedYear === date('Y')) {
                            return (float) $prefetchedTotal;
                        }

                        $rekap = static::filteredRekap($record, $periodDate);

                        $cashTotal = (float) $rekap->sum('total_zf_amount')
                            + (float) $rekap->sum('total_zm_amount')
                            + (float) $rekap->sum('total_ifs_amount');

                        $riceValue = (float) $rekap->sum(function ($item) {
                            $riceAmount = (float) ($item->total_zf_rice ?? 0);
                            $ricePrice = (float) ($item->unit?->rice_price ?? 0);

                            return $riceAmount * $ricePrice;
                        });

                        return $cashTotal + $riceValue;
                    })
                    ->numeric()
                    ->sortable(
                        true,
                        fn(Builder $query, string $direction): Builder => $query->orderBy(static::TOTAL_ALIAS, $direction)
                    ),

                Tables\Columns\TextColumn::make('total_zf_rice')
                    ->label('Zakat Fitrah (Beras)')
                    ->getStateUsing(function (District $record, $livewire) {
                        $selectedYear = $livewire->tableFilters['tahun']['value'] ?? date('Y');
                        $periodDate = $selectedYear . '-01-01';
                        return static::filteredRekap($record, $periodDate)->sum('total_zf_rice');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_zf_amount')
                    ->label('Zakat Fitrah (Uang)')
                    ->getStateUsing(function (District $record, $livewire) {
                        $selectedYear = $livewire->tableFilters['tahun']['value'] ?? date('Y');
                        $periodDate = $selectedYear . '-01-01';
                        return static::filteredRekap($record, $periodDate)->sum('total_zf_amount');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_zm_amount')
                    ->label('Zakat Mal')
                    ->getStateUsing(function (District $record, $livewire) {
                        $selectedYear = $livewire->tableFilters['tahun']['value'] ?? date('Y');
                        $periodDate = $selectedYear . '-01-01';
                        return static::filteredRekap($record, $periodDate)->sum('total_zm_amount');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_ifs_amount')
                    ->label('Infak Sedekah')
                    ->getStateUsing(function (District $record, $livewire) {
                        $selectedYear = $livewire->tableFilters['tahun']['value'] ?? date('Y');
                        $periodDate = $selectedYear . '-01-01';
                        return static::filteredRekap($record, $periodDate)->sum('total_ifs_amount');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_zf_muzakki')
                    ->label('Muzakki ZF')
                    ->getStateUsing(function (District $record, $livewire) {
                        $selectedYear = $livewire->tableFilters['tahun']['value'] ?? date('Y');
                        $periodDate = $selectedYear . '-01-01';
                        return static::filteredRekap($record, $periodDate)->sum('total_zf_muzakki');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_zm_muzakki')
                    ->label('Muzakki ZM')
                    ->getStateUsing(function (District $record, $livewire) {
                        $selectedYear = $livewire->tableFilters['tahun']['value'] ?? date('Y');
                        $periodDate = $selectedYear . '-01-01';
                        return static::filteredRekap($record, $periodDate)->sum('total_zm_muzakki');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_ifs_munfiq')
                    ->label('Munfiq')
                    ->getStateUsing(function (District $record, $livewire) {
                        $selectedYear = $livewire->tableFilters['tahun']['value'] ?? date('Y');
                        $periodDate = $selectedYear . '-01-01';
                        return static::filteredRekap($record, $periodDate)->sum('total_ifs_munfiq');
                    })
                    ->numeric(),
            ])
            ->filters([
                SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(
                        collect(range(0, 4))
                            ->mapWithKeys(fn($i) => [
                                now()->subYears($i)->format('Y') => now()->subYears($i)->format('Y')
                            ])
                            ->toArray()
                    )
                    ->default(now()->format('Y'))
                    ->query(fn ($query) => $query),
            ])
            ->actions([
                /*  ActionGroup::make([

                    Tables\Actions\Action::make('pdf')
                        ->label('Report UPZ DKM/RT/RW')
                        ->color('success')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn(Model $record) => route('report.pdf', $record))
                        ->openUrlInNewTab(),

                ])
                    ->icon('heroicon-o-cloud-arrow-down')
                    ->size('lg') */], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                //
            ])
            ->headerActions([
                ActionsExportAction::make()
                    ->exporter(DistrictExporter::class)
            ])
            ->defaultSort(static::TOTAL_ALIAS, 'desc')
            ->recordUrl(null)
            ->defaultPaginationPageOption(50);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if (User::currentIsUpzKecamatan() && $user->district_id) {
            $query->where('district_id', $user->district_id);
        }

        $currentPeriodDate = now()->format('Y') . '-01-01';

        $query->select('districts.*')
            ->selectSub(function ($subQuery) use ($currentPeriodDate) {
                $subQuery->from('rekap_zis')
                    ->join('unit_zis', 'unit_zis.id', '=', 'rekap_zis.unit_id')
                    ->whereColumn('unit_zis.district_id', 'districts.id')
                    ->where('rekap_zis.period', static::REKAP_PERIOD)
                    ->where('rekap_zis.period_date', $currentPeriodDate)
                    ->selectRaw(
                        'COALESCE(SUM(COALESCE(rekap_zis.total_zf_amount, 0) + COALESCE(rekap_zis.total_zm_amount, 0) + COALESCE(rekap_zis.total_ifs_amount, 0) + COALESCE(rekap_zis.total_zf_rice, 0) * COALESCE(unit_zis.rice_price, 0)), 0)'
                    );
            }, static::TOTAL_ALIAS);

        return $query
            ->withSum('rekapZis', 'total_zf_rice')
            ->withSum('rekapZis', 'total_zf_amount')
            ->withSum('rekapZis', 'total_zf_muzakki')
            ->withSum('rekapZis', 'total_zm_amount')
            ->withSum('rekapZis', 'total_zm_muzakki')
            ->withSum('rekapZis', 'total_ifs_amount')
            ->withSum('rekapZis', 'total_ifs_munfiq')
            ->with(['rekapZis.unit']);
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
