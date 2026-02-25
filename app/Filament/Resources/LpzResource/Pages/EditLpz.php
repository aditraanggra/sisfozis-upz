<?php

namespace App\Filament\Resources\LpzResource\Pages;

use App\Filament\Resources\LpzResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLpz extends EditRecord
{
    protected static string $resource = LpzResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
