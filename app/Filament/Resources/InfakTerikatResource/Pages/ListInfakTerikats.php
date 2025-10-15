<?php

namespace App\Filament\Resources\InfakTerikatResource\Pages;

use App\Filament\Resources\InfakTerikatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInfakTerikats extends ListRecords
{
    protected static string $resource = InfakTerikatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
