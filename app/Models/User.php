<?php

namespace App\Models;

use App\Models\M_Role;
use Laravel\Sanctum\HasApiTokens;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

     protected $table = 'users';

     protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'isSuspended',
        'alamat',
        'no_telp',
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

    // method mencari role ybs
    public function hasRole($role)
    {
        return $this->role->name === $role;
    }

    // reference this role_id ke roles id
    public function role()
    {
        return $this->belongsTo(M_Role::class, 'role_id');
    }

    // deklarasi this user_id bisa punya banyak user_id di pasang_lelangs
    public function pasangLelang()
    {
        return $this->hasMany(M_PasangLelang::class);
    }

    // deklarasi this user_id bisa punya banyak user_id di log_masuks
    public function logMasuk()
    {
        return $this->hasMany(M_LogMasuk::class);
    }


    public function lelangMenang()
    {
        return $this->hasMany(M_Lelang::class, 'pemenang_id');
    }
    public function transaksi()
    {
        return $this->hasManyThrough(
            M_Transaksi::class,
            M_PasangLelang::class,
            'user_id',       // Foreign key di M_PasangLelang
            'pasang_lelang_id', // Foreign key di M_Transaksi
            'id',            // Local key di User
            'id'             // Local key di M_PasangLelang
        );
    }

}
