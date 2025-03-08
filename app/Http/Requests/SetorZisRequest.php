<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetorZisRequest extends BaseTransactionRequest
{
    public function rules()
    {
        return array_merge(parent::baseRules(), [
            'zf_amount_deposit' => 'required|integer|min:0',
            'zf_rice_deposit' => 'required|numeric|min:0',
            'zm_amount_deposit' => 'required|integer|min:0',
            'ifs_amount_deposit' => 'required|integer|min:0',
            'total_deposit' => 'required|integer|min:0',
            'status' => 'required|string|max:255',
            'validation' => 'required|string|max:255',
            'upload' => 'required|string|max:255',

        ]);
    }
}
