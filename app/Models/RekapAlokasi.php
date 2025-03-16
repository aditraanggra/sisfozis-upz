<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapAlokasi extends Model
{
    use HasFactory;

    protected $table = 'rekap_alokasi';

    protected $fillable = [
        'unit_id',
        'periode',
        'periode_date',
        'total_setor_zf_amount',
        'total_setor_zf_rice',
        'total_setor_zm',
        'total_setor_ifs',
        'total_kelola_zf_amount',
        'total_kelola_zf_rice',
        'total_kelola_zm',
        'total_kelola_ifs',
        'hak_amil_zf_amount',
        'hak_amil_zf_rice',
        'hak_amil_zm',
        'hak_amil_ifs',
        'alokasi_pendis_zf_amount',
        'alokasi_pendis_zf_rice',
        'alokasi_pendis_zm',
        'alokasi_pendis_ifs',
        'hak_op_zf_amount',
        'hak_op_zf_rice',
    ];

    protected $casts = [
        'periode_date' => 'date',
        'total_setor_zf_rice' => 'float',
        'total_kelola_zf_rice' => 'float',
        'hak_amil_zf_rice' => 'float',
        'alokasi_pendis_zf_rice' => 'float',
    ];

    public function unit()
    {
        return $this->belongsTo('App\Models\UnitZis', 'unit_id');
    }
}
