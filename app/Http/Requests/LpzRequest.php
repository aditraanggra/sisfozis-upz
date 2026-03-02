<?php

namespace App\Http\Requests;

class LpzRequest extends BaseTransactionRequest
{
    public function rules()
    {
        return array_merge(parent::baseRules(), [
            'lpz_year' => 'required|integer|digits:4',
            'form101' => 'nullable|string|max:255',
            'form102' => 'nullable|string|max:255',
            'lpz' => 'nullable|string|max:255',
        ]);
    }
}
