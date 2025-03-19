<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RekapPendisResource extends JsonResource
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
            'total_pendis' => [
                'zf_amount' => (int) $this->t_pendis_zf_amount,
                'zf_rice' => (float) $this->t_pendis_zf_rice,
                'zm' => (int) $this->t_pendis_zm,
                'ifs' => (int) $this->t_pendis_ifs,
            ],
            'asnaf' => [
                'fakir' => [
                    'amount' => (int) $this->t_pendis_fakir_amount,
                    'rice' => (float) $this->t_pendis_fakir_rice,
                ],
                'miskin' => [
                    'amount' => (int) $this->t_pendis_miskin_amount,
                    'rice' => (float) $this->t_pendis_miskin_rice,
                ],
                'fisabilillah' => [
                    'amount' => (int) $this->t_pendis_fisabilillah_amount,
                    'rice' => (float) $this->t_pendis_fisabilillah_rice,
                ],
            ],
            'program' => [
                'kemanusiaan' => [
                    'amount' => (int) $this->t_pendis_kemanusiaan_amount,
                    'rice' => (float) $this->t_pendis_kemanusiaan_rice,
                ],
                'dakwah' => [
                    'amount' => (int) $this->t_pendis_dakwah_amount,
                    'rice' => (float) $this->t_pendis_dakwah_rice,
                ],
            ],
            't_pm' => (int) $this->t_pm,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
