<?php

namespace App\Filament\Resources\UnitZisResource\Pages;

use App\Filament\Resources\UnitZisResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUnitZis extends ListRecords
{
    protected static string $resource = UnitZisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
