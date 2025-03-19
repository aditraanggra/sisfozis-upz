<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapHakAmil extends Model
{
    use HasFactory;

    protected $table = 'rekap_hak_amil';

    protected $fillable = [
        'unit_id',
        'periode',
        'periode_date',
        't_pendis_ha_zf_amount',
        't_pendis_ha_zf_rice',
        't_pendis_ha_zm',
        't_pendis_ha_ifs',
        't_pm'
    ];

    protected $casts = [
        'periode_date' => 'date',
        't_pendis_ha_zf_amount' => 'integer',
        't_pendis_ha_zf_rice' => 'float',
        't_pendis_ha_zm' => 'integer',
        't_pendis_ha_ifs' => 'integer',
        't_pm' => 'integer'
    ];

    public function unit()
    {
        return $this->belongsTo(UnitZis::class, 'unit_id');
    }
}
