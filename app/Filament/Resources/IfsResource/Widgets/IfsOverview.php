<?php

namespace App\Filament\Resources\IfsResource\Widgets;

use App\Models\Ifs;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\Reactive;

class IfsOverview extends BaseWidget
{
    #[Reactive]
    public ?array $tableFilters = null;

    protected function getStats(): array
    {
        $year = $this->tableFilters['trx_year']['value'] ?? null;

        $baseQuery = Ifs::query();

        if ($year) {
            $baseQuery->whereYear('trx_date', $year);
        }

        $totalAmount = (clone $baseQuery)->sum('amount');
        $totalMunfik = (clone $baseQuery)->count('munfiq_name');

        return [
            Stat::make('Total Infak Sedekah', 'Rp ' . number_format($totalAmount, 0, ',', '.')),
            Stat::make('Total Munfik', $totalMunfik),
        ];
    }
}
