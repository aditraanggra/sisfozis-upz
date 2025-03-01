<?php

namespace App\Http\Requests;

class DonationBoxRequest extends BaseTransactionRequest
{
    public function rules()
    {
        return array_merge(parent::baseRules(), [
            'amount' => 'required|integer|min:0'
        ]);
    }
}
