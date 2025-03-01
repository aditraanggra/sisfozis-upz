<?php

namespace App\Http\Resources;

class FidyahResource extends BaseTransactionResource
{
    public function toArray($request)
    {
        return array_merge(parent::getBaseArray(), [
            'name' => $this->name,
            'total_day' => $this->total_day,
            'amount' => $this->amount
        ]);
    }
}
