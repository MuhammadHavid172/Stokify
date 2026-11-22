<?php

namespace App\Models;

use App\Models\Concerns\BelongsToKios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailBarangMasuk extends Model
{
    use HasFactory, BelongsToKios;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'detail_barang_masuk';

    /**
     * Kolom yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kios_id',
        'barang_masuk_id',
        'barang_id',
        'jumlah',
        'harga_beli',
        'subtotal',
        'harga_modal_sebelum',
        'stok_sebelum',
    ];

    /**
     * Casting tipe data kolom.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'jumlah' => 'integer',
        'harga_beli' => 'integer',
        'subtotal' => 'integer',
        'harga_modal_sebelum' => 'integer',
        'stok_sebelum' => 'integer',
    ];

    /**
     * Relasi ke header transaksi barang masuk.
     */
    public function barangMasuk(): BelongsTo
    {
        return $this->belongsTo(BarangMasuk::class, 'barang_masuk_id');
    }

    /**
     * Relasi ke model Barang.
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}
