<?php

namespace App\Filament\Clusters\Dskl\Resources\FidyahResource\Pages;

use App\Filament\Clusters\Dskl\Resources\FidyahResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFidyahs extends ListRecords
{
    protected static string $resource = FidyahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
