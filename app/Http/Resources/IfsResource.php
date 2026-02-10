<?php

namespace App\Http\Resources;

class IfsResource extends BaseTransactionResource
{
    public function toArray($request)
    {
        return array_merge(parent::getBaseArray(), [
            'munfiq_name' => $this->munfiq_name,
            'amount' => $this->amount,
            'total_munfiq' => $this->total_munfiq,
            'desc' => $this->desc,
        ]);
    }
}
