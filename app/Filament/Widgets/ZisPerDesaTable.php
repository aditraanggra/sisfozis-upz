<?php

namespace App\Filament\Widgets;

use App\Models\District;
use App\Models\Ifs;
use App\Models\User;
use App\Models\Village;
use App\Models\Zf;
use App\Models\Zm;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class ZisPerDesaTable extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Penerimaan ZIS per Desa')
            ->query(function () {
                $startDate = $this->filters['startDate'] ?? null;
                $endDate = $this->filters['endDate'] ?? null;
                $year = $this->filters['year'] ?? null;
                $user = Auth::user();

                $query = Village::query()
                    ->select('villages.*')
                    ->with('district')
                    ->selectSub(
                        Zf::query()
                            ->selectRaw('COALESCE(SUM(zf_amount), 0)')
                            ->join('unit_zis', 'zfs.unit_id', '=', 'unit_zis.id')
                            ->whereColumn('unit_zis.village_id', 'villages.id')
                            ->when($startDate, fn($q) => $q->whereDate('trx_date', '>=', $startDate))
                            ->when($endDate, fn($q) => $q->whereDate('trx_date', '<=', $endDate))
                            ->when($year, fn($q) => $q->whereYear('trx_date', $year)),
                        'total_zf_amount'
                    )
                    ->selectSub(
                        Zf::query()
                            ->selectRaw('COALESCE(SUM(zf_rice), 0)')
                            ->join('unit_zis', 'zfs.unit_id', '=', 'unit_zis.id')
                            ->whereColumn('unit_zis.village_id', 'villages.id')
                            ->when($startDate, fn($q) => $q->whereDate('trx_date', '>=', $startDate))
                            ->when($endDate, fn($q) => $q->whereDate('trx_date', '<=', $endDate))
                            ->when($year, fn($q) => $q->whereYear('trx_date', $year)),
                        'total_zf_rice'
                    )
                    ->selectSub(
                        Zf::query()
                            ->selectRaw('COALESCE(SUM(total_muzakki), 0)')
                            ->join('unit_zis', 'zfs.unit_id', '=', 'unit_zis.id')
                            ->whereColumn('unit_zis.village_id', 'villages.id')
                            ->where('zf_amount', '>', 0)
                            ->when($startDate, fn($q) => $q->whereDate('trx_date', '>=', $startDate))
                            ->when($endDate, fn($q) => $q->whereDate('trx_date', '<=', $endDate))
                            ->when($year, fn($q) => $q->whereYear('trx_date', $year)),
                        'total_zf_muzakki_uang'
                    )
                    ->selectSub(
                        Zf::query()
                            ->selectRaw('COALESCE(SUM(total_muzakki), 0)')
                            ->join('unit_zis', 'zfs.unit_id', '=', 'unit_zis.id')
                            ->whereColumn('unit_zis.village_id', 'villages.id')
                            ->where('zf_rice', '>', 0)
                            ->when($startDate, fn($q) => $q->whereDate('trx_date', '>=', $startDate))
                            ->when($endDate, fn($q) => $q->whereDate('trx_date', '<=', $endDate))
                            ->when($year, fn($q) => $q->whereYear('trx_date', $year)),
                        'total_zf_muzakki_beras'
                    )
                    ->selectSub(
                        Zm::query()
                            ->selectRaw('COALESCE(SUM(amount), 0)')
                            ->join('unit_zis', 'zms.unit_id', '=', 'unit_zis.id')
                            ->whereColumn('unit_zis.village_id', 'villages.id')
                            ->when($startDate, fn($q) => $q->whereDate('trx_date', '>=', $startDate))
                            ->when($endDate, fn($q) => $q->whereDate('trx_date', '<=', $endDate))
                            ->when($year, fn($q) => $q->whereYear('trx_date', $year)),
                        'total_zm_amount'
                    )
                    ->selectSub(
                        Zm::query()
                            ->selectRaw('COUNT(*)')
                            ->join('unit_zis', 'zms.unit_id', '=', 'unit_zis.id')
                            ->whereColumn('unit_zis.village_id', 'villages.id')
                            ->when($startDate, fn($q) => $q->whereDate('trx_date', '>=', $startDate))
                            ->when($endDate, fn($q) => $q->whereDate('trx_date', '<=', $endDate))
                            ->when($year, fn($q) => $q->whereYear('trx_date', $year)),
                        'total_zm_muzakki'
                    )
                    ->selectSub(
                        Ifs::query()
                            ->selectRaw('COALESCE(SUM(amount), 0)')
                            ->join('unit_zis', 'ifs.unit_id', '=', 'unit_zis.id')
                            ->whereColumn('unit_zis.village_id', 'villages.id')
                            ->when($startDate, fn($q) => $q->whereDate('trx_date', '>=', $startDate))
                            ->when($endDate, fn($q) => $q->whereDate('trx_date', '<=', $endDate))
                            ->when($year, fn($q) => $q->whereYear('trx_date', $year)),
                        'total_ifs_amount'
                    )
                    ->selectSub(
                        Ifs::query()
                            ->selectRaw('COUNT(*)')
                            ->join('unit_zis', 'ifs.unit_id', '=', 'unit_zis.id')
                            ->whereColumn('unit_zis.village_id', 'villages.id')
                            ->when($startDate, fn($q) => $q->whereDate('trx_date', '>=', $startDate))
                            ->when($endDate, fn($q) => $q->whereDate('trx_date', '<=', $endDate))
                            ->when($year, fn($q) => $q->whereYear('trx_date', $year)),
                        'total_ifs_munfiq'
                    )
                    ->orderBy('name');

                // Apply role-based scope for upz_kecamatan
                if (User::currentIsUpzKecamatan() && $user->district_id) {
                    $query->where('district_id', $user->district_id);
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Desa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('district.name')
                    ->label('Kecamatan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_zf_amount')
                    ->label('ZF (Uang)')
                    ->numeric()
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_zf_rice')
                    ->label('ZF Beras (kg)')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' kg')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_zf_muzakki_uang')
                    ->label('Muzakki ZF Uang')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_zf_muzakki_beras')
                    ->label('Muzakki ZF Beras')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_zm_amount')
                    ->label('Zakat Mal')
                    ->numeric()
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_zm_muzakki')
                    ->label('Muzakki Mal')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_ifs_amount')
                    ->label('Infak')
                    ->numeric()
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_ifs_munfiq')
                    ->label('Munfiq')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_uang')
                    ->label('Total Uang')
                    ->getStateUsing(fn($record) => ($record->total_zf_amount ?? 0) + ($record->total_zm_amount ?? 0) + ($record->total_ifs_amount ?? 0))
                    ->numeric()
                    ->money('IDR', locale: 'id'),
                Tables\Columns\TextColumn::make('total_muzakki_munfiq')
                    ->label('Total Muzakki/Munfiq')
                    ->getStateUsing(fn($record) => ($record->total_zf_muzakki_uang ?? 0) + ($record->total_zf_muzakki_beras ?? 0) + ($record->total_zm_muzakki ?? 0) + ($record->total_ifs_munfiq ?? 0))
                    ->numeric(),
                SelectFilter::make('district_id')
                    ->label('Kecamatan')
                    ->options(fn() => $this->getDistrictOptions())
                    ->searchable()
                    ->visible(fn() => !User::currentIsUpzKecamatan()),
            ])
            ->defaultSort('name')
            ->striped();
    }

    protected function getDistrictOptions(): array
    {
        return District::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
