<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseTransactionResource extends JsonResource
{
    protected function getBaseArray()
    {
        return [
            'id' => $this->id,
            'unit' => new UnitZisResource(true, "Data upz terkoneksi", $this->whenLoaded('unit', function () {
                return $this->unit->only(['id', 'unit_name', 'no_regiter']);
            })),
            'trx_date' => $this->trx_date->format('Y-m-d'),
            'desc' => $this->desc,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
