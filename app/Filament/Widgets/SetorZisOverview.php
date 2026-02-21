<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SetorZisOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getTablePage(): string
    {
        return \App\Filament\Resources\SetorZisResource\Pages\ListSetorZis::class;
    }

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();

        $totalDeposit = $query->sum('total_deposit');
        $totalZfAmount = $query->sum('zf_amount_deposit');
        $totalZfRice = $query->sum('zf_rice_deposit');
        $totalZmAmount = $query->sum('zm_amount_deposit');
        $totalIfsAmount = $query->sum('ifs_amount_deposit');
        $totalTransactions = $query->count();

        return [
            Stat::make('Total Setoran', 'Rp ' . number_format($totalDeposit, 0, ',', '.'))
                ->description($totalTransactions . ' transaksi')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Setoran Zakat Fitrah (Uang)', 'Rp ' . number_format($totalZfAmount, 0, ',', '.'))
                ->description('Zakat Fitrah')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Setoran Zakat Fitrah (Beras)', number_format($totalZfRice, 2, ',', '.') . ' Kg')
                ->description('Zakat Fitrah Beras')
                ->descriptionIcon('heroicon-m-cube')
                ->color('warning'),

            Stat::make('Setoran ZM + IFS', 'Rp ' . number_format($totalZmAmount + $totalIfsAmount, 0, ',', '.'))
                ->description('Zakat Maal & Infak/Sedekah')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('info'),
        ];
    }
}
