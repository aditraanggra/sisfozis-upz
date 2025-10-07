<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use App\Models\Scopes\ZisScope;

class Fidyah extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'trx_date',
        'name',
        'total_day',
        'amount',
        'desc'
    ];

    protected $casts = [
        'trx_date' => 'date',
        'total_day' => 'integer',
        'amount' => 'integer'
    ];

    protected static function booted()
    {
        static::addGlobalScope('user_access', function (Builder $builder) {
            if (!Auth::check()) {
                return;
            }

            $user = Auth::user();

            if (!$user) {
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

        static::addGlobalScope(new ZisScope);
    }

    public function unit()
    {
        return $this->belongsTo(UnitZis::class, 'unit_id');
    }
}
