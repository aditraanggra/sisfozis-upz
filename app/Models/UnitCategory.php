<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'desc'];

    // Relasi ke tabel unit_zis
    public function unitZis()
    {
        return $this->hasMany(UnitZis::class);
    }
}
