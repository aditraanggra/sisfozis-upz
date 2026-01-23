<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ZfPaymentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['beras', 'uang'])],
            'rice_amount' => ['nullable', 'numeric', 'min:0', 'required_if:type,beras'],
            'money_amount' => ['nullable', 'integer', 'min:0', 'required_if:type,uang'],
            'sk_reference' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama jenis pembayaran wajib diisi',
            'type.required' => 'Tipe pembayaran wajib dipilih',
            'type.in' => 'Tipe pembayaran harus beras atau uang',
            'rice_amount.required_if' => 'Jumlah beras wajib diisi untuk tipe beras',
            'money_amount.required_if' => 'Nominal uang wajib diisi untuk tipe uang',
        ];
    }
}
