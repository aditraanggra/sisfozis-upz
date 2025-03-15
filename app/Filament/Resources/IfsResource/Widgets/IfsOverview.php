<?php

namespace App\Filament\Resources\IfsResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class IfsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Infak Sedekah', 'Rp ' . number_format(\App\Models\Ifs::sum('amount'), 0, ',', '.')),
            Stat::make('Total Munfik', \App\Models\Ifs::count('munfiq_name'))
        ];
    }
}
