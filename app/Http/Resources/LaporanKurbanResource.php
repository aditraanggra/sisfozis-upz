<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LaporanKurbanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'unit' => $this->whenLoaded('unit', function () {
                return [
                    'id' => $this->unit->id,
                    'unit_name' => $this->unit->unit_name,
                    'no_register' => $this->unit->no_register,
                ];
            }),
            'tahun_hijriah' => $this->tahun_hijriah,
            'tahun_masehi' => $this->tahun_masehi,
            'jml_sapi' => $this->jml_sapi,
            'jml_kerbau' => $this->jml_kerbau,
            'jml_kambing' => $this->jml_kambing,
            'jml_domba' => $this->jml_domba,
            'total_ekor' => $this->total_ekor,
            'total_shohibul' => $this->total_shohibul,
            'shohibul_dikoreksi' => $this->shohibul_dikoreksi,
            'alasan_koreksi' => $this->alasan_koreksi,
            'jml_paket_daging' => $this->jml_paket_daging,
            'jml_penerima' => $this->jml_penerima,
            'kelompok_penerima' => $this->kelompok_penerima,
            'catatan' => $this->catatan,
            'no_referensi' => $this->no_referensi,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'verified_at' => $this->verified_at?->toIso8601String(),
            'dokumentasi' => DokumentasiKurbanResource::collection($this->whenLoaded('dokumentasi')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
