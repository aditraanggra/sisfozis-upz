<?php

namespace App\Http\Resources;

class LpzResource extends BaseTransactionResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return array_merge(parent::getBaseArray(), [
            'lpz_year' => $this->lpz_year,
            'form101' => $this->form101,
            'form102' => $this->form102,
            'lpz' => $this->lpz,
        ]);
    }
}
