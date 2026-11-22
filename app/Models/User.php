<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kios_id',
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_aktif',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_aktif' => 'boolean',
    ];

    /**
     * Relasi ke model Kios.
     */
    public function kios(): BelongsTo
    {
        return $this->belongsTo(Kios::class, 'kios_id');
    }

    /**
     * Cek apakah user memiliki peran sebagai admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Cek apakah user memiliki peran sebagai kasir.
     */
    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    /**
     * Dapatkan URL rute beranda pengguna sesuai perannya (role).
     */
    public function homeRoute(): string
    {
        return $this->isAdmin() ? route('admin.dashboard') : route('kasir.index');
    }

    /**
     * Relasi transaksi penjualan yang dilayani oleh user ini.
     */
    public function penjualan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Penjualan::class, 'user_id');
    }

    /**
     * Relasi riwayat shift kasir milik pengguna ini.
     */
    public function shiftKasir(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ShiftKasir::class, 'user_id');
    }

    /**
     * Dapatkan shift kasir yang sedang aktif / berjalan untuk pengguna ini.
     */
    public function shiftBerjalan(): ?ShiftKasir
    {
        return ShiftKasir::withoutGlobalScopes()
            ->where('kios_id', $this->kios_id)
            ->where('user_id', $this->id)
            ->where('status', 'berjalan')
            ->first();
    }

    /**
     * Penanganan pemanggilan statis User::shiftBerjalan($user).
     */
    public static function __callStatic($method, $parameters)
    {
        if ($method === 'shiftBerjalan') {
            $user = $parameters[0] ?? \Illuminate\Support\Facades\Auth::user();
            return $user?->shiftBerjalan();
        }

        return parent::__callStatic($method, $parameters);
    }
}
