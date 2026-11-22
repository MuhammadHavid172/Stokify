<?php

namespace App\Models;

use App\Models\Concerns\BelongsToKios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PenyesuaianStok extends Model
{
    use HasFactory, BelongsToKios;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'penyesuaian_stok';

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kios_id',
        'user_id',
        'nomor',
        'tanggal',
        'mode',
        'catatan',
        'total_kerugian',
    ];

    /**
     * Casting tipe data kolom.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal' => 'date',
        'total_kerugian' => 'integer',
    ];

    /**
     * Relasi ke user / petugas pencatat penyesuaian.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke rincian item penyesuaian stok.
     */
    public function detail(): HasMany
    {
        return $this->hasMany(DetailPenyesuaianStok::class, 'penyesuaian_stok_id');
    }

    /**
     * Relasi ke mutasi stok (polymorphic morphMany).
     */
    public function mutasiStok(): MorphMany
    {
        return $this->morphMany(MutasiStok::class, 'referensi');
    }
}
