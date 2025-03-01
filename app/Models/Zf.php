<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Zf extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'trx_date',
        'muzakki_name',
        'zf_rice',
        'zf_amount',
        'total_muzakki',
        'desc'
    ];

    protected $casts = [
        'trx_date' => 'date',
        'zf_rice' => 'float',
        'zf_amount' => 'integer',
        'total_muzakki' => 'integer'
    ];

    public function unit()
    {
        return $this->belongsTo(UnitZis::class, 'unit_id');
    }
}
