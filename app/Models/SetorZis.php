<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SetorZis extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'trx_date',
        'zf_amount_deposit',
        'zf_rice_deposit',
        'zm_amount_deposit',
        'ifs_amount_deposit',
        'total_deposit',
        'status',
        'validation',
        'upload',
    ];

    protected $casts = [
        'trx_date' => 'date',
        'amount' => 'integer',
        'zf_amount_deposit' => 'integer',
        'zf_rice_deposit' => 'float',
        'zm_amount_deposit' => 'integer',
        'ifs_amount_deposit' => 'integer',
        'total_deposit' => 'integer',
        'status' => 'string',
        'validation' => 'string',
        'upload' => 'string',
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
}
