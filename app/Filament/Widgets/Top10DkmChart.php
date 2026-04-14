<?php

namespace App\Filament\Widgets;

use App\Models\RekapZis;
use App\Models\User;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Top10DkmChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Top 5 UPZ DKM - Penerimaan ZIS Tertinggi';

    protected static ?string $description = 'Total penerimaan: Zakat Fitrah (Uang) + Zakat Mal + Infak';

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '400px';

    private const DKM_CATEGORY_ID = 4;

    protected function getData(): array
    {
        $topDkm = $this->getTopDkmData();

        if ($topDkm->isEmpty()) {
            return [
                'datasets' => [],
                'labels'   => [],
            ];
        }

        $labels = $topDkm->pluck('unit_name')->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'Total Uang (Rp)',
                    'data'            => $topDkm->pluck('total_zis')->toArray(),
                    'backgroundColor' => 'rgba(139, 92, 246, 0.8)',
                    'borderColor'     => '#7c3aed',
                    'borderWidth'     => 1,
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => 'Total Beras (kg)',
                    'data'            => $topDkm->pluck('total_beras')->toArray(),
                    'backgroundColor' => 'rgba(6, 182, 212, 0.8)',
                    'borderColor'     => '#0891b2',
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

    private function getTopDkmData(): Collection
    {
        $user = Auth::user();
        $year = $this->filters['year'] ?? now()->year;

        return RekapZis::query()
            ->select('unit_zis.unit_name')
            ->selectRaw('SUM(COALESCE(rekap_zis.total_zf_rice, 0)) as total_beras')
            ->selectRaw('SUM(COALESCE(rekap_zis.total_zf_amount, 0) + COALESCE(rekap_zis.total_zm_amount, 0) + COALESCE(rekap_zis.total_ifs_amount, 0)) as total_zis')
            ->join('unit_zis', 'rekap_zis.unit_id', '=', 'unit_zis.id')
            ->where('rekap_zis.period', 'tahunan')
            ->whereYear('rekap_zis.period_date', $year)
            ->where('unit_zis.category_id', self::DKM_CATEGORY_ID)
            ->when(
                User::currentIsUpzKecamatan() && $user?->district_id,
                fn($q) => $q->where('unit_zis.district_id', $user->district_id)
            )
            ->when(
                User::currentIsUpzDesa() && $user?->village_id,
                fn($q) => $q->where('unit_zis.village_id', $user->village_id)
            )
            ->groupBy('unit_zis.id', 'unit_zis.unit_name')
            ->havingRaw('SUM(COALESCE(rekap_zis.total_zf_amount, 0) + COALESCE(rekap_zis.total_zm_amount, 0) + COALESCE(rekap_zis.total_ifs_amount, 0)) > 0')
            ->orderByDesc('total_zis')
            ->limit(5)
            ->get();
    }
}
