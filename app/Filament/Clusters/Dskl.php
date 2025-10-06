<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Dskl extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Rekap & Transaksi';

    public static function getLabel(): string
    {
        return 'DSKL';
    }
}
