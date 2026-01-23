<?php

namespace App\Filament\Resources\AllocationConfigResource\Pages;

use App\Filament\Resources\AllocationConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAllocationConfigs extends ListRecords
{
    protected static string $resource = AllocationConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
