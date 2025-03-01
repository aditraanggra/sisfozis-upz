<?php

namespace App\Http\Requests;

class ZmRequest extends BaseTransactionRequest
{
    public function rules()
    {
        return array_merge(parent::baseRules(), [
            'category_maal' => 'required|string|max:255',
            'muzakki_name' => 'required|string|max:255',
            'amount' => 'required|integer|min:0'
        ]);
    }
}
