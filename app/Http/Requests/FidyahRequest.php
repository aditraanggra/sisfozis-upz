<?php

namespace App\Http\Requests;

class FidyahRequest extends BaseTransactionRequest
{
    public function rules()
    {
        return array_merge(parent::baseRules(), [
            'name' => 'required|string|max:255',
            'total_day' => 'required|integer|min:1',
            'amount' => 'required|integer|min:0'
        ]);
    }
}
