<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseTransactionRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Implement your authorization logic
    }

    protected function baseRules()
    {
        return [
            'unit_id' => 'required|exists:unit_zis,id',
            'trx_date' => 'required|date',
            'desc' => 'nullable|string'
        ];
    }
}
