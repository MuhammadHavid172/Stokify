<?php

namespace App\Models;

use App\Models\Concerns\BelongsToKios;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penjualan extends Model
{
    use HasFactory, BelongsToKios;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'penjualan';

    /**
     * Kolom yang dapat diisi massal.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * Casting tipe data kolom.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'waktu_transaksi' => 'datetime',
        'total' => 'integer',
        'bayar' => 'integer',
        'kembalian' => 'integer',
        'total_modal' => 'integer',
        'dibatalkan_pada' => 'datetime',
    ];

    /**
     * Relasi ke kasir / pengguna yang melayani transaksi.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke shift kasir tempat transaksi ini dicatat.
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftKasir::class, 'shift_id');
    }

    /**
     * Relasi ke pengguna yang membatalkan transaksi (jika ada).
     */
    public function dibatalkanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibatalkan_oleh');
    }

    /**
     * Relasi ke detail rincian barang penjualan.
     */
    public function detail(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class, 'penjualan_id');
    }

    /**
     * Alias relasi rincian (jamak).
     */
    public function details(): HasMany
    {
        return $this->detail();
    }

    /**
     * Hitung laba kotor transaksi (total penjualan - total modal HPP).
     */
    protected function labaKotor(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) $this->total - (int) $this->total_modal,
        );
    }
}
