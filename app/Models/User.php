<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Role;

class User extends Authenticatable implements JWTSubject
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'name', 'email', 'age', 'membership_status', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->password = bcrypt($user->password);
        });
    }
    // Relasi ke peran
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    // Mendapatkan identifier JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // Mendapatkan klaim kustom untuk JWT
    public function getJWTCustomClaims()
    {
        return [];
    }
}
