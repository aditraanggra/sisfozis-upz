<?php

namespace App\Filament\Widgets;

use App\Models\SetorZis;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SetorZisOverview extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $totalDeposit = SetorZis::sum('total_deposit');
        $totalZfAmount = SetorZis::sum('zf_amount_deposit');
        $totalZfRice = SetorZis::sum('zf_rice_deposit');
        $totalZmAmount = SetorZis::sum('zm_amount_deposit');
        $totalIfsAmount = SetorZis::sum('ifs_amount_deposit');
        $totalTransactions = SetorZis::count();

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
