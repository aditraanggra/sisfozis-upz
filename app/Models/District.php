<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_code',
        'name',
    ];

    // Relasi ke tabel villages
    public function villages()
    {
        return $this->hasMany(Village::class);
    }

    // Relasi ke tabel unit_zis
    public function unitzis()
    {
        return $this->hasMany(unitzis::class);
    }

    public function rekapZis()
    {
        return $this->hasManyThrough(
            RekapZis::class,   // Model tujuan akhir
            UnitZis::class,    // Model perantara
            'district_id',    // Foreign key di model perantara (unit_zis)
            'unit_id',     // Foreign key di model tujuan akhir (rekap_zis)
            'id',              // Local key di model asal (kecamatan)
            'id'               // Local key di model perantara (unit_zis)
        );
    }

    public function setorZis()
    {
        return $this->hasMany(setorZis::class, 'unit_id');
    }
}
