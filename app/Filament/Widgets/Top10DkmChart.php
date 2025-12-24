<?php

namespace App\Filament\Widgets;

use App\Models\RekapZis;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Top10DkmChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Top 10 UPZ DKM - Penerimaan ZIS Tertinggi';

    protected static ?string $description = 'Total penerimaan: Zakat Fitrah (Uang) + Zakat Mal + Infak';

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '500px';

    private const DKM_CATEGORY_ID = 4;

    private const COLORS = [
        'rgba(139, 92, 246, 0.8)',   // violet
        'rgba(59, 130, 246, 0.8)',   // blue
        'rgba(16, 185, 129, 0.8)',   // green
        'rgba(245, 158, 11, 0.8)',   // amber
        'rgba(239, 68, 68, 0.8)',    // red
        'rgba(236, 72, 153, 0.8)',   // pink
        'rgba(6, 182, 212, 0.8)',    // cyan
        'rgba(249, 115, 22, 0.8)',   // orange
        'rgba(34, 197, 94, 0.8)',    // emerald
        'rgba(168, 85, 247, 0.8)',   // purple
    ];

    private const BORDER_COLORS = [
        '#7c3aed',
        '#2563eb',
        '#059669',
        '#d97706',
        '#dc2626',
        '#db2777',
        '#0891b2',
        '#ea580c',
        '#16a34a',
        '#9333ea',
    ];

    protected function getData(): array
    {
        $topDkm = $this->getTopDkmData();

        if ($topDkm->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $count = $topDkm->count();

        return [
            'datasets' => [
                [
                    'label' => 'Total ZIS (Rp)',
                    'data' => $topDkm->pluck('total_zis')->toArray(),
                    'backgroundColor' => array_slice(self::COLORS, 0, $count),
                    'borderColor' => array_slice(self::BORDER_COLORS, 0, $count),
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $topDkm->map(fn($item) => $item->unit_name . ' (Beras: ' . number_format($item->total_beras ?? 0, 1, ',', '.') . ' kg)')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'stacked' => false,
                    'ticks' => [
                        'callback' => "function(value) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(value); }",
                    ],
                ],
                'y' => [
                    'stacked' => false,
                    'ticks' => [
                        'autoSkip' => false,
                        'font' => [
                            'size' => 11,
                        ],
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
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
            ->limit(10)
            ->get();
    }
}
