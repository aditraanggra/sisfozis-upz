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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ZisPerDesaTable extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    protected ?string $startDate = null;
    protected ?string $endDate = null;
    protected ?string $year = null;

    public function table(Table $table): Table
    {
        $this->startDate = $this->filters['startDate'] ?? null;
        $this->endDate = $this->filters['endDate'] ?? null;
        $this->year = $this->filters['year'] ?? null;
        $user = Auth::user();

        return $table
            ->heading('Penerimaan ZIS per Desa')
            ->query(
                Village::query()
                    ->select('villages.*')
                    ->with('district')
                    ->selectSub($this->getZfAmountSubquery(), 'total_zf_amount')
                    ->selectSub($this->getZfRiceSubquery(), 'total_zf_rice')
                    ->selectSub($this->getZfMuzakkiUangSubquery(), 'total_zf_muzakki_uang')
                    ->selectSub($this->getZfMuzakkiBerasSubquery(), 'total_zf_muzakki_beras')
                    ->selectSub($this->getZmAmountSubquery(), 'total_zm_amount')
                    ->selectSub($this->getZmMuzakkiSubquery(), 'total_zm_muzakki')
                    ->selectSub($this->getIfsAmountSubquery(), 'total_ifs_amount')
                    ->selectSub($this->getIfsMunfiqSubquery(), 'total_ifs_munfiq')
                    ->selectRaw($this->getTotalZisSubquery() . ' as total_zis')
                    ->when(
                        User::currentIsUpzKecamatan() && $user?->district_id,
                        fn($q) => $q->where('district_id', $user->district_id)
                    )
            )
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('district.name')
                    ->label('Kecamatan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Desa')
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
                Tables\Columns\TextColumn::make('total_zis')
                    ->label('Total Uang')
                    ->numeric()
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_muzakki_munfiq')
                    ->label('Total Muzakki/Munfiq')
                    ->getStateUsing(fn($record) => ($record->total_zf_muzakki_uang ?? 0) + ($record->total_zf_muzakki_beras ?? 0) + ($record->total_zm_muzakki ?? 0) + ($record->total_ifs_munfiq ?? 0))
                    ->numeric(),
            ])
            ->filters(
                User::currentIsUpzKecamatan() ? [] : [
                    SelectFilter::make('district_id')
                        ->label('Kecamatan')
                        ->options(fn() => District::orderBy('name')->pluck('name', 'id')->toArray())
                        ->searchable(),
                ]
            )
            ->defaultSort('total_zis', 'desc')
            ->striped();
    }

    protected function baseZfQuery(): Builder
    {
        return Zf::query()
            ->join('unit_zis', 'zfs.unit_id', '=', 'unit_zis.id')
            ->whereColumn('unit_zis.village_id', 'villages.id')
            ->when($this->startDate, fn($q) => $q->whereDate('trx_date', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('trx_date', '<=', $this->endDate))
            ->when($this->year, fn($q) => $q->whereYear('trx_date', $this->year));
    }

    protected function baseZmQuery(): Builder
    {
        return Zm::query()
            ->join('unit_zis', 'zms.unit_id', '=', 'unit_zis.id')
            ->whereColumn('unit_zis.village_id', 'villages.id')
            ->when($this->startDate, fn($q) => $q->whereDate('trx_date', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('trx_date', '<=', $this->endDate))
            ->when($this->year, fn($q) => $q->whereYear('trx_date', $this->year));
    }

    protected function baseIfsQuery(): Builder
    {
        return Ifs::query()
            ->join('unit_zis', 'ifs.unit_id', '=', 'unit_zis.id')
            ->whereColumn('unit_zis.village_id', 'villages.id')
            ->when($this->startDate, fn($q) => $q->whereDate('trx_date', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('trx_date', '<=', $this->endDate))
            ->when($this->year, fn($q) => $q->whereYear('trx_date', $this->year));
    }

    protected function getZfAmountSubquery(): Builder
    {
        return $this->baseZfQuery()->selectRaw('COALESCE(SUM(zf_amount), 0)');
    }

    protected function getZfRiceSubquery(): Builder
    {
        return $this->baseZfQuery()->selectRaw('COALESCE(SUM(zf_rice), 0)');
    }

    protected function getZfMuzakkiUangSubquery(): Builder
    {
        return $this->baseZfQuery()->where('zf_amount', '>', 0)->selectRaw('COALESCE(SUM(total_muzakki), 0)');
    }

    protected function getZfMuzakkiBerasSubquery(): Builder
    {
        return $this->baseZfQuery()->where('zf_rice', '>', 0)->selectRaw('COALESCE(SUM(total_muzakki), 0)');
    }

    protected function getZmAmountSubquery(): Builder
    {
        return $this->baseZmQuery()->selectRaw('COALESCE(SUM(amount), 0)');
    }

    protected function getZmMuzakkiSubquery(): Builder
    {
        return $this->baseZmQuery()->selectRaw('COUNT(*)');
    }

    protected function getIfsAmountSubquery(): Builder
    {
        return $this->baseIfsQuery()->selectRaw('COALESCE(SUM(amount), 0)');
    }

    protected function getIfsMunfiqSubquery(): Builder
    {
        return $this->baseIfsQuery()->selectRaw('COUNT(*)');
    }

    protected function getTotalZisSubquery(): string
    {
        $zfSub = $this->getZfAmountSubquery()->toRawSql();
        $zmSub = $this->getZmAmountSubquery()->toRawSql();
        $ifsSub = $this->getIfsAmountSubquery()->toRawSql();

        return "({$zfSub}) + ({$zmSub}) + ({$ifsSub})";
    }
}
