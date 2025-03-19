<?php

namespace App\Http\Requests;

class DistributionRequest extends BaseTransactionRequest
{
    public function rules()
    {
        return array_merge(parent::baseRules(), [
            'mustahik_name' => 'required|string|max:255',
            'nik' => 'required|string|size:16',
            'fund_type' => 'required|string|max:255',
            'asnaf' => 'required|string|max:255',
            'program' => 'required|string|max:255',
            'total_rice' => 'required|numeric|min:0',
            'total_amount' => 'required|integer|min:0',
            'beneficiary' => 'required|integer|min:1',
        ]);
    }
}
