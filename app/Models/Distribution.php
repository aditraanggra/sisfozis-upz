<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Distribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'trx_date',
        'mustahik_name',
        'nik',
        'fund_type',
        'asnaf',
        'program',
        'total_rice',
        'total_amount',
        'beneficiary',
        'desc'
    ];

    protected $casts = [
        'trx_date' => 'date',
        'total_rice' => 'float',
        'total_amount' => 'integer',
        'beneficiary' => 'integer',
    ];

    public function unit()
    {
        return $this->belongsTo(UnitZis::class, 'unit_id');
    }
}
