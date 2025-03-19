<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapPendis extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'unit_id',
        'periode',
        'periode_date',
        't_pendis_zf_amount',
        't_pendis_zf_rice',
        't_pendis_zm',
        't_pendis_ifs',
        't_pendis_fakir_amount',
        't_pendis_miskin_amount',
        't_pendis_fisabilillah_amount',
        't_pendis_fakir_rice',
        't_pendis_miskin_rice',
        't_pendis_fisabilillah_rice',
        't_pendis_kemanusiaan_amount',
        't_pendis_dakwah_amount',
        't_pendis_kemanusiaan_rice',
        't_pendis_dakwah_rice',
        't_pm',
    ];

    /**
     * Get the unit that owns the rekap pendis.
     */
    public function unit()
    {
        return $this->belongsTo(UnitZis::class, 'unit_id');
    }
}
