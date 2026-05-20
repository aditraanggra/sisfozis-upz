<?php

namespace App\Http\Requests;

use App\Models\UnitZis;
use Illuminate\Foundation\Http\FormRequest;

class StoreLaporanKurbanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return UnitZis::where('user_id', $this->user()?->id)->exists();
    }

    public function rules(): array
    {
        return [
            'tahun_hijriah' => 'required|string|regex:/^\d{4}$/',
            'tahun_masehi' => 'required|integer|min:2020|max:2100',
            'jml_sapi' => 'required|integer|min:0|max:999',
            'jml_kerbau' => 'required|integer|min:0|max:999',
            'jml_kambing' => 'required|integer|min:0|max:999',
            'jml_domba' => 'required|integer|min:0|max:999',
            'total_shohibul' => 'required|integer|min:0',
            'shohibul_dikoreksi' => 'boolean',
            'alasan_koreksi' => 'nullable|string|max:255',
            'jml_paket_daging' => 'nullable|integer|min:0',
            'jml_penerima' => 'nullable|integer|min:0',
            'kelompok_penerima' => 'nullable|array',
            'kelompok_penerima.*' => 'string|in:warga,fakir_miskin,pesantren,panti_asuhan,luar_daerah',
            'catatan' => 'nullable|string|max:1000',
        ];
    }
}
