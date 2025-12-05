<?php

namespace App\Filament\Widgets;

use App\Models\Distribution;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DistributionStatsWidget extends BaseWidget
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

        // Total Dana Distribusi
        $totalAmountCurrent = $this->calculateAmount($startDate, $endDate, $year);
        $totalAmountPrevious = $comparisonReady
            ? $this->calculateAmount($previousStart, $previousEnd, $previousYear)
            : null;
        [$amountDescription, $amountIcon, $amountColor] = $this->formatComparisonDescription(
            $totalAmountCurrent,
            $totalAmountPrevious,
            $comparisonReady
        );

        // Total Beras Distribusi
        $totalRiceCurrent = $this->calculateRice($startDate, $endDate, $year);
        $totalRicePrevious = $comparisonReady
            ? $this->calculateRice($previousStart, $previousEnd, $previousYear)
            : null;
        [$riceDescription, $riceIcon, $riceColor] = $this->formatComparisonDescription(
            $totalRiceCurrent,
            $totalRicePrevious,
            $comparisonReady
        );

        // Total Mustahik (Penerima)
        $totalBeneficiariesCurrent = $this->calculateBeneficiaries($startDate, $endDate, $year);
        $totalBeneficiariesPrevious = $comparisonReady
            ? $this->calculateBeneficiaries($previousStart, $previousEnd, $previousYear)
            : null;
        [$beneficiariesDescription, $beneficiariesIcon, $beneficiariesColor] = $this->formatComparisonDescription(
            $totalBeneficiariesCurrent,
            $totalBeneficiariesPrevious,
            $comparisonReady
        );

        return [
            Stat::make('Total Pendistribusian (Uang)', 'Rp ' . number_format($totalAmountCurrent, 0, ',', '.'))
                ->description($amountDescription)
                ->descriptionIcon($amountIcon)
                ->color($amountColor),

            Stat::make('Total Pendistribusian (Beras)', number_format($totalRiceCurrent, 2, ',', '.') . ' Kg')
                ->description($riceDescription)
                ->descriptionIcon($riceIcon)
                ->color($riceColor),

            Stat::make('Total Penerima Manfaat', number_format($totalBeneficiariesCurrent, 0, ',', '.'))
                ->description($beneficiariesDescription)
                ->descriptionIcon($beneficiariesIcon)
                ->color($beneficiariesColor),
        ];
    }

    protected function calculateAmount(?string $startDate, ?string $endDate, ?string $year): float
    {
        return (float) Distribution::query()
            ->when($startDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '>=', $startDate))
            ->when($endDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '<=', $endDate))
            ->when($year, fn(EloquentBuilder $query) => $query->whereYear('trx_date', $year))
            ->sum('total_amount');
    }

    protected function calculateRice(?string $startDate, ?string $endDate, ?string $year): float
    {
        return (float) Distribution::query()
            ->when($startDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '>=', $startDate))
            ->when($endDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '<=', $endDate))
            ->when($year, fn(EloquentBuilder $query) => $query->whereYear('trx_date', $year))
            ->sum('total_rice');
    }

    protected function calculateBeneficiaries(?string $startDate, ?string $endDate, ?string $year): float
    {
        return (float) Distribution::query()
            ->when($startDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '>=', $startDate))
            ->when($endDate, fn(EloquentBuilder $query) => $query->whereDate('trx_date', '<=', $endDate))
            ->when($year, fn(EloquentBuilder $query) => $query->whereYear('trx_date', $year))
            ->sum('beneficiary');
    }

    protected function resolvePreviousPeriod(?string $startDate, ?string $endDate, ?string $year): array
    {
        if ($year !== null) {
            $previousYear = (int) $year - 1;

            return [
                $startDate ? $this->parseAndSubYear($startDate) : null,
                $endDate ? $this->parseAndSubYear($endDate) : null,
                $previousYear > 0 ? (string) $previousYear : null,
            ];
        }

        if ($startDate !== null || $endDate !== null) {
            return [
                $startDate ? $this->parseAndSubYear($startDate) : null,
                $endDate ? $this->parseAndSubYear($endDate) : null,
                null,
            ];
        }

        $currentYear = Carbon::now()->year;

        return [null, null, (string) ($currentYear - 1)];
    }

    protected function parseAndSubYear(?string $dateString): ?string
    {
        if ($dateString === null) {
            return null;
        }

        try {
            return Carbon::parse($dateString)->subYear()->toDateString();
        } catch (\Exception $e) {
            Log::warning('Invalid date string for parsing: ' . $dateString, ['error' => $e->getMessage()]);

            return null;
        }
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
