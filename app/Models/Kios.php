<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kios extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terasosiasi dengan model.
     *
     * @var string
     */
    protected $table = 'kios';

    /**
     * Kolom yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_kios',
        'alamat',
        'no_telepon',
        'status',
        'lead_time_hari',
        'hari_pengaman',
        'cakupan_kulakan_hari',
        'batas_stok_berlebih_hari',
        'margin_tipis_persen',
    ];

    /**
     * Casting tipe data kolom.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'lead_time_hari' => 'integer',
        'hari_pengaman' => 'integer',
        'cakupan_kulakan_hari' => 'integer',
        'batas_stok_berlebih_hari' => 'integer',
        'margin_tipis_persen' => 'integer',
    ];

    /**
     * Relasi ke model User (satu kios memiliki banyak user/petugas).
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'kios_id');
    }

    /**
     * Relasi ke model Kategori.
     */
    public function kategori(): HasMany
    {
        return $this->hasMany(Kategori::class, 'kios_id');
    }

    /**
     * Relasi ke model Barang.
     */
    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class, 'kios_id');
    }

    /**
     * Relasi ke model MutasiStok.
     */
    public function mutasiStok(): HasMany
    {
        return $this->hasMany(MutasiStok::class, 'kios_id');
    }

    /**
     * Relasi ke model LogAktivitas.
     */
    public function logAktivitas(): HasMany
    {
        return $this->hasMany(LogAktivitas::class, 'kios_id');
    }

    /**
     * Relasi ke model Supplier.
     */
    public function supplier(): HasMany
    {
        return $this->hasMany(Supplier::class, 'kios_id');
    }

    /**
     * Relasi ke transaksi BarangMasuk.
     */
    public function barangMasuk(): HasMany
    {
        return $this->hasMany(BarangMasuk::class, 'kios_id');
    }

    /**
     * Relasi ke transaksi PenyesuaianStok.
     */
    public function penyesuaianStok(): HasMany
    {
        return $this->hasMany(PenyesuaianStok::class, 'kios_id');
    }

    /**
     * Relasi ke transaksi Penjualan.
     */
    public function penjualan(): HasMany
    {
        return $this->hasMany(Penjualan::class, 'kios_id');
    }

    /**
     * Relasi ke shift kasir.
     */
    public function shiftKasir(): HasMany
    {
        return $this->hasMany(ShiftKasir::class, 'kios_id');
    }
}
