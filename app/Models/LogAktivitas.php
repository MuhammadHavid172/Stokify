<?php

namespace App\Models;

use App\Models\Concerns\BelongsToKios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class LogAktivitas extends Model
{
    use HasFactory, BelongsToKios;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'log_aktivitas';

    /**
     * Kolom yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kios_id',
        'user_id',
        'aksi',
        'deskripsi',
        'data_lama',
        'data_baru',
    ];

    /**
     * Casting tipe data kolom.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data_lama' => 'array',
        'data_baru' => 'array',
    ];

    /**
     * Relasi ke user / petugas yang melakukan aktivitas.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper praktis untuk mencatat log aktivitas secara otomatis.
     */
    public static function catat(string $aksi, string $deskripsi, mixed $lama = null, mixed $baru = null): self
    {
        $user = Auth::user();

        return static::create([
            'kios_id' => $user?->kios_id,
            'user_id' => $user?->id,
            'aksi' => $aksi,
            'deskripsi' => $deskripsi,
            'data_lama' => $lama,
            'data_baru' => $baru,
        ]);
    }
}
