<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'district_id',
        'village_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isUpzKecamatan(): bool
    {
        return $this->hasRole('upz_kecamatan');
    }

    public function isUpzDesa(): bool
    {
        return $this->hasRole('upz_desa');
    }

    // Relasi ke tabel unit_zis
    public function unitZis()
    {
        return $this->belongsTo(unitZis::class, 'unit_id');
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        // Atau gunakan role-based access
        return $this->hasRole([
            'super_admin',
            'admin',
            'tim_sisfo',
            'monitoring',
            'upz_kecamatan',
            'upz_desa'
        ]);
    }

    /**
     * Get current authenticated user as User instance
     */
    public static function current(): ?self
    {
        $user = Auth::user();
        return $user instanceof self ? $user : null;
    }

    /**
     * Check if current user is super admin
     */
    public static function currentIsSuperAdmin(): bool
    {
        $user = self::current();
        return $user ? $user->isSuperAdmin() : false;
    }

    /**
     * Check if current user is admin
     */
    public static function currentIsAdmin(): bool
    {
        $user = self::current();
        return $user ? $user->isAdmin() : false;
    }

    /**
     * Check if current user is admin
     */
    public static function currentIsUpzKecamatan(): bool
    {
        $user = self::current();
        return $user ? $user->isUpzKecamatan() : false;
    }

    /**
     * Check if current user is admin
     */
    public static function currentIsUpzDesa(): bool
    {
        $user = self::current();
        return $user ? $user->isUpzDesa() : false;
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
