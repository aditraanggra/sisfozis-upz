<?php

namespace App\Filament\Imports;

use App\Models\LaporanKurban;
use EightyNine\ExcelImport\EnhancedDefaultImport;
use Illuminate\Support\Collection;

class LaporanKurbanExcelImport extends EnhancedDefaultImport
{
    public function __construct(
        string $model = LaporanKurban::class,
        array $attributes = []
    ) {
        parent::__construct($model, $attributes);

        $this->setAfterValidationMutator(function (array $data): array {
            if (isset($data['shohibul_dikoreksi'])) {
                $val = $data['shohibul_dikoreksi'];
                if (is_string($val)) {
                    $data['shohibul_dikoreksi'] = in_array(strtolower($val), ['ya', 'true', '1', 'yes'], true);
                } else {
                    $data['shohibul_dikoreksi'] = (bool) $val;
                }
            }

            if (isset($data['kelompok_penerima']) && is_string($data['kelompok_penerima'])) {
                $data['kelompok_penerima'] = array_map('trim', explode(',', $data['kelompok_penerima']));
            }

            return $data;
        });
    }

    protected function beforeCollection(Collection $collection): void
    {
        if ($collection->isEmpty()) {
            $this->stopImportWithError('File Excel kosong, tidak ada data untuk diimpor.');
        }
    }

    protected function beforeCreateRecord(array $data, $row): void
    {
        $required = ['unit_id', 'tahun_hijriah', 'tahun_masehi'];

        foreach ($required as $field) {
            if (! isset($data[$field]) || $data[$field] === null || $data[$field] === '') {
                $this->stopImportWithError("Kolom '{$field}' wajib diisi pada setiap baris data.");
            }
        }
    }
}
