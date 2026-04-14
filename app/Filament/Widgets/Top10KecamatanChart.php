<?php

namespace App\Filament\Widgets;

use App\Models\RekapZis;
use App\Models\User;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Collection;

class Top10KecamatanChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Top 5 Kecamatan - Penerimaan ZIS Tertinggi';

    protected static ?string $description = 'Total penerimaan: Zakat Fitrah (Uang) + Zakat Mal + Infak';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '400px';

    public static function canView(): bool
    {
        return User::currentIsSuperAdmin() || User::currentIsTimSisfo();
    }

    protected function getData(): array
    {
        $topKecamatan = $this->getTopKecamatanData();

        if ($topKecamatan->isEmpty()) {
            return [
                'datasets' => [],
                'labels'   => [],
            ];
        }

        $labels = $topKecamatan->pluck('district_name')->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'Total Uang (Rp)',
                    'data'            => $topKecamatan->pluck('total_zis')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor'     => '#2563eb',
                    'borderWidth'     => 1,
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => 'Total Beras (kg)',
                    'data'            => $topKecamatan->pluck('total_beras')->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                    'borderColor'     => '#059669',
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

    private function getTopKecamatanData(): Collection
    {
        $year = $this->filters['year'] ?? now()->year;

        return RekapZis::query()
            ->select('districts.name as district_name')
            ->selectRaw('SUM(COALESCE(rekap_zis.total_zf_rice, 0)) as total_beras')
            ->selectRaw('SUM(COALESCE(rekap_zis.total_zf_amount, 0) + COALESCE(rekap_zis.total_zm_amount, 0) + COALESCE(rekap_zis.total_ifs_amount, 0)) as total_zis')
            ->join('unit_zis', 'rekap_zis.unit_id', '=', 'unit_zis.id')
            ->join('districts', 'unit_zis.district_id', '=', 'districts.id')
            ->where('rekap_zis.period', 'tahunan')
            ->whereYear('rekap_zis.period_date', $year)
            ->groupBy('districts.id', 'districts.name')
            ->havingRaw('SUM(COALESCE(rekap_zis.total_zf_amount, 0) + COALESCE(rekap_zis.total_zm_amount, 0) + COALESCE(rekap_zis.total_ifs_amount, 0)) > 0')
            ->orderByDesc('total_zis')
            ->limit(5)
            ->get();
    }
}
