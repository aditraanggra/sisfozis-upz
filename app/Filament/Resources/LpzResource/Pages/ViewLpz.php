<?php

namespace App\Filament\Resources\LpzResource\Pages;

use App\Filament\Resources\LpzResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLpz extends ViewRecord
{
    protected static string $resource = LpzResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
