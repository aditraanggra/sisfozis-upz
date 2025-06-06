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
        'address',
        'unit_leader',
        'unit_assistant',
        'unit_finance',
        'operator_phone',
        'rice_price',
        'is_verified',
        'profile_completion'
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

    public function zfs()
    {
        return $this->hasMany(Zf::class, 'unit_id');
    }

    public function zms()
    {
        return $this->hasMany(Zm::class, 'unit_id');
    }

    public function ifs()
    {
        return $this->hasMany(Ifs::class, 'unit_id');
    }

    public function fidyahs()
    {
        return $this->hasMany(Fidyah::class, 'unit_id');
    }

    public function donationBoxes()
    {
        return $this->hasMany(DonationBox::class, 'unit_id');
    }

    public function distributions()
    {
        return $this->hasMany(Distribution::class, 'unit_id');
    }

    public function rekapZis()
    {
        return $this->hasMany(RekapZis::class, 'unit_id');
    }

    public function rekapPendis()
    {
        return $this->hasMany(RekapPendis::class, 'unit_id');
    }

    public function rekapAlokasi()
    {
        return $this->hasMany(RekapAlokasi::class, 'unit_id');
    }

    public function rekapHakAmil()
    {
        return $this->hasMany(RekapHakAmil::class, 'unit_id');
    }

    public function rekapSetor()
    {
        return $this->hasMany(RekapSetor::class, 'unit_id');
    }

    public function rekapUnit()
    {
        return $this->hasMany(RekapUnit::class, 'unit_id');
    }

    public function setorZis()
    {
        return $this->hasMany(setorZis::class, 'unit_id');
    }
}
