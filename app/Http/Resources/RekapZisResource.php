<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RekapZisResource extends JsonResource
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
            'period' => $this->period,
            'period_date' => $this->period_date,
            'total_zf_rice' => (float) $this->total_zf_rice,
            'total_zf_amount' => (int) $this->total_zf_amount,
            'total_zf_muzakki' => (int) $this->total_zf_muzakki,
            'total_zm_amount' => (int) $this->total_zm_amount,
            'total_zm_muzakki' => (int) $this->total_zm_muzakki,
            'total_ifs_amount' => (int) $this->total_ifs_amount,
            'total_ifs_munfiq' => (int) $this->total_ifs_munfiq,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
