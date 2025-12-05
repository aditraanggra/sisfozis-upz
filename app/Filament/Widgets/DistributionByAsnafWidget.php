<?php

namespace App\Filament\Widgets;

use App\Models\Distribution;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Str;

class DistributionByAsnafWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Pendistribusian per Asnaf';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $year = $this->filters['year'] ?? null;

        $data = Distribution::query()
            ->selectRaw('asnaf, SUM(total_amount) as total_amount, SUM(total_rice) as total_rice')
            ->when($startDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '>=', $startDate))
            ->when($endDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '<=', $endDate))
            ->when($year, fn(EloquentBuilder $query) => $query->whereYear('trx_date', $year))
            ->whereNotNull('asnaf')
            ->groupBy('asnaf')
            ->orderBy('asnaf')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Pendistribusian Dana (Rp)',
                    'data' => $data->pluck('total_amount')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Pendistribusian Beras (Kg)',
                    'data' => $data->pluck('total_rice')->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $data->pluck('asnaf')->map(fn($asnaf) => Str::title($asnaf))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Dana (Rp)',
                    ],
                    'ticks' => [
                        'callback' => "function(value) { return 'Rp ' + value.toLocaleString('id-ID'); }",
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Beras (Kg)',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                    'ticks' => [
                        'callback' => "function(value) { return value + ' Kg'; }",
                    ],
                ],
            ],
        ];
    }
}
