<?php

namespace App\Filament\Imports;

use App\Models\Lpz;
use EightyNine\ExcelImport\EnhancedDefaultImport;
use Illuminate\Support\Collection;

class LpzExcelImport extends EnhancedDefaultImport
{
    public function __construct(
        string $model = Lpz::class,
        array $attributes = []
    ) {
        parent::__construct($model, $attributes);
    }

    protected function beforeCollection(Collection $collection): void
    {
        if ($collection->isEmpty()) {
            $this->stopImportWithError('File Excel kosong, tidak ada data untuk diimpor.');
        }
    }

    protected function beforeCreateRecord(array $data, $row): void
    {
        // Validasi kolom wajib
        $required = ['unit_id', 'trx_date', 'lpz_year'];

        foreach ($required as $field) {
            if (! isset($data[$field]) || $data[$field] === null || $data[$field] === '') {
                $this->stopImportWithError("Kolom '{$field}' wajib diisi pada setiap baris data.");
            }
        }
    }
}
