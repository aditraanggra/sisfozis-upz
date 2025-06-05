<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class AllZisOverview extends BaseWidget
{

    use InteractsWithPageFilters;

    protected function getStats(): array
    {

        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $year = $this->filters['year'] ?? null;

        return [
            //
            Stat::make(
                'Total Penerimaan Zakat Fitrah',
                'Rp ' . number_format(
                    \App\Models\Zf::query()
                        ->when(
                            $startDate,
                            fn(EloquentBuilder $query) => $query->whereDate('trx_date', '>=', $startDate)
                        )
                        ->when(
                            $endDate,
                            fn(EloquentBuilder $query) => $query->whereDate('trx_date', '<=', $endDate)
                        )
                        ->when(
                            $year,
                            fn(EloquentBuilder $query) => $query->whereYear('trx_date', $year)
                        )
                        ->sum('zf_amount')
                        +
                        \App\Models\Zf::join('unit_zis', 'zfs.unit_id', '=', 'unit_zis.id')
                        ->when(
                            $startDate,
                            fn(EloquentBuilder $query) => $query->whereDate('zfs.trx_date', '>=', $startDate)
                        )
                        ->when(
                            $endDate,
                            fn(EloquentBuilder $query) => $query->whereDate('zfs.trx_date', '<=', $endDate)
                        )
                        ->when(
                            $year,
                            fn(EloquentBuilder $query) => $query->whereYear('zfs.trx_date', $year)
                        )
                        ->selectRaw('COALESCE(SUM(zfs.zf_rice * unit_zis.rice_price), 0) as total_rice_value')
                        ->value('total_rice_value'),
                    0,
                    ',',
                    '.'
                )
            )
                //->description('Total Penerimaan Zakat Fitrah Uang')
                //->descriptionIcon('heroicon-m-banknotes')

                ->color('primary'),
            Stat::make(
                'Total Penerimaan Zakat Mal',
                'Rp ' . number_format(
                    \App\Models\Zm::query()
                        ->when(
                            $startDate,
                            fn(EloquentBuilder $query) => $query->whereDate('trx_date', '>=', $startDate)
                        )
                        ->when(
                            $endDate,
                            fn(EloquentBuilder $query) => $query->whereDate('trx_date', '<=', $endDate)
                        )
                        ->when(
                            $year,
                            fn(EloquentBuilder $query) => $query->whereYear('trx_date', $year)
                        )
                        ->sum('amount')
                )
            )
                //->description('Total Penerimaan Zakat Mal')
                //->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
            Stat::make(
                'Total Penerimaan Infaq/Shodaqoh',
                'Rp ' . number_format(
                    \App\Models\Ifs::query()
                        ->when(
                            $startDate,
                            fn(EloquentBuilder $query) => $query->whereDate('trx_date', '>=', $startDate)
                        )
                        ->when(
                            $endDate,
                            fn(EloquentBuilder $query) => $query->whereDate('trx_date', '<=', $endDate)
                        )
                        ->when(
                            $year,
                            fn(EloquentBuilder $query) => $query->whereYear('trx_date', $year)
                        )
                        ->sum('amount')
                )
            )
                //->description('Total Penerimaan Infaq/Shodaqoh')
                //->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
        ];
    }
}
