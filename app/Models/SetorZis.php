<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class SetorZis extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'trx_date',
        'zf_amount_deposit',
        'zf_rice_deposit',
        'zf_rice_sold_amount',
        'zf_rice_sold_price',
        'zf_rice_sold_proof',
        'zm_amount_deposit',
        'ifs_amount_deposit',
        'total_deposit',
        'status',
        'validation',
        'upload',
        'deposit_destination',
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
    }

    protected $casts = [
        'trx_date' => 'date',
        'amount' => 'integer',
        'zf_amount_deposit' => 'integer',
        'zf_rice_deposit' => 'float',
        'zf_rice_sold_amount' => 'integer',
        'zf_rice_sold_price' => 'integer',
        'zm_amount_deposit' => 'integer',
        'ifs_amount_deposit' => 'integer',
        'total_deposit' => 'integer',
        'status' => 'string',
        'validation' => 'string',
        'upload' => 'string',
        'zf_rice_sold_proof' => 'string',
        'deposit_destination' => 'string',
    ];

    // =========================================================================
    // Computed Accessors
    // =========================================================================

    /**
     * Apakah beras sudah terjual (baik langsung oleh unit maupun batch oleh desa/kec).
     */
    public function getIsRiceSoldAttribute(): bool
    {
        return $this->zf_rice_sold_amount > 0;
    }

    /**
     * Jumlah beras yang belum terjual (Kg).
     * Jika sudah terjual, return 0.
     */
    public function getUnsoldRiceAttribute(): float
    {
        return $this->is_rice_sold ? 0.0 : (float) $this->zf_rice_deposit;
    }

    /**
     * Hitung ulang jumlah beras asli dari data penjualan (untuk audit trail).
     * Berguna saat zf_rice_deposit sudah di-0-kan setelah penjualan.
     */
    public function getOriginalRiceQtyAttribute(): float
    {
        if ($this->zf_rice_sold_price > 0 && $this->zf_rice_sold_amount > 0) {
            return round($this->zf_rice_sold_amount / $this->zf_rice_sold_price, 2);
        }

        return (float) $this->zf_rice_deposit;
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    /**
     * Scope: hanya record yang berasnya belum terjual.
     */
    public function scopeUnsoldRice(Builder $query): Builder
    {
        return $query->where('zf_rice_deposit', '>', 0)
                     ->where(function ($q) {
                         $q->where('zf_rice_sold_amount', 0)
                           ->orWhereNull('zf_rice_sold_amount');
                     });
    }

    /**
     * Scope: filter berdasarkan tujuan setoran.
     */
    public function scopeByDestination(Builder $query, string $destination): Builder
    {
        return $query->where('deposit_destination', $destination);
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function unit()
    {
        return $this->belongsTo(UnitZis::class, 'unit_id');
    }

    public function district()
    {
        return $this->hasOneThrough(
            District::class,  // Model tujuan akhir
            UnitZis::class,    // Model perantara
            'id',              // Foreign key di model perantara (unit_zis)
            'id',              // Foreign key di model tujuan akhir (kecamatan)
            'unit_id',     // Local key di model asal (rekap_zis)
            'district_id'     // Local key di model perantara (unit_zis)
        );
    }

    public function village()
    {
        return $this->hasOneThrough(
            Village::class,  // Model tujuan akhir
            UnitZis::class,    // Model perantara
            'id',              // Foreign key di model perantara (unit_zis)
            'id',              // Foreign key di model tujuan akhir (kecamatan)
            'unit_id',     // Local key di model asal (rekap_zis)
            'village_id'     // Local key di model perantara (unit_zis)
        );
    }
}
