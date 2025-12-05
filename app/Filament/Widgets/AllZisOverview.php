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

class AllZisOverview extends BaseWidget
{

    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $year = $this->filters['year'] ?? null;

        $comparisonReady = $year !== null || $startDate !== null || $endDate !== null;

        [$previousStart, $previousEnd, $previousYear] = $comparisonReady
            ? $this->resolvePreviousPeriod($startDate, $endDate, $year)
            : [null, null, null];

        $zakatFitrahCurrent = $this->calculateZakatFitrahTotal($startDate, $endDate, $year);
        $zakatFitrahPrevious = $comparisonReady
            ? $this->calculateZakatFitrahTotal($previousStart, $previousEnd, $previousYear)
            : null;
        [$zakatFitrahDescription, $zakatFitrahIcon, $zakatFitrahColor] = $this->formatComparisonDescription(
            $zakatFitrahCurrent,
            $zakatFitrahPrevious,
            $comparisonReady
        );

        $zakatMalCurrent = $this->calculateZakatMalTotal($startDate, $endDate, $year);
        $zakatMalPrevious = $comparisonReady
            ? $this->calculateZakatMalTotal($previousStart, $previousEnd, $previousYear)
            : null;
        [$zakatMalDescription, $zakatMalIcon, $zakatMalColor] = $this->formatComparisonDescription(
            $zakatMalCurrent,
            $zakatMalPrevious,
            $comparisonReady
        );

        $infaqCurrent = $this->calculateInfaqTotal($startDate, $endDate, $year);
        $infaqPrevious = $comparisonReady
            ? $this->calculateInfaqTotal($previousStart, $previousEnd, $previousYear)
            : null;
        [$infaqDescription, $infaqIcon, $infaqColor] = $this->formatComparisonDescription(
            $infaqCurrent,
            $infaqPrevious,
            $comparisonReady
        );

        return [
            Stat::make(
                'Total Penerimaan Zakat Fitrah',
                'Rp ' . number_format($zakatFitrahCurrent, 0, ',', '.')
            )
                ->description($zakatFitrahDescription)
                ->descriptionIcon($zakatFitrahIcon)
                ->descriptionColor($zakatFitrahColor)
                ->color('primary'),
            Stat::make(
                'Total Penerimaan Zakat Mal',
                'Rp ' . number_format($zakatMalCurrent, 0, ',', '.')
            )
                ->description($zakatMalDescription)
                ->descriptionIcon($zakatMalIcon)
                ->descriptionColor($zakatMalColor)
                ->color('primary'),
            Stat::make(
                'Total Penerimaan Infaq/Shodaqoh',
                'Rp ' . number_format($infaqCurrent, 0, ',', '.')
            )
                ->description($infaqDescription)
                ->descriptionIcon($infaqIcon)
                ->descriptionColor($infaqColor)
                ->color('primary'),
        ];
    }

    protected function calculateZakatFitrahTotal(?string $startDate, ?string $endDate, ?string $year): float
    {
        return (float) Zf::query()
            ->when($startDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '>=', $startDate))
            ->when($endDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '<=', $endDate))
            ->when($year, fn(EloquentBuilder $query) => $query->whereYear('trx_date', $year))
            ->sum('zf_amount');
    }

    protected function calculateZakatMalTotal(?string $startDate, ?string $endDate, ?string $year): float
    {
        return (float) Zm::query()
            ->when($startDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '>=', $startDate))
            ->when($endDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '<=', $endDate))
            ->when($year, fn(EloquentBuilder $query) => $query->whereYear('trx_date', $year))
            ->sum('amount');
    }

    protected function calculateInfaqTotal(?string $startDate, ?string $endDate, ?string $year): float
    {
        return (float) Ifs::query()
            ->when($startDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '>=', $startDate))
            ->when($endDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '<=', $endDate))
            ->when($year, fn(EloquentBuilder $query) => $query->whereYear('trx_date', $year))
            ->sum('amount');
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
}
