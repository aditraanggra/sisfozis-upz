<?php

namespace App\Filament\Resources\MasterProgramResource\Pages;

use App\Filament\Resources\MasterProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterPrograms extends ListRecords
{
    protected static string $resource = MasterProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
