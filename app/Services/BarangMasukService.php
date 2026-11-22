<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\DetailBarangMasuk;
use App\Models\LogAktivitas;
use App\Models\MutasiStok;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BarangMasukService
{
    public function __construct(protected StokService $stokService)
    {
    }

    /**
     * Simpan transaksi barang masuk dalam satu transaksi database terkunci.
     *
     * @param array $data Input form: tanggal, supplier_id, catatan, items (array of [barang_id, jumlah, harga_beli])
     * @param User $user Petugas admin yang mencatat transaksi
     * @return BarangMasuk
     * @throws InvalidArgumentException
     */
    public function simpan(array $data, User $user): BarangMasuk
    {
        return DB::transaction(function () use ($data, $user) {
            $rawItems = $data['items'] ?? [];
            if (empty($rawItems)) {
                throw new InvalidArgumentException('Daftar barang masuk tidak boleh kosong.');
            }

            // Tangani barang duplikat: gabungkan jumlah jika ada item dengan barang_id sama
            $consolidated = [];
            foreach ($rawItems as $item) {
                $bId = (int) $item['barang_id'];
                $qty = (int) $item['jumlah'];
                $price = (int) $item['harga_beli'];

                if ($qty < 1) {
                    throw new InvalidArgumentException('Jumlah barang masuk minimal 1.');
                }
                if ($price < 0) {
                    throw new InvalidArgumentException('Harga beli tidak boleh negatif.');
                }

                if (isset($consolidated[$bId])) {
                    $oldQty = $consolidated[$bId]['jumlah'];
                    $oldPrice = $consolidated[$bId]['harga_beli'];
                    $newQty = $oldQty + $qty;
                    // Rata-rata tertimbang harga beli jika duplikat dimasukkan
                    $newPrice = (int) round((($oldQty * $oldPrice) + ($qty * $price)) / $newQty);

                    $consolidated[$bId]['jumlah'] = $newQty;
                    $consolidated[$bId]['harga_beli'] = $newPrice;
                    $consolidated[$bId]['subtotal'] = $newQty * $newPrice;
                } else {
                    $consolidated[$bId] = [
                        'barang_id' => $bId,
                        'jumlah' => $qty,
                        'harga_beli' => $price,
                        'subtotal' => $qty * $price,
                    ];
                }
            }

            // Urutkan item berdasarkan barang_id agar urutan penguncian baris konsisten (mencegah deadlock)
            $items = array_values($consolidated);
            usort($items, fn ($a, $b) => $a['barang_id'] <=> $b['barang_id']);

            // Hitung total di server
            $total = 0;
            foreach ($items as $item) {
                $total += $item['subtotal'];
            }

            // Buat nomor nota berformat BM-YYYYMMDD-0001 urut per kios per hari
            $tanggal = isset($data['tanggal']) ? Carbon::parse($data['tanggal']) : Carbon::today();
            $nomor = $this->generateNomorNota($user->kios_id, $tanggal);

            // Buat header BarangMasuk
            $barangMasuk = BarangMasuk::withoutGlobalScopes()->create([
                'kios_id' => $user->kios_id,
                'supplier_id' => $data['supplier_id'] ?? null,
                'user_id' => $user->id,
                'nomor' => $nomor,
                'tanggal' => $tanggal->toDateString(),
                'catatan' => $data['catatan'] ?? null,
                'total' => $total,
                'status' => 'selesai',
            ]);

            // Proses setiap item
            foreach ($items as $item) {
                // Kunci baris barang untuk update
                $barang = Barang::withoutGlobalScopes()
                    ->where('id', $item['barang_id'])
                    ->where('kios_id', $user->kios_id)
                    ->where('is_aktif', true)
                    ->lockForUpdate()
                    ->first();

                if (! $barang) {
                    throw new InvalidArgumentException("Barang dengan ID {$item['barang_id']} tidak valid, nonaktif, atau bukan milik kios Anda.");
                }

                $stokSebelum = (int) $barang->stok;
                $hargaModalSebelum = (int) $barang->harga_modal;
                $jumlahMasuk = (int) $item['jumlah'];
                $hargaBeli = (int) $item['harga_beli'];
                $subtotal = $jumlahMasuk * $hargaBeli;

                // Hitung harga modal baru dengan RATA-RATA TERTIMBANG:
                // modal_baru = ((stok_lama x modal_lama) + (jumlah x harga_beli)) / (stok_lama + jumlah)
                // Pakai stok_lama max(0, stok). Jika stok_lama <= 0, modal_baru = harga_beli.
                $stokLamaEfektif = max(0, $stokSebelum);
                if ($stokLamaEfektif <= 0) {
                    $modalBaru = $hargaBeli;
                } else {
                    $modalBaru = (int) round((($stokLamaEfektif * $hargaModalSebelum) + ($jumlahMasuk * $hargaBeli)) / ($stokLamaEfektif + $jumlahMasuk));
                }

                // Update harga modal barang jika ada perubahan nilai
                if ($modalBaru !== $hargaModalSebelum) {
                    $barang->harga_modal = $modalBaru;
                    $barang->save();

                    LogAktivitas::withoutGlobalScopes()->create([
                        'kios_id' => $user->kios_id,
                        'user_id' => $user->id,
                        'aksi' => 'barang.ubah_harga',
                        'deskripsi' => "Harga modal '{$barang->nama}' ({$barang->kode}) diperbarui otomatis dari barang masuk {$nomor}",
                        'data_lama' => ['harga_modal' => $hargaModalSebelum],
                        'data_baru' => ['harga_modal' => $modalBaru],
                    ]);
                }

                // Tambah stok melalui StokService
                $this->stokService->catat(
                    $barang,
                    'barang_masuk',
                    $jumlahMasuk,
                    "Barang masuk {$nomor}",
                    $barangMasuk
                );

                // Catat baris detail barang masuk
                DetailBarangMasuk::withoutGlobalScopes()->create([
                    'kios_id' => $user->kios_id,
                    'barang_masuk_id' => $barangMasuk->id,
                    'barang_id' => $barang->id,
                    'jumlah' => $jumlahMasuk,
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $subtotal,
                    'harga_modal_sebelum' => $hargaModalSebelum,
                    'stok_sebelum' => $stokSebelum,
                ]);
            }

            // Catat log aktivitas barang masuk
            LogAktivitas::withoutGlobalScopes()->create([
                'kios_id' => $user->kios_id,
                'user_id' => $user->id,
                'aksi' => 'barang_masuk.tambah',
                'deskripsi' => "Mencatat barang masuk nomor {$barangMasuk->nomor} total " . rupiah($barangMasuk->total),
                'data_lama' => null,
                'data_baru' => [
                    'nomor' => $barangMasuk->nomor,
                    'total' => $barangMasuk->total,
                    'item_count' => count($items),
                ],
            ]);

            return $barangMasuk;
        });
    }

    /**
     * Batalkan nota barang masuk (void).
     *
     * @param BarangMasuk $barangMasuk Nota yang ingin dibatalkan
     * @param string $alasan Alasan pembatalan (minimal 5 karakter)
     * @param User $user Admin yang membatalkan nota
     * @return BarangMasuk
     * @throws InvalidArgumentException
     */
    public function batalkan(BarangMasuk $barangMasuk, string $alasan, User $user): BarangMasuk
    {
        return DB::transaction(function () use ($barangMasuk, $alasan, $user) {
            // Kunci nota untuk update
            $lockedNota = BarangMasuk::withoutGlobalScopes()
                ->where('id', $barangMasuk->id)
                ->where('kios_id', $user->kios_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedNota->status === 'dibatalkan') {
                throw new InvalidArgumentException("Nota {$lockedNota->nomor} sudah dibatalkan sebelumnya dan tidak dapat dibatalkan lagi.");
            }

            if (strlen(trim($alasan)) < 5) {
                throw new InvalidArgumentException('Alasan pembatalan nota wajib diisi minimal 5 karakter.');
            }

            // Ambil detail diurutkan berdasarkan barang_id untuk mencegah deadlock
            $details = $lockedNota->detail()
                ->orderBy('barang_id', 'asc')
                ->get();

            // 1. TAHAP VALIDASI: Periksa apakah semua stok barang masih mencukupi untuk ditarik kembali
            foreach ($details as $detail) {
                $barang = Barang::withoutGlobalScopes()
                    ->where('id', $detail->barang_id)
                    ->where('kios_id', $user->kios_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($barang->stok < $detail->jumlah) {
                    throw new InvalidArgumentException(
                        "Stok {$barang->nama} tidak cukup untuk dibatalkan; sebagian barang sudah terjual. Gunakan Penyesuaian Stok."
                    );
                }
            }

            // 2. TAHAP EKSEKUSI: Kurangi stok dan kembalikan harga modal jika aman
            foreach ($details as $detail) {
                $barang = Barang::withoutGlobalScopes()
                    ->where('id', $detail->barang_id)
                    ->where('kios_id', $user->kios_id)
                    ->firstOrFail();

                // Cari mutasi stok asal dari nota ini
                $mutasiAsal = MutasiStok::withoutGlobalScopes()
                    ->where('kios_id', $user->kios_id)
                    ->where('barang_id', $barang->id)
                    ->where('referensi_type', BarangMasuk::class)
                    ->where('referensi_id', $lockedNota->id)
                    ->where('tipe', 'barang_masuk')
                    ->first();

                // Kurangi stok lewat StokService bertipe 'penyesuaian'
                $this->stokService->catat(
                    $barang,
                    'penyesuaian',
                    -$detail->jumlah,
                    "Pembatalan {$lockedNota->nomor}: {$alasan}",
                    $lockedNota
                );

                // Pendekatan Harga Modal Saat Pembatalan:
                // Harga modal dikembalikan ke harga_modal_sebelum HANYA jika tidak ada mutasi stok lain
                // yang terjadi pada barang tersebut setelah nota ini.
                $adaMutasiSesudahnya = false;
                if ($mutasiAsal) {
                    $adaMutasiSesudahnya = MutasiStok::withoutGlobalScopes()
                        ->where('kios_id', $user->kios_id)
                        ->where('barang_id', $barang->id)
                        ->where('id', '>', $mutasiAsal->id)
                        ->where(function ($q) use ($lockedNota) {
                            $q->where('referensi_type', '!=', BarangMasuk::class)
                                ->orWhere('referensi_id', '!=', $lockedNota->id);
                        })
                        ->exists();
                }

                if (! $adaMutasiSesudahnya) {
                    $barang->harga_modal = $detail->harga_modal_sebelum;
                    $barang->save();

                    LogAktivitas::withoutGlobalScopes()->create([
                        'kios_id' => $user->kios_id,
                        'user_id' => $user->id,
                        'aksi' => 'barang.ubah_harga',
                        'deskripsi' => "Harga modal '{$barang->nama}' dikembalikan ke " . rupiah($detail->harga_modal_sebelum) . " karena pembatalan nota {$lockedNota->nomor}",
                        'data_lama' => null,
                        'data_baru' => ['harga_modal' => $detail->harga_modal_sebelum],
                    ]);
                }
            }

            // Update status nota menjadi dibatalkan
            $lockedNota->status = 'dibatalkan';
            $lockedNota->dibatalkan_oleh = $user->id;
            $lockedNota->dibatalkan_pada = now();
            $lockedNota->alasan_batal = $alasan;
            $lockedNota->save();

            // Catat log aktivitas pembatalan nota
            LogAktivitas::withoutGlobalScopes()->create([
                'kios_id' => $user->kios_id,
                'user_id' => $user->id,
                'aksi' => 'barang_masuk.batal',
                'deskripsi' => "Membatalkan nota barang masuk {$lockedNota->nomor}. Alasan: {$alasan}",
                'data_lama' => ['status' => 'selesai'],
                'data_baru' => [
                    'status' => 'dibatalkan',
                    'alasan' => $alasan,
                    'dibatalkan_pada' => $lockedNota->dibatalkan_pada->toDateTimeString(),
                ],
            ]);

            return $lockedNota;
        });
    }

    /**
     * Helper untuk membuat nomor nota BM-YYYYMMDD-0001 unik per kios per hari.
     */
    protected function generateNomorNota(int $kiosId, Carbon $tanggal): string
    {
        $prefix = 'BM-' . $tanggal->format('Ymd') . '-';

        $latest = BarangMasuk::withoutGlobalScopes()
            ->where('kios_id', $kiosId)
            ->where('nomor', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        $seq = 1;
        if ($latest && preg_match('/-(\d{4})$/', $latest->nomor, $matches)) {
            $seq = ((int) $matches[1]) + 1;
        }

        return sprintf('%s%04d', $prefix, $seq);
    }
}
