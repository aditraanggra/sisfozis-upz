<?php

namespace App\Filament\Widgets;

use App\Models\RekapZis;
use App\Models\User;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Top10DesaChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Top 5 Desa - Penerimaan ZIS Tertinggi';

    protected static ?string $description = 'Total penerimaan: Zakat Fitrah (Uang) + Zakat Mal + Infak';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '400px';

    protected function getData(): array
    {
        $topDesa = $this->getTopDesaData();

        if ($topDesa->isEmpty()) {
            return [
                'datasets' => [],
                'labels'   => [],
            ];
        }

        $labels = $topDesa->pluck('village_name')->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'Total Uang (Rp)',
                    'data'            => $topDesa->pluck('total_zis')->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                    'borderColor'     => '#059669',
                    'borderWidth'     => 1,
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => 'Total Beras (kg)',
                    'data'            => $topDesa->pluck('total_beras')->toArray(),
                    'backgroundColor' => 'rgba(245, 158, 11, 0.8)',
                    'borderColor'     => '#d97706',
                    'borderWidth'     => 1,
                    'yAxisID'         => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
            {
                plugins: {
                    legend: { display: true },
                    tooltip: { enabled: true },
                },
                scales: {
                    x: {
                        ticks: { font: { size: 12 } },
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        title: { display: true, text: 'Uang (Rp)' },
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            },
                        },
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        title: { display: true, text: 'Beras (kg)' },
                        grid: { drawOnChartArea: false },
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('id-ID').format(value) + ' kg';
                            },
                        },
                    },
                },
                maintainAspectRatio: false,
            }
        JS);
    }

    private function getTopDesaData(): Collection
    {
        $user = Auth::user();
        $year = $this->filters['year'] ?? now()->year;

        return RekapZis::query()
            ->select('villages.name as village_name')
            ->selectRaw('SUM(COALESCE(rekap_zis.total_zf_rice, 0)) as total_beras')
            ->selectRaw('SUM(COALESCE(rekap_zis.total_zf_amount, 0) + COALESCE(rekap_zis.total_zm_amount, 0) + COALESCE(rekap_zis.total_ifs_amount, 0)) as total_zis')
            ->join('unit_zis', 'rekap_zis.unit_id', '=', 'unit_zis.id')
            ->join('villages', 'unit_zis.village_id', '=', 'villages.id')
            ->where('rekap_zis.period', 'tahunan')
            ->whereYear('rekap_zis.period_date', $year)
            ->whereNotNull('unit_zis.village_id')
            ->when(
                User::currentIsUpzKecamatan() && $user?->district_id,
                fn($q) => $q->where('unit_zis.district_id', $user->district_id)
            )
            ->when(
                User::currentIsUpzDesa() && $user?->village_id,
                fn($q) => $q->where('unit_zis.village_id', $user->village_id)
            )
            ->groupBy('villages.id', 'villages.name')
            ->havingRaw('SUM(COALESCE(rekap_zis.total_zf_amount, 0) + COALESCE(rekap_zis.total_zm_amount, 0) + COALESCE(rekap_zis.total_ifs_amount, 0)) > 0')
            ->orderByDesc('total_zis')
            ->limit(5)
            ->get();
    }
}
