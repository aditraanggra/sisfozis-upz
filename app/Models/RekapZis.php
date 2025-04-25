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
        return $this->belongsTo(UnitZis::class, 'unit_id');
    }

    public function district()
    {
        return $this->hasOneThrough(
            District::class,  // Model tujuan akhir
            UnitZis::class,    // Model perantara
            'id',              // Foreign key di model perantara (unit_zis)
            'id',              // Foreign key di model tujuan akhir (kecamatan)
            'unit_id',     // Local key di model asal (rekap_zis)
            'district_id'     // Local key di model perantara (unit_zis)
        );
    }

    public function village()
    {
        return $this->hasOneThrough(
            Village::class,  // Model tujuan akhir
            UnitZis::class,    // Model perantara
            'id',              // Foreign key di model perantara (unit_zis)
            'id',              // Foreign key di model tujuan akhir (kecamatan)
            'unit_id',     // Local key di model asal (rekap_zis)
            'village_id'     // Local key di model perantara (unit_zis)
        );
    }

    // Di model Rekap (misalnya RekapZis)
    public function zf()
    {
        return $this->hasMany(Zf::class, 'unit_id', 'unit_id');
    }
}
