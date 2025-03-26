<?php

namespace App\Filament\Resources\RekapZisResource\Pages;

use App\Filament\Resources\RekapZisResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRekapZis extends ListRecords
{
    protected static string $resource = RekapZisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
