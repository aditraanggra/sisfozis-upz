<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RekapHakAmilResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
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
            'total_pendis_ha' => [
                'zf_amount' => (int) $this->t_pendis_ha_zf_amount,
                'zf_rice' => (float) $this->t_pendis_ha_zf_rice,
                'zm' => (int) $this->t_pendis_ha_zm,
                'ifs' => (int) $this->t_pendis_ha_ifs,
            ],
            't_pm' => (int) $this->t_pm,
            /* 'created_at' => $this->created_at,
            'updated_at' => $this->updated_at, */
        ];
    }
}
