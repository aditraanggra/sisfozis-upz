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

    // Relasi ke tabel unit_zis
    public function unitZis()
    {
        return $this->belongsTo(unitZis::class);
    }
    /* public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return str_ends_with($this->email, '@sisfoupz.org')
            || str_ends_with($this->email, '@timsisfo.com')
            || str_ends_with($this->email, '@monitoring.com')
            || str_ends_with($this->email, '@gmail.com');
    } */

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        /* $allowedDomains = [
            '@sisfoupz.org',
            '@timsisfo.com',
            '@monitoring.com'
        ];

        foreach ($allowedDomains as $domain) {
            if (str_ends_with($this->email, $domain)) {
                return true;
            }
        } */

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
        return Auth::user();
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
}
