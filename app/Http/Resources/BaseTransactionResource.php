<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseTransactionResource extends JsonResource
{
    protected function getBaseArray()
    {
        return [
            'id' => $this->id,
            'unit' => $this->whenLoaded('unit', function () {
                return [
                    'id' => $this->unit->id,
                    'unit_name' => $this->unit->unit_name,
                ];
            }),
            'trx_date' => $this->trx_date->format('Y-m-d'),
        ];
    }
}
