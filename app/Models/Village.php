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
}
