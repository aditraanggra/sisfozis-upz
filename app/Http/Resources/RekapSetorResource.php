<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RekapSetorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|
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
            't_setor_zf_amount' => $this->t_setor_zf_amount,
            't_setor_zf_rice' => $this->t_setor_zf_rice,
            't_setor_zm' => $this->t_setor_zm,
            't_setor_ifs' => $this->t_setor_ifs,
            /* 'created_at' => $this->created_at,
            'updated_at' => $this->updated_at, */
        ];
    }
}
