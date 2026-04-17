<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LpzSampleExport implements FromArray, WithHeadings
{
    public function __construct(protected array $data)
    {
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return count($this->data) > 0 ? array_keys($this->data[0]) : [];
    }
}
