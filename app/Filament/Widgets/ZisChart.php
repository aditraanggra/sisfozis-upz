<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ZisChart extends ChartWidget
{

    use InteractsWithPageFilters;

    protected static ?string $heading = 'Grafik Penerimaan Dana ZIS (Zakat, Infaq, Sedekah)';

    protected function getData(): array
    {


        // Inisialisasi array 12 bulan dengan nilai 0
        $initMonthlyData = array_fill(0, 12, 0);

        return [
            'datasets' => [
                [
                    'label' => 'Zakat Mal',
                    'data' => $this->getMonthlyData(\App\Models\Zm::class, 'amount', $initMonthlyData),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
                [
                    'label' => 'Zakat Fitrah',
                    'data' => $this->getMonthlyDataZakatFitrah($initMonthlyData),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
                [
                    'label' => 'Infaq',
                    'data' => $this->getMonthlyData(\App\Models\Ifs::class, 'amount', $initMonthlyData),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    private function getMonthlyData(string $model, string $amountField, array $initData): array
    {
        $monthlyData = $initData;
        $year = $this->filters['year'] ?? null;

        $data = $model::selectRaw("EXTRACT(MONTH FROM trx_date) as month, SUM({$amountField}) as total")
            ->when($year, fn($query) => $query->whereYear('trx_date', $year))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        foreach ($data as $item) {
            // Index array dimulai dari 0, sedangkan bulan dimulai dari 1
            $monthIndex = (int)$item->month - 1;
            $monthlyData[$monthIndex] = (int)$item->total;
        }

        return $monthlyData;
    }

    private function getMonthlyDataZakatFitrah(array $initData): array
    {
        $monthlyData = $initData;
        $year = $this->filters['year'] ?? null;

        $data = \App\Models\Zf::query()
            ->selectRaw("
            EXTRACT(MONTH FROM zfs.trx_date) as month,
            SUM(zfs.zf_amount + zfs.zf_rice * unit_zis.rice_price) as total
        ")
            ->when($year, fn($query) => $query->whereYear('trx_date', $year))
            ->join('unit_zis', 'zfs.unit_id', '=', 'unit_zis.id')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        foreach ($data as $item) {
            $monthIndex = (int)$item->month - 1;
            $monthlyData[$monthIndex] = (int)$item->total;
        }

        return $monthlyData;
    }


    protected function getType(): string
    {
        return 'line';
    }
}
