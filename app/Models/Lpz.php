<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lpz extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'trx_date',
        'lpz_year',
        'form101',
        'form102',
        'lpz',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitZis::class, 'unit_id');
    }
}
