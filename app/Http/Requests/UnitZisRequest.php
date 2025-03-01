<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnitZisRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'category_id' => 'required|exists:unit_categories,id',
            'village_id' => 'required|exists:villages,id',
            'district_id' => 'required|exists:districts,id',
            'no_sk' => 'required|string|max:255',
            'unit_name' => 'required|string|max:255',
            'no_register' => 'required|string|max:255|unique:unit_zis,no_register,' . $this->id,
            'unit_field' => 'required|string|max:255',
            'address' => 'required|string',
            'unit_leader' => 'required|string|max:255',
            'unit_assistant' => 'required|string|max:255',
            'unit_finance' => 'required|string|max:255',
            'operator_name' => 'required|string|max:255',
            'operator_phone' => 'required|string|max:255',
            'rice_price' => 'required|integer|min:0',
            'is_verified' => 'boolean'
        ];
    }
}
