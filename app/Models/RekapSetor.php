<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapSetor extends Model
{
    use HasFactory;

    protected $table = 'rekap_setor';

    protected $fillable = [
        'unit_id',
        'periode',
        'periode_date',
        't_setor_zf_amount',
        't_setor_zf_rice',
        't_setor_zm',
        't_setor_ifs'
    ];

    protected $casts = [
        'periode_date' => 'date',
        't_setor_zf_amount' => 'integer',
        't_setor_zf_rice' => 'float',
        't_setor_zm' => 'integer',
        't_setor_ifs' => 'integer'
    ];

    public function unit()
    {
        return $this->belongsTo(UnitZis::class, 'unit_id');
    }
}
