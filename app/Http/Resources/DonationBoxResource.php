<?php

namespace App\Http\Resources;

class DonationBoxResource extends BaseTransactionResource
{
    public function toArray($request)
    {
        return array_merge(parent::getBaseArray(), [
            'amount' => $this->amount
        ]);
    }
}
