<?php

namespace App\Models;

use App\Models\Scopes\ZisScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Zm extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'trx_date',
        'category_maal',
        'muzakki_name',
        'no_telp',
        'amount',
        'desc',
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

        static::addGlobalScope(new ZisScope);
    }

    protected $casts = [
        'trx_date' => 'date',
        'amount' => 'integer',
        'no_telp' => 'string',
    ];

    public function unit()
    {
        return $this->belongsTo(UnitZis::class, 'unit_id');
    }
}
