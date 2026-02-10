<?php

namespace App\Http\Requests;

class IfsRequest extends BaseTransactionRequest
{
    public function rules()
    {
        return array_merge(parent::baseRules(), [
            'munfiq_name' => 'required|string|max:255',
            'amount' => 'required|integer|min:0',
            'total_munfiq' => 'required|integer|min:1',
        ]);
    }
}
