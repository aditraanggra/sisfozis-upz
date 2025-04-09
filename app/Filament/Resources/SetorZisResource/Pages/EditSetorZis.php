<?php

namespace App\Filament\Resources\SetorZisResource\Pages;

use App\Filament\Resources\SetorZisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSetorZis extends EditRecord
{
    protected static string $resource = SetorZisResource::class;

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
