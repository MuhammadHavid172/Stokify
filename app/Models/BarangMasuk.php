<?php

namespace App\Models;

use App\Models\Concerns\BelongsToKios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class BarangMasuk extends Model
{
    use HasFactory, BelongsToKios;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'barang_masuk';

    /**
     * Kolom yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kios_id',
        'supplier_id',
        'user_id',
        'nomor',
        'tanggal',
        'catatan',
        'total',
        'status',
        'dibatalkan_oleh',
        'dibatalkan_pada',
        'alasan_batal',
    ];

    /**
     * Casting tipe data kolom.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal' => 'date',
        'dibatalkan_pada' => 'datetime',
        'total' => 'integer',
    ];

    /**
     * Relasi ke model Supplier.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Relasi ke user / petugas pencatat nota.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke user / admin yang membatalkan nota.
     */
    public function pembatal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibatalkan_oleh');
    }

    /**
     * Relasi ke baris-baris detail barang masuk.
     */
    public function detail(): HasMany
    {
        return $this->hasMany(DetailBarangMasuk::class, 'barang_masuk_id');
    }

    /**
     * Relasi polimorfik ke mutasi stok yang tercipta dari dokumen nota ini.
     */
    public function mutasiStok(): MorphMany
    {
        return $this->morphMany(MutasiStok::class, 'referensi');
    }
}
