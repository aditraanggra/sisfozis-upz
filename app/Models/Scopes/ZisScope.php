<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ZisScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Pastikan kita dalam konteks web dan ada user yang login
        if (!Auth::check() || !Auth::hasUser()) {
            return;
        }

        $user = Auth::user();

        // Jika user tidak login atau tidak memiliki role yang memerlukan filter
        if (!$user || !User::currentIsUpzKecamatan() || !User::currentIsUpzDesa()) {
            return;
        }

        // Filter berdasarkan role
        if (User::currentIsUpzKecamatan() && $user->district_id) {
            // Filter berdasarkan district_id
            $builder->whereHas('unit', function ($query) use ($user) {
                $query->where('district_id', $user->district_id);
            });
        } elseif (User::currentIsUpzDesa() && $user->village_id) {
            // Filter berdasarkan village_id
            $builder->whereHas('unit', function ($query) use ($user) {
                $query->where('village_id', $user->village_id);
            });
        }
    }
}
