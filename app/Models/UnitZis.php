<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitZis extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'village_id',
        'district_id',
        'no_sk',
        'unit_name',
        'no_register',
        'unit_field',
        'address',
        'unit_leader',
        'unit_assistant',
        'unit_finance',
        'operator_name',
        'operator_phone',
        'rice_price',
        'is_verified'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(UnitCategory::class, 'category_id');
    }

    public function village()
    {
        return $this->belongsTo(Village::class, 'village_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }
}
