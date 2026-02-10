<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Ifs extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'trx_date',
        'munfiq_name',
        'amount',
        'total_munfiq',
        'desc',
    ];

    protected $casts = [
        'trx_date' => 'date',
        'amount' => 'integer',
        'total_munfiq' => 'integer',
    ];

    protected static function booted()
    {
        static::addGlobalScope('user_access', function (Builder $builder) {
            if (! Auth::check()) {
                return;
            }

            $user = Auth::user();

            if (! $user) {
                return;
            }

            // Hanya terapkan filter untuk role tertentu
            if (User::currentIsUpzKecamatan() && $user->district_id) {
                $builder->whereHas('unit', function ($query) use ($user) {
                    $query->where('district_id', $user->district_id);
                });
            } elseif (User::currentIsUpzDesa() && $user->village_id) {
                $builder->whereHas('unit', function ($query) use ($user) {
                    $query->where('village_id', $user->village_id);
                });
            }
            // Admin dan super_admin tidak dibatasi
        });

        static::saving(function ($model) {
            if (isset($model->total_munfiq) && $model->total_munfiq < 1) {
                throw new \InvalidArgumentException('Total Munfiq must be at least 1');
            }
        });
    }

    public function unit()
    {
        return $this->belongsTo(UnitZis::class, 'unit_id');
    }
}
