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
}
