<?php

namespace App\Models;

use App\Models\Concerns\BelongsToKios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    use HasFactory, BelongsToKios;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'kategori';

    /**
     * Kolom yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kios_id',
        'nama',
    ];

    /**
     * Relasi ke model Barang.
     */
    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class, 'kategori_id');
    }

    /**
     * Menghasilkan kode awalan (prefix 3 huruf kapital) berdasarkan nama kategori.
     * Contoh:
     * - Sembako -> SMB
     * - Rokok -> ROK
     * - Minuman -> MNM
     * - Makanan -> MKN
     */
    public function getPrefixKode(): string
    {
        $raw = strtoupper(trim((string) $this->nama));

        // Peta istilah umum kios/ritel Indonesia
        $map = [
            'SEMBAKO' => 'SMB',
            'MINUMAN' => 'MNM',
            'ROKOK' => 'ROK',
            'MAKANAN' => 'MKN',
            'SNACK' => 'SNK',
            'SABUN' => 'SBN',
            'OBAT' => 'OBT',
            'BUMBU' => 'BMB',
            'PULSA' => 'PLS',
            'HARIAN' => 'HRN',
            'KEBUTUHAN HARIAN' => 'KBH',
            'MAKANAN RINGAN' => 'MKR',
            'ALAT TULIS' => 'ATK',
        ];

        if (isset($map[$raw])) {
            return $map[$raw];
        }

        $clean = preg_replace('/[^A-Z0-9\s]/', '', $raw);
        $words = array_values(array_filter(explode(' ', $clean)));

        if (empty($words)) {
            return 'BRG';
        }

        // Jika kata pertama ada di daftar istilah umum (misal: "Minuman Dingin" -> MNM)
        if (isset($map[$words[0]])) {
            return $map[$words[0]];
        }

        if (count($words) === 1) {
            $w = $words[0];
            if (isset($map[$w])) {
                return $map[$w];
            }
            if (strlen($w) <= 3) {
                return str_pad($w, 3, 'X');
            }
            // Huruf pertama + konsonan berikutnya
            $first = substr($w, 0, 1);
            $consonants = preg_replace('/[AEIOU]/', '', substr($w, 1));
            $candidate = $first . $consonants;
            if (strlen($candidate) >= 3) {
                return substr($candidate, 0, 3);
            }
            return substr($w, 0, 3);
        }

        // Multi kata
        if (count($words) >= 3) {
            return substr($words[0], 0, 1) . substr($words[1], 0, 1) . substr($words[2], 0, 1);
        }

        return substr($words[0], 0, 2) . substr($words[1], 0, 1);
    }
}
