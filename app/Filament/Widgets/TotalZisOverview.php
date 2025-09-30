<?php

namespace App\Filament\Widgets;

use App\Models\Ifs;
use App\Models\Zf;
use App\Models\Zm;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Carbon;

class TotalZisOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $year = $this->filters['year'] ?? null;

        $currentTotal = $this->calculateTotal($startDate, $endDate, $year);

        $comparisonReady = $year !== null || $startDate !== null || $endDate !== null;

        [$previousStart, $previousEnd, $previousYear] = $comparisonReady
            ? $this->resolvePreviousPeriod($startDate, $endDate, $year)
            : [null, null, null];

        $previousTotal = $comparisonReady
            ? $this->calculateTotal($previousStart, $previousEnd, $previousYear)
            : null;

        [$description, $icon, $descriptionColor] = $this->formatComparisonDescription(
            $currentTotal,
            $previousTotal,
            $comparisonReady
        );

        return [
            Stat::make(
                'Total Penerimaan ZIS',
                'Rp ' . number_format($currentTotal, 0, ',', '.')
            )
                ->chart($this->getMonthlyTotals($startDate, $endDate, $year))
                ->description($description)
                ->descriptionIcon($icon)
                ->descriptionColor($descriptionColor)
                ->color('primary'),
        ];
    }

    protected function getColumns(): int
    {
        return 1;
    }

    protected function calculateTotal(?string $startDate, ?string $endDate, ?string $year): float
    {
        $totalZakatFitrahMoney = Zf::query()
            ->when($startDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '>=', $startDate))
            ->when($endDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '<=', $endDate))
            ->when($year, fn(EloquentBuilder $query) => $query->whereYear('trx_date', $year))
            ->sum('zf_amount');

        $totalZakatFitrahRice = Zf::join('unit_zis', 'zfs.unit_id', '=', 'unit_zis.id')
            ->when($startDate, fn(EloquentBuilder $query) => $query->whereDate('zfs.trx_date', '>=', $startDate))
            ->when($endDate, fn(EloquentBuilder $query) => $query->whereDate('zfs.trx_date', '<=', $endDate))
            ->when($year, fn(EloquentBuilder $query) => $query->whereYear('zfs.trx_date', $year))
            ->selectRaw('COALESCE(SUM(zfs.zf_rice * unit_zis.rice_price), 0) as total_rice_value')
            ->value('total_rice_value');

        $totalZakatMal = Zm::query()
            ->when($startDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '>=', $startDate))
            ->when($endDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '<=', $endDate))
            ->when($year, fn(EloquentBuilder $query) => $query->whereYear('trx_date', $year))
            ->sum('amount');

        $totalInfaqShodaqoh = Ifs::query()
            ->when($startDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '>=', $startDate))
            ->when($endDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '<=', $endDate))
            ->when($year, fn(EloquentBuilder $query) => $query->whereYear('trx_date', $year))
            ->sum('amount');

        return (float) ($totalZakatFitrahMoney + ($totalZakatFitrahRice ?? 0) + $totalZakatMal + $totalInfaqShodaqoh);
    }

    protected function resolvePreviousPeriod(?string $startDate, ?string $endDate, ?string $year): array
    {
        if ($year !== null) {
            $previousYear = (int) $year - 1;

            return [
                $startDate ? Carbon::parse($startDate)->subYear()->toDateString() : null,
                $endDate ? Carbon::parse($endDate)->subYear()->toDateString() : null,
                $previousYear > 0 ? (string) $previousYear : null,
            ];
        }

        if ($startDate !== null || $endDate !== null) {
            return [
                $startDate ? Carbon::parse($startDate)->subYear()->toDateString() : null,
                $endDate ? Carbon::parse($endDate)->subYear()->toDateString() : null,
                null,
            ];
        }

        $currentYear = Carbon::now()->year;

        return [null, null, (string) ($currentYear - 1)];
    }

    protected function formatComparisonDescription(
        float $currentTotal,
        ?float $previousTotal,
        bool $comparisonReady
    ): array {
        if (! $comparisonReady) {
            return [
                'Tambahkan filter tahun atau tanggal untuk membandingkan dengan tahun lalu',
                'heroicon-m-information-circle',
                'secondary',
            ];
        }

        if ($previousTotal === null) {
            return [
                'Data tahun lalu tidak tersedia',
                'heroicon-m-information-circle',
                'secondary',
            ];
        }

        if ($previousTotal <= 0.0) {
            if ($currentTotal <= 0.0) {
                return [
                    'Belum ada penerimaan pada kedua tahun',
                    'heroicon-m-minus',
                    'secondary',
                ];
            }

            return [
                'Naik dari nihil pada tahun lalu',
                'heroicon-m-arrow-trending-up',
                'success',
            ];
        }

        $difference = $currentTotal - $previousTotal;

        if ($difference > 0.0) {
            $percentage = ($difference / $previousTotal) * 100;

            return [
                'Naik ' . number_format($percentage, 2, ',', '.') . '% dibanding tahun lalu',
                'heroicon-m-arrow-trending-up',
                'success',
            ];
        }

        if ($difference < 0.0) {
            $percentage = abs($difference) / $previousTotal * 100;

            return [
                'Turun ' . number_format($percentage, 2, ',', '.') . '% dibanding tahun lalu',
                'heroicon-m-arrow-trending-down',
                'danger',
            ];
        }

        return [
            'Tetap dibanding tahun lalu',
            'heroicon-m-minus',
            'secondary',
        ];
    }

    protected function getMonthlyTotals(?string $startDate, ?string $endDate, ?string $year): array
    {
        $monthlyTotals = array_fill(0, 12, 0.0);

        $zakatFitrahData = Zf::query()
            ->join('unit_zis', 'zfs.unit_id', '=', 'unit_zis.id')
            ->selectRaw('
                EXTRACT(MONTH FROM zfs.trx_date) as month,
                SUM(zfs.zf_amount + zfs.zf_rice * unit_zis.rice_price) as total
            ')
            ->when($startDate, fn(EloquentBuilder $query) => $query->whereDate('zfs.trx_date', '>=', $startDate))
            ->when($endDate, fn(EloquentBuilder $query) => $query->whereDate('zfs.trx_date', '<=', $endDate))
            ->when($year, fn(EloquentBuilder $query) => $query->whereYear('zfs.trx_date', $year))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        foreach ($zakatFitrahData as $item) {
            $monthIndex = ((int) $item->month) - 1;

            if ($monthIndex < 0 || $monthIndex > 11) {
                continue;
            }

            $monthlyTotals[$monthIndex] += (float) $item->total;
        }

        $zakatMalData = Zm::query()
            ->selectRaw('EXTRACT(MONTH FROM trx_date) as month, SUM(amount) as total')
            ->when($startDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '>=', $startDate))
            ->when($endDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '<=', $endDate))
            ->when($year, fn(EloquentBuilder $query) => $query->whereYear('trx_date', $year))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        foreach ($zakatMalData as $item) {
            $monthIndex = ((int) $item->month) - 1;

            if ($monthIndex < 0 || $monthIndex > 11) {
                continue;
            }

            $monthlyTotals[$monthIndex] += (float) $item->total;
        }

        $infaqData = Ifs::query()
            ->selectRaw('EXTRACT(MONTH FROM trx_date) as month, SUM(amount) as total')
            ->when($startDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '>=', $startDate))
            ->when($endDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '<=', $endDate))
            ->when($year, fn(EloquentBuilder $query) => $query->whereYear('trx_date', $year))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        foreach ($infaqData as $item) {
            $monthIndex = ((int) $item->month) - 1;

            if ($monthIndex < 0 || $monthIndex > 11) {
                continue;
            }

            $monthlyTotals[$monthIndex] += (float) $item->total;
        }

        return array_map(static fn(float $value) => round($value, 2), $monthlyTotals);
    }
}
