<?php

namespace App\Filament\Imports;

use App\Models\Lpz;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class LpzImporter extends Importer
{
    protected static ?string $model = Lpz::class;

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
            ImportColumn::make('lpz_year')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('form101'),
            ImportColumn::make('form102'),
            ImportColumn::make('lpz'),
        ];
    }

    public function resolveRecord(): ?Lpz
    {
        // return Lpz::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Lpz();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import data LPZ berhasil dan ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' diimpor.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' gagal diimpor.';
        }

        return $body;
    }
}
