<?php

namespace App\Filament\Resources\MasterProgramResource\Pages;

use App\Filament\Resources\MasterProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterProgram extends EditRecord
{
    protected static string $resource = MasterProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
