<?php

namespace App\Filament\Resources\ZmResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ZmOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            //
            Stat::make('Total Zakat Mal', 'Rp ' . number_format(\App\Models\Zm::sum('amount'), 0, ',', '.')),
            Stat::make('Total Muzakki', \App\Models\Zm::count('muzakki_name'))
        ];
    }
}
