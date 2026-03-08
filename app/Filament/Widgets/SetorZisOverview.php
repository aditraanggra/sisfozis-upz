<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SetorZisOverview extends BaseWidget
{
    use InteractsWithPageTable;

    public array $tableColumnSearches = [];

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

        // Beras belum terjual: rice_deposit > 0 AND sold_amount = 0
        $unsoldRice = (clone $query)
            ->where('zf_rice_deposit', '>', 0)
            ->where('zf_rice_sold_amount', 0)
            ->sum('zf_rice_deposit');

        // Total konversi beras ke uang
        $totalRiceSold = $query->sum('zf_rice_sold_amount');

        return [
            Stat::make('Total Setoran', 'Rp ' . number_format($totalDeposit, 0, ',', '.'))
                ->description($totalTransactions . ' transaksi')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Setoran Zakat Fitrah (Uang)', 'Rp ' . number_format($totalZfAmount, 0, ',', '.'))
                ->description('Zakat Fitrah')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Beras Belum Terjual', number_format($unsoldRice, 2, ',', '.') . ' Kg')
                ->description('Masih dalam bentuk beras')
                ->descriptionIcon('heroicon-m-cube')
                ->color($unsoldRice > 0 ? 'warning' : 'gray'),

            Stat::make('Konversi Beras ke Uang', 'Rp ' . number_format($totalRiceSold, 0, ',', '.'))
                ->description('Beras yang sudah terjual')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),

            Stat::make('Setoran ZM + IFS', 'Rp ' . number_format($totalZmAmount + $totalIfsAmount, 0, ',', '.'))
                ->description('Zakat Maal & Infak/Sedekah')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('info'),
        ];
    }
}
