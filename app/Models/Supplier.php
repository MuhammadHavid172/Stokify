<?php

namespace App\Models;

use App\Models\Concerns\BelongsToKios;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory, BelongsToKios;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'supplier';

    /**
     * Kolom yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kios_id',
        'nama',
        'no_telepon',
        'alamat',
        'is_aktif',
    ];

    /**
     * Casting tipe data kolom.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    /**
     * Relasi ke transaksi barang masuk dari supplier ini.
     */
    public function barangMasuk(): HasMany
    {
        return $this->hasMany(BarangMasuk::class, 'supplier_id');
    }

    /**
     * Scope untuk menyaring hanya supplier yang aktif.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_aktif', true);
    }
}
