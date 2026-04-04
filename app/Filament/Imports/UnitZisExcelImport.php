<?php

namespace App\Filament\Imports;

use App\Models\UnitZis;
use EightyNine\ExcelImport\EnhancedDefaultImport;
use Illuminate\Support\Collection;

class UnitZisExcelImport extends EnhancedDefaultImport
{
    public function __construct(
        string $model = UnitZis::class,
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
        $required = ['user_id', 'category_id', 'village_id', 'district_id', 'is_verified', 'profile_completion'];

        foreach ($required as $field) {
            if (! isset($data[$field]) || $data[$field] === null || $data[$field] === '') {
                $this->stopImportWithError("Kolom '{$field}' wajib diisi pada setiap baris data.");
            }
        }
    }
}
