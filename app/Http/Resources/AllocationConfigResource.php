<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllocationConfigResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'zis_type' => $this->zis_type,
            'zis_type_label' => $this->TYPES[$this->zis_type] ?? $this->zis_type,
            'effective_year' => $this->effective_year,
            'setor_percentage' => (float) $this->setor_percentage,
            'kelola_percentage' => (float) $this->kelola_percentage,
            'amil_percentage' => (float) $this->amil_percentage,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
