<?php

namespace App\Http\Requests;

use App\Models\AllocationConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AllocationConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $configId = $this->route('allocationConfig')?->id;

        return [
            'zis_type' => [
                'required',
                Rule::in(array_keys(AllocationConfig::TYPES)),
            ],
            'effective_year' => [
                'required',
                'integer',
                'min:2020',
                'max:2100',
                Rule::unique('allocation_configs')
                    ->where('zis_type', $this->zis_type)
                    ->ignore($configId),
            ],
            'setor_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'kelola_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'amil_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'zis_type.required' => 'Jenis ZIS wajib dipilih',
            'zis_type.in' => 'Jenis ZIS harus zf, zm, atau ifs',
            'effective_year.required' => 'Tahun efektif wajib diisi',
            'effective_year.unique' => 'Konfigurasi untuk jenis ZIS dan tahun ini sudah ada',
            'setor_percentage.required' => 'Persentase setor wajib diisi',
            'setor_percentage.max' => 'Persentase setor maksimal 100%',
            'kelola_percentage.required' => 'Persentase kelola wajib diisi',
            'kelola_percentage.max' => 'Persentase kelola maksimal 100%',
            'amil_percentage.required' => 'Persentase amil wajib diisi',
            'amil_percentage.max' => 'Persentase amil maksimal 100%',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $setor = $this->setor_percentage ?? 0;
            $kelola = $this->kelola_percentage ?? 0;

            if (($setor + $kelola) != 100) {
                $validator->errors()->add(
                    'setor_percentage',
                    'Total persentase setor dan kelola harus 100%'
                );
            }
        });
    }
}
