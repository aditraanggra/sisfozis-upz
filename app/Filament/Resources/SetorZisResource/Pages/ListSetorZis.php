<?php

namespace App\Filament\Resources\SetorZisResource\Pages;

use App\Filament\Resources\SetorZisResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSetorZis extends ListRecords
{
    protected static string $resource = SetorZisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
