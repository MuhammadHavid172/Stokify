<?php

namespace App\Models;

use App\Models\Concerns\BelongsToKios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPenjualan extends Model
{
    use HasFactory, BelongsToKios;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'detail_penjualan';

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
        'jumlah' => 'integer',
        'harga_jual' => 'integer',
        'harga_modal' => 'integer',
        'subtotal' => 'integer',
        'subtotal_modal' => 'integer',
    ];

    /**
     * Relasi ke transaksi penjualan header.
     */
    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    /**
     * Relasi ke barang persediaan.
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}
