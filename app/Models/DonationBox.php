<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DonationBox extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'trx_date',
        'amount',
        'desc'
    ];

    protected $casts = [
        'trx_date' => 'date',
        'amount' => 'integer'
    ];

    public function unit()
    {
        return $this->belongsTo(UnitZis::class, 'unit_id');
    }
}
