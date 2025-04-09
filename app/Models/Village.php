<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id',
        'village_code',
        'name',
    ];

    // Relasi ke tabel districts
    public function district()
    {
        return $this->belongsTo(District::class);
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
            'village_id',    // Foreign key di model perantara (unit_zis)
            'unit_id',     // Foreign key di model tujuan akhir (rekap_zis)
            'id',              // Local key di model asal (desa)
            'id'               // Local key di model perantara (unit_zis)
        );
    }

    public function setorZis()
    {
        return $this->hasMany(setorZis::class, 'unit_id');
    }
}
