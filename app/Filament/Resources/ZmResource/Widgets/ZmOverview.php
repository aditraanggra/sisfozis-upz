<?php

namespace App\Filament\Resources\ZmResource\Widgets;

use App\Models\Zm;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\Reactive;

class ZmOverview extends BaseWidget
{
    #[Reactive]
    public ?array $tableFilters = null;

    protected function getStats(): array
    {
        $year = $this->tableFilters['trx_year']['value'] ?? null;

        $baseQuery = Zm::query();

        if ($year) {
            $baseQuery->whereYear('trx_date', $year);
        }

        $totalAmount = (clone $baseQuery)->sum('amount');
        $totalMuzakki = (clone $baseQuery)->count('muzakki_name');

        return [
            Stat::make('Total Zakat Mal', 'Rp ' . number_format($totalAmount, 0, ',', '.')),
            Stat::make('Total Muzakki', $totalMuzakki),
        ];
    }
}
