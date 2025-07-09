<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Scopes\ZisScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

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
