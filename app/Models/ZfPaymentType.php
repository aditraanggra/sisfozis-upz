<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ZfPaymentType extends Model
{
    protected $fillable = [
        'name',
        'type',
        'rice_amount',
        'money_amount',
        'sk_reference',
        'is_active',
    ];

    protected $casts = [
        'rice_amount' => 'float',
        'money_amount' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeBeras(Builder $query): Builder
    {
        return $query->where('type', 'beras');
    }

    public function scopeUang(Builder $query): Builder
    {
        return $query->where('type', 'uang');
    }

    public function isBeras(): bool
    {
        return $this->type === 'beras';
    }

    public function isUang(): bool
    {
        return $this->type === 'uang';
    }
}
