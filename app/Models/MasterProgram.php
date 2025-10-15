<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterProgram extends Model
{
    //
    protected $fillable = [
        'name',
        'desc'
    ];

    public function infakTerikats()
    {
        return $this->hasMany(InfakTerikat::class, 'program_id');
    }
}
