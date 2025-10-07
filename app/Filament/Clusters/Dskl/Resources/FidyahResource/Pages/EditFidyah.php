<?php

namespace App\Filament\Clusters\Dskl\Resources\FidyahResource\Pages;

use App\Filament\Clusters\Dskl\Resources\FidyahResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFidyah extends EditRecord
{
    protected static string $resource = FidyahResource::class;

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
