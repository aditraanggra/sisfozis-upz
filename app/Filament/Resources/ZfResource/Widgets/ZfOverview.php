<?php

namespace App\Filament\Resources\ZfResource\Widgets;

use App\Models\Zf;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\Reactive;

class ZfOverview extends BaseWidget
{
    #[Reactive]
    public ?array $tableFilters = null;

    protected function getStats(): array
    {
        $year = $this->tableFilters['trx_year']['value'] ?? null;

        $baseQuery = Zf::query();

        if ($year) {
            $baseQuery->whereYear('trx_date', $year);
        }

        $totalAmount = (clone $baseQuery)->sum('zf_amount');
        $totalRice = (clone $baseQuery)->sum('zf_rice');
        $totalMuzakki = (clone $baseQuery)->sum('total_muzakki');

        return [
            Stat::make('Total Zakat Fitrah Uang', 'Rp.' . number_format($totalAmount, 0, ',', '.')),
            Stat::make('Total Zakat Fitrah Beras (Kg)', number_format($totalRice, 2, ',', '.') . ' Kg'),
            Stat::make('Total Muzakki', $totalMuzakki),
        ];
    }
}
