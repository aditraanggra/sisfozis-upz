<?php

namespace App\Http\Resources;

class ZfResource extends BaseTransactionResource
{
    public function toArray($request)
    {
        return array_merge(parent::getBaseArray(), [
            'muzakki_name' => $this->muzakki_name,
            'zf_rice' => $this->zf_rice,
            'zf_amount' => $this->zf_amount,
            'total_muzakki' => $this->total_muzakki
        ]);
    }
}
