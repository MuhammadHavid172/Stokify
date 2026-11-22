<?php

namespace App\Models;

use App\Models\Concerns\BelongsToKios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPenyesuaianStok extends Model
{
    use HasFactory, BelongsToKios;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'detail_penyesuaian_stok';

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kios_id',
        'penyesuaian_stok_id',
        'barang_id',
        'jenis',
        'jumlah',
        'stok_sebelum',
        'stok_sesudah',
        'harga_modal',
        'nilai',
        'alasan',
    ];

    /**
     * Casting tipe data kolom.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'jumlah' => 'integer',
        'stok_sebelum' => 'integer',
        'stok_sesudah' => 'integer',
        'harga_modal' => 'integer',
        'nilai' => 'integer',
    ];

    /**
     * Relasi ke header PenyesuaianStok.
     */
    public function penyesuaianStok(): BelongsTo
    {
        return $this->belongsTo(PenyesuaianStok::class, 'penyesuaian_stok_id');
    }

    /**
     * Relasi ke model Barang.
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}
