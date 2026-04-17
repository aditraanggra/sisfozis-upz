<?php

namespace App\Filament\Resources\LpzResource\Pages;

use App\Filament\Exports\LpzExporter;
use App\Filament\Imports\LpzImporter;
use App\Filament\Resources\LpzResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLpzs extends ListRecords
{
    protected static string $resource = LpzResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->importer(LpzImporter::class),
            Actions\ExportAction::make()
                ->exporter(LpzExporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
