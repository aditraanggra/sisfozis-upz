<?php

namespace App\Http\Resources;

class ZmResource extends BaseTransactionResource
{
    public function toArray($request)
    {
        return array_merge(parent::getBaseArray(), [
            'category_maal' => $this->category_maal,
            'muzakki_name' => $this->muzakki_name,
            'amount' => $this->amount
        ]);
    }
}
