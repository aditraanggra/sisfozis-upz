<?php

namespace App\Filament\Resources\SetorZisResource\Pages;

use App\Filament\Resources\SetorZisResource;
use App\Filament\Widgets\SetorZisOverview;
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

    protected function getHeaderWidgets(): array
    {
        return [
            SetorZisOverview::class,
        ];
    }
}
