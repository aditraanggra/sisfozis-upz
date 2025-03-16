<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapZis extends Model
{
    //
    protected $table = 'rekap_zis';

    protected $fillable = [
        'unit_id',
        'period',
        'period_date',
        'total_zf_rice',
        'total_zf_amount',
        'total_zf_muzakki',
        'total_zm_amount',
        'total_zm_muzakki',
        'total_ifs_amount',
        'total_ifs_munfiq'
    ];

    protected $cast = [
        'total_zf_rice' => 'float',
        'total_zf_amount' => 'integer',
        'total_zf_muzakki' => 'integer',
        'total_zm_amount' => 'integer',
        'total_zm_muzakki' => 'integer',
        'total_ifs_amount' => 'integer',
        'total_ifs_munfiq' => 'integer'
    ];

    public function unit()
    {
        return $this->belongsTo('App\Models\UnitZis', 'unit_id');
    }
}
