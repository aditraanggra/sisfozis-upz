<?php

namespace App\Filament\Imports;

use App\Models\User;
use EightyNine\ExcelImport\EnhancedDefaultImport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class UserExcelImport extends EnhancedDefaultImport
{
    public function __construct(
        string $model = User::class,
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
        $required = ['name', 'email', 'password'];

        foreach ($required as $field) {
            if (! isset($data[$field]) || $data[$field] === null || $data[$field] === '') {
                $this->stopImportWithError("Kolom '{$field}' wajib diisi pada setiap baris data.");
            }
        }

        // Validasi format email
        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->stopImportWithError("Format email '{$data['email']}' tidak valid.");
        }

        // Cek duplikasi email
        if (User::where('email', $data['email'])->exists()) {
            $this->stopImportWithError("Email '{$data['email']}' sudah terdaftar dalam sistem.");
        }
    }

    protected function mutateBeforeCreate(array $data): array
    {
        // Hash password sebelum disimpan
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }
}
