<?php

namespace App\Http\Requests;

class ZfRequest extends BaseTransactionRequest
{
    public function rules()
    {
        return array_merge(parent::baseRules(), [
            'muzakki_name' => 'required|string|max:255',
            'zf_rice' => 'required|numeric|min:0',
            'zf_amount' => 'required|integer|min:0',
            'total_muzakki' => 'required|integer|min:1'
        ]);
    }
}
