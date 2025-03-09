<?php

namespace App\Filament\Imports;

use App\Models\UnitZis;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class UnitZisImporter extends Importer
{
    protected static ?string $model = UnitZis::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('user_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('category_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('village_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('district_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('no_sk'),
            ImportColumn::make('unit_name'),
            ImportColumn::make('no_register'),
            ImportColumn::make('address'),
            ImportColumn::make('unit_leader'),
            ImportColumn::make('unit_assistant'),
            ImportColumn::make('unit_finance'),
            ImportColumn::make('operator_phone'),
            ImportColumn::make('rice_price')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('is_verified')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('profile_completion')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
        ];
    }

    public function resolveRecord(): ?UnitZis
    {
        // return UnitZis::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new UnitZis();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import data user berhasil dan ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' Proses import gaga.';
        }

        return $body;
    }
}
