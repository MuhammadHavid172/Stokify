<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\MutasiStok;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StokService
{
    /**
     * Catat mutasi stok dan perbarui stok barang dalam transaksi database yang aman dan terkunci.
     *
     * @param Barang $barang Model barang yang akan diubah stoknya
     * @param string $tipe Tipe mutasi: stok_awal, barang_masuk, penjualan, void_penjualan, penyesuaian
     * @param int $jumlah Nilai perubahan stok bertanda (+ untuk menambah, - untuk mengurangi)
     * @param string|null $keterangan Keterangan tambahan opsional
     * @param mixed $referensi Dokumen asal mutasi (misal model transaksi/order)
     * @return MutasiStok
     *
     * @throws InvalidArgumentException Jika stok sesudah mutasi bernilai negatif (< 0)
     */
    public function catat(
        Barang $barang,
        string $tipe,
        int $jumlah,
        ?string $keterangan = null,
        mixed $referensi = null,
        ?\DateTimeInterface $waktu = null
    ): MutasiStok {
        return DB::transaction(function () use ($barang, $tipe, $jumlah, $keterangan, $referensi, $waktu) {
            // Kunci baris barang untuk update (mencegah race condition antar transaksi)
            $lockedBarang = Barang::withoutGlobalScopes()
                ->where('id', $barang->id)
                ->lockForUpdate()
                ->firstOrFail();

            $stokSebelum = (int) $lockedBarang->stok;
            $stokSesudah = $stokSebelum + $jumlah;

            // Validasi siklus stok tertutup: stok tidak boleh menjadi negatif
            if ($stokSesudah < 0) {
                throw new InvalidArgumentException(
                    "Stok tidak mencukupi untuk barang '{$lockedBarang->nama}'. Stok saat ini ({$stokSebelum}) tidak boleh kurang dari pengurangan (" . abs($jumlah) . ")."
                );
            }

            // Perbarui stok pada barang
            $lockedBarang->stok = $stokSesudah;
            $lockedBarang->save();

            // Sinkronkan juga objek $barang yang dikirim dari pemanggil
            $barang->stok = $stokSesudah;

            // Tentukan user pencatat mutasi
            $userId = Auth::id() ?? $lockedBarang->kios->users()->first()?->id ?? 1;

            // Tulis baris mutasi stok
            $mutasi = MutasiStok::withoutGlobalScopes()->create([
                'kios_id' => $lockedBarang->kios_id,
                'barang_id' => $lockedBarang->id,
                'user_id' => $userId,
                'tipe' => $tipe,
                'jumlah' => $jumlah,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $stokSesudah,
                'keterangan' => $keterangan,
                'referensi_type' => $referensi ? get_class($referensi) : null,
                'referensi_id' => $referensi ? $referensi->id : null,
            ]);

            if ($waktu) {
                $mutasi->created_at = $waktu;
                $mutasi->updated_at = $waktu;
                $mutasi->saveQuietly();
            }

            return $mutasi;
        });
    }

    /**
     * Memungkinkan pemanggilan method secara statis, misalnya:
     * StokService::catat($barang, 'stok_awal', 10, 'Keterangan');
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return (new static)->$name(...$arguments);
    }
}
