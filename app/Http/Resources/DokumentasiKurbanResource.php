<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DokumentasiKurbanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'jenis' => $this->jenis,
            'file_path' => $this->file_path,
            'file_size' => $this->file_size,
            'mime_type' => $this->mime_type,
            'uploaded_at' => $this->uploaded_at?->toIso8601String(),
        ];
    }
}
