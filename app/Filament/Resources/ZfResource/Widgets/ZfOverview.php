<?php

namespace App\Filament\Resources\ZfResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ZfOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Zakat Fitrah Uang', 'Rp.' . number_format(\App\Models\Zf::sum('zf_amount'), 0, ',', '.')),
            Stat::make('Total Zakat Fitrah Beras (Kg)', number_format(\App\Models\Zf::sum('zf_rice'), 2, ',', '.') . ' Kg'),
            Stat::make('Total Muzakki', \App\Models\Zf::sum('total_muzakki')) // Provide the required argument
        ];
    }
}
