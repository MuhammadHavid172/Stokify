<?php

namespace App\Models\Concerns;

use App\Models\Kios;
use App\Models\Scopes\KiosScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Trait BelongsToKios
 *
 * Trait fondasi isolasi data multi-tenant per kios:
 * 1. Menerapkan KiosScope secara global pada model.
 * 2. Mengisi otomatis kios_id saat event creating jika kios_id belum diisi dan user sedang login.
 * 3. Menyediakan relasi kios() belongsTo ke model Kios.
 *
 * PENGGUNAAN PADA TAHAP BERIKUTNYA:
 * Pasang trait ini pada model data operasional kios (misal: Barang, Kategori, Transaksi, Shift, dll):
 *
 *   use App\Models\Concerns\BelongsToKios;
 *
 *   class Barang extends Model
 *   {
 *       use BelongsToKios;
 *   }
 *
 * PERINGATAN (Jebakan Umum):
 * JANGAN gunakan trait ini pada model User!
 * Scope memanggil Auth::user(), sedangkan Laravel memuat user lewat query ke model User,
 * sehingga akan terjadi rekursi tak berujung.
 * Untuk data user, filter manual via relasi: auth()->user()->kios->users().
 */
trait BelongsToKios
{
    /**
     * Boot trait BelongsToKios.
     */
    public static function bootBelongsToKios(): void
    {
        // 1. Terapkan Global Scope KiosScope
        static::addGlobalScope(new KiosScope);

        // 2. Auto-fill dan proteksi kios_id: jika ada user login, kunci selalu ke kios miliknya
        static::creating(function ($model) {
            if (Auth::check() && Auth::user()->kios_id) {
                $model->kios_id = Auth::user()->kios_id;
            }
        });
    }

    /**
     * Relasi belongsTo ke Kios pemilik data.
     */
    public function kios(): BelongsTo
    {
        return $this->belongsTo(Kios::class, 'kios_id');
    }
}
