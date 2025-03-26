<?php

namespace App\Filament\Resources\RekapZisResource\Pages;

use App\Filament\Resources\RekapZisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRekapZis extends EditRecord
{
    protected static string $resource = RekapZisResource::class;

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
