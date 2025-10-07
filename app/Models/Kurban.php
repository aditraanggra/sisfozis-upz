<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\ZisScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class Kurban extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_id',
        'total_mudhohi',
        'animal_types',
        'total',
        'total_benef',
        'desc',
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

    /**
     * Relasi ke Unit (UPZ)
     */
    public function unit()
    {
        return $this->belongsTo(UnitZis::class, 'unit_id');
    }

    /**
     * Scope: filter berdasarkan jenis hewan
     */
    public function scopeByAnimal($query, string $animal)
    {
        return $query->where('animal_types', $animal);
    }

    /**
     * Scope: rekap total per unit
     */
    public function scopeSummaryByUnit($query)
    {
        return $query->select('unit_id')
            ->selectRaw('SUM(total_mudhohi) as total_mudhohi')
            ->selectRaw('SUM(total) as total_hewan')
            ->selectRaw('SUM(total_benef) as total_penerima')
            ->groupBy('unit_id');
    }
}
