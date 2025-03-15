<?php

namespace App\Filament\Resources\IfsResource\Pages;

use App\Filament\Resources\IfsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIfs extends EditRecord
{
    protected static string $resource = IfsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
