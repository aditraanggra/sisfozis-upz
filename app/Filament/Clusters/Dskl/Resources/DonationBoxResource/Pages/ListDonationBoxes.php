<?php

namespace App\Filament\Clusters\Dskl\Resources\DonationBoxResource\Pages;

use App\Filament\Clusters\Dskl\Resources\DonationBoxResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDonationBoxes extends ListRecords
{
    protected static string $resource = DonationBoxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
