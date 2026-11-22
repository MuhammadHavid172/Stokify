<?php

namespace App\Models;

use App\Models\Concerns\BelongsToKios;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barang extends Model
{
    use HasFactory, BelongsToKios;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'barang';

    /**
     * Kolom yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kios_id',
        'kategori_id',
        'kode',
        'nama',
        'satuan',
        'harga_modal',
        'harga_jual',
        'stok',
        'stok_minimum',
        'is_aktif',
        'created_at',
        'updated_at',
    ];

    /**
     * Casting tipe data kolom.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'harga_modal' => 'integer',
        'harga_jual' => 'integer',
        'stok' => 'integer',
        'stok_minimum' => 'integer',
        'is_aktif' => 'boolean',
    ];

    /**
     * Aksesor yang otomatis di-append ke array/JSON.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'stok_menipis',
    ];

    /**
     * Relasi ke model Kategori.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    /**
     * Relasi ke riwayat mutasi stok.
     */
    public function mutasiStok(): HasMany
    {
        return $this->hasMany(MutasiStok::class, 'barang_id');
    }

    /**
     * Relasi ke rincian penjualan.
     */
    public function detailPenjualan(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class, 'barang_id');
    }

    /**
     * Aksesor stok_menipis: bernilai true jika stok saat ini <= stok minimum.
     */
    public function getStokMenipisAttribute(): bool
    {
        return $this->stok <= $this->stok_minimum;
    }

    /**
     * Scope untuk menyaring hanya barang yang aktif.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_aktif', true);
    }

    /**
     * Scope untuk menyaring barang aktif yang stoknya menipis atau habis (stok <= stok_minimum).
     */
    public function scopeStokMenipis(Builder $query): Builder
    {
        return $query->where('is_aktif', true)->whereColumn('stok', '<=', 'stok_minimum');
    }
}
