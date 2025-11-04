<?php

namespace App\Filament\Imports;

use App\Models\Distribution;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class DistributionImporter extends Importer
{
    protected static ?string $model = Distribution::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('unit_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('trx_date')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('mustahik_name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('nik')
                ->rules(['max:255']),
            ImportColumn::make('fund_type')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('asnaf')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('program')
                ->rules(['max:255']),
            ImportColumn::make('total_rice')
                ->numeric()
                ->rules(['numeric']),
            ImportColumn::make('total_amount')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('beneficiary')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('desc'),
        ];
    }

    public function resolveRecord(): ?Distribution
    {
        return new Distribution();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import data Pendistribusian berhasil dan ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' gagal diimport.';
        }

        return $body;
    }
}