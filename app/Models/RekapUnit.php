<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapUnit extends Model
{
    use HasFactory;

    protected $table = 'rekap_dkm';

    protected $fillable = [
        'unit_id',
        'periode',
        'periode_date',
        't_penerimaan_zis',
        't_penerimaan_zis_beras',
        't_pendistribusian',
        't_setor',
        'muzakki',
        'mustahik'
    ];

    protected $casts = [
        'periode_date' => 'date',
        't_penerimaan_zis' => 'integer',
        't_penerimaan_zis_beras' => 'float',
        't_pendistribusian' => 'integer',
        't_setor' => 'integer',
        'muzakki' => 'integer',
        'mustahik' => 'integer'
    ];

    public function unit()
    {
        return $this->belongsTo(UnitZis::class, 'unit_id');
    }
}
