<?php

namespace App\Http\Resources;

class DistributionResource extends BaseTransactionResource
{
    public function toArray($request)
    {
        return array_merge(parent::getBaseArray(), [
            'mustahik_name' => $this->mustahik_name,
            'nik' => $this->nik,
            'fund_type' => $this->fund_type,
            'asnaf' => $this->asnaf,
            'program' => $this->program,
            'total_rice' => $this->total_rice,
            'total_amount' => $this->total_amount,
            'beneficiary' => $this->beneficiary,
        ]);
    }
}
