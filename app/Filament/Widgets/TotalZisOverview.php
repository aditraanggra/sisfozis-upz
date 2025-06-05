<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;


class TotalZisOverview extends BaseWidget
{

    protected static ?int $sort = -2;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    use InteractsWithPageFilters;

    protected function getStats(): array
    {

        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $year = $this->filters['year'] ?? null;

        return [
            //
            Stat::make(
                'Total Penerimaan ZIS',
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
                        ->value('total_rice_value')
                        +
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
                        +
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
                        ->sum('amount'),
                    0,
                    ',',
                    '.'
                )
            )
                ->description('Total Penerimaan Zakat, Infak, dan Sedekah')
                ->descriptionIcon('heroicon-m-banknotes')

                //->chart(\App\Models\Zf::pluck('zf_amount')->toArray())
                ->color('primary'),
        ];
    }
}
