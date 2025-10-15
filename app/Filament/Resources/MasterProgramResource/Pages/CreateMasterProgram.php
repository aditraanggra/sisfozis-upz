<?php

namespace App\Filament\Resources\MasterProgramResource\Pages;

use App\Filament\Resources\MasterProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterProgram extends CreateRecord
{
    protected static string $resource = MasterProgramResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
