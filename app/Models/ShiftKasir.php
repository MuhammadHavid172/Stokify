<?php

namespace App\Models;

use App\Models\Concerns\BelongsToKios;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftKasir extends Model
{
    use HasFactory, BelongsToKios;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'shift_kasir';

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
        'waktu_buka' => 'datetime',
        'waktu_tutup' => 'datetime',
        'modal_awal' => 'integer',
        'uang_fisik' => 'integer',
        'uang_seharusnya' => 'integer',
        'selisih' => 'integer',
        'jumlah_transaksi' => 'integer',
        'total_penjualan_tunai' => 'integer',
        'jumlah_nota_batal' => 'integer',
        'ditutup_paksa' => 'boolean',
    ];

    /**
     * Relasi ke kasir / pemilik shift.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke pengguna yang menutup shift (jika ditutup).
     */
    public function ditutupOlehUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditutup_oleh');
    }

    /**
     * Relasi ke transaksi penjualan yang terjadi pada shift ini.
     */
    public function penjualan(): HasMany
    {
        return $this->hasMany(Penjualan::class, 'shift_id');
    }

    /**
     * Scope untuk shift yang sedang berjalan.
     */
    public function scopeBerjalan($query)
    {
        return $query->where('status', 'berjalan');
    }

    /**
     * Scope untuk shift yang sudah ditutup.
     */
    public function scopeDitutup($query)
    {
        return $query->where('status', 'ditutup');
    }

    /**
     * Accessor durasi shift kasir dalam format mudah dibaca.
     */
    protected function durasi(): Attribute
    {
        return Attribute::make(
            get: function () {
                $start = $this->waktu_buka ? Carbon::parse($this->waktu_buka)->timezone('Asia/Jayapura') : Carbon::now('Asia/Jayapura');
                $end = $this->waktu_tutup
                    ? Carbon::parse($this->waktu_tutup)->timezone('Asia/Jayapura')
                    : Carbon::now('Asia/Jayapura');

                $diffMinutes = max(0, $start->diffInMinutes($end));
                $hours = (int) floor($diffMinutes / 60);
                $minutes = (int) ($diffMinutes % 60);

                if ($hours > 0) {
                    return "{$hours} jam {$minutes} menit";
                }

                return "{$minutes} menit";
            }
        );
    }

    /**
     * Accessor uang seharusnya secara live untuk shift yang masih berjalan.
     */
    public function getUangSeharusnyaLiveAttribute(): int
    {
        if ($this->status === 'ditutup' && $this->uang_seharusnya !== null) {
            return (int) $this->uang_seharusnya;
        }

        $totalTunai = (int) $this->penjualan()
            ->where('status', 'selesai')
            ->sum('total');

        return (int) $this->modal_awal + $totalTunai;
    }

    /**
     * Cek apakah shift berjalan sudah lebih dari 16 jam.
     */
    public function getIsLebihDari16JamAttribute(): bool
    {
        if ($this->status !== 'berjalan' || ! $this->waktu_buka) {
            return false;
        }

        $start = Carbon::parse($this->waktu_buka)->timezone('Asia/Jayapura');
        return $start->lessThanOrEqualTo(Carbon::now('Asia/Jayapura')->subHours(16));
    }
}
