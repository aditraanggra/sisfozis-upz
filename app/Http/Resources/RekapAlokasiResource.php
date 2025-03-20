<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RekapAlokasiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'unit_id' => $this->unit_id,
            'unit' => $this->whenLoaded('unit', function () {
                return [
                    'id' => $this->unit->id,
                    'unit_name' => $this->unit->unit_name,
                ];
            }),
            'periode' => $this->periode,
            'periode_date' => $this->periode_date,
            'total_setor' => [
                'zf_amount' => (int) $this->total_setor_zf_amount,
                'zf_rice' => (float) $this->total_setor_zf_rice,
                'zm' => (int) $this->total_setor_zm,
                'ifs' => (int) $this->total_setor_ifs,
            ],
            'total_kelola' => [
                'zf_amount' => (int) $this->total_kelola_zf_amount,
                'zf_rice' => (float) $this->total_kelola_zf_rice,
                'zm' => (int) $this->total_kelola_zm,
                'ifs' => (int) $this->total_kelola_ifs,
            ],
            'hak_amil' => [
                'zf_amount' => (int) $this->hak_amil_zf_amount,
                'zf_rice' => (float) $this->hak_amil_zf_rice,
                'zm' => (int) $this->hak_amil_zm,
                'ifs' => (int) $this->hak_amil_ifs,
            ],
            'alokasi_pendis' => [
                'zf_amount' => (int) $this->alokasi_pendis_zf_amount,
                'zf_rice' => (float) $this->alokasi_pendis_zf_rice,
                'zm' => (int) $this->alokasi_pendis_zm,
                'ifs' => (int) $this->alokasi_pendis_ifs,
            ],
            'hak_op' => [
                'zf_amount' => (int) $this->hak_op_zf_amount,
                'zf_rice' => (float) $this->hak_op_zf_rice,
            ],
            /* 'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(), */
        ];
    }
}
