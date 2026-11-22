<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\DetailPenjualan;
use App\Models\LogAktivitas;
use App\Models\Penjualan;
use App\Models\ShiftKasir;
use App\Models\User;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PenjualanService
{
    public function __construct(protected StokService $stokService)
    {
    }

    /**
     * Buat transaksi penjualan baru secara atomik (satu DB transaction)
     * Idempoten berdasarkan token untuk mencegah submit ganda.
     * Wajib memiliki shift kasir yang sedang berjalan.
     *
     * @param array $items Array daftar item: [['barang_id' => x, 'jumlah' => y], ...]
     * @param int $bayar Jumlah nominal uang tunai yang diterima
     * @param string $token Token unik UUID idempotency
     * @param User|null $user Petugas kasir/admin (opsional, default Auth::user())
     * @param DateTimeInterface|null $waktuTransaksi Waktu transaksi (opsional, default now('Asia/Jayapura'))
     * @return Penjualan
     * @throws ValidationException
     */
    public function buat(
        array $items,
        int $bayar,
        string $token,
        ?User $user = null,
        ?DateTimeInterface $waktuTransaksi = null
    ): Penjualan {
        $user = $user ?? Auth::user();
        if (! $user) {
            throw ValidationException::withMessages([
                'auth' => 'Pengguna tidak terotentikasi.',
            ]);
        }

        $kiosId = $user->kios_id;
        $token = trim($token);

        if (empty($token)) {
            throw ValidationException::withMessages([
                'token' => 'Token transaksi wajib diisi.',
            ]);
        }

        // 1. Cek idempotensi token sebelum transaksi
        $existing = Penjualan::withoutGlobalScopes()
            ->where('kios_id', $kiosId)
            ->where('token', $token)
            ->with('detail')
            ->first();

        if ($existing) {
            return $existing;
        }

        // 2. Gabungkan baris duplikat barang_id dan validasi kuantitas
        $mergedItems = [];
        foreach ($items as $item) {
            $bId = (int) ($item['barang_id'] ?? 0);
            $qty = (int) ($item['jumlah'] ?? 0);

            if ($bId <= 0 || $qty <= 0) {
                continue;
            }

            $mergedItems[$bId] = ($mergedItems[$bId] ?? 0) + $qty;
        }

        if (empty($mergedItems)) {
            throw ValidationException::withMessages([
                'items' => 'Daftar belanjaan tidak boleh kosong.',
            ]);
        }

        // Urutkan item berdasarkan barang_id secara konsisten untuk mencegah deadlock
        ksort($mergedItems);

        return DB::transaction(function () use ($kiosId, $user, $mergedItems, $bayar, $token, $waktuTransaksi) {
            // Wajib punya shift berjalan. Kunci baris shift pengguna untuk mencegah balapan dengan tutup shift.
            $shift = ShiftKasir::withoutGlobalScopes()
                ->where('kios_id', $kiosId)
                ->where('user_id', $user->id)
                ->where('status', 'berjalan')
                ->lockForUpdate()
                ->first();

            if (! $shift) {
                throw ValidationException::withMessages([
                    'shift' => 'Buka shift terlebih dahulu.',
                ]);
            }

            // Cek ulang token di dalam transaksi untuk antisipasi race condition submit ganda
            $existingInside = Penjualan::withoutGlobalScopes()
                ->where('kios_id', $kiosId)
                ->where('token', $token)
                ->lockForUpdate()
                ->first();

            if ($existingInside) {
                return $existingInside->load('detail');
            }

            $preparedLines = [];
            $total = 0;
            $totalModal = 0;

            // 3. Kunci setiap baris barang, validasi kepemilikan kios, status aktif, dan stok
            foreach ($mergedItems as $bId => $qty) {
                $barang = Barang::withoutGlobalScopes()
                    ->where('id', $bId)
                    ->lockForUpdate()
                    ->first();

                // Validasi kepemilikan kios & keaktifan
                if (! $barang || (int) $barang->kios_id !== (int) $kiosId || ! $barang->is_aktif) {
                    throw ValidationException::withMessages([
                        'items' => 'Barang tidak valid atau tidak aktif.',
                    ]);
                }

                // Validasi kecukupan stok
                if ((int) $barang->stok < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$barang->nama} tidak cukup (tersisa {$barang->stok})",
                    ]);
                }

                // Ambil harga jual dan modal langsung dari database snapshot
                $hargaJual = (int) $barang->harga_jual;
                $hargaModal = (int) $barang->harga_modal;
                $subtotal = $qty * $hargaJual;
                $subtotalModal = $qty * $hargaModal;

                $total += $subtotal;
                $totalModal += $subtotalModal;

                $preparedLines[] = [
                    'barang' => $barang,
                    'qty' => $qty,
                    'harga_jual' => $hargaJual,
                    'harga_modal' => $hargaModal,
                    'subtotal' => $subtotal,
                    'subtotal_modal' => $subtotalModal,
                ];
            }

            // 4. Validasi pembayaran tunai
            if ($bayar < $total) {
                throw ValidationException::withMessages([
                    'bayar' => 'Uang bayar kurang dari total belanja.',
                ]);
            }

            $kembalian = $bayar - $total;
            $waktu = $waktuTransaksi ? Carbon::parse($waktuTransaksi) : Carbon::now('Asia/Jayapura');

            // 5. Generate nomor nota urut harian per kios
            $nomor = $this->generateNomor($kiosId, $waktu);

            // Simpan header penjualan
            $penjualan = Penjualan::withoutGlobalScopes()->create([
                'kios_id' => $kiosId,
                'user_id' => $user->id,
                'shift_id' => $shift->id,
                'nomor' => $nomor,
                'waktu_transaksi' => $waktu,
                'total' => $total,
                'bayar' => $bayar,
                'kembalian' => $kembalian,
                'total_modal' => $totalModal,
                'metode_bayar' => 'tunai',
                'status' => 'selesai',
                'token' => $token,
            ]);

            // Simpan detail dan potong stok
            foreach ($preparedLines as $line) {
                $barang = $line['barang'];
                $qty = $line['qty'];

                DetailPenjualan::withoutGlobalScopes()->create([
                    'kios_id' => $kiosId,
                    'penjualan_id' => $penjualan->id,
                    'barang_id' => $barang->id,
                    'kode_barang' => $barang->kode,
                    'nama_barang' => $barang->nama,
                    'satuan' => $barang->satuan,
                    'jumlah' => $qty,
                    'harga_jual' => $line['harga_jual'],
                    'harga_modal' => $line['harga_modal'],
                    'subtotal' => $line['subtotal'],
                    'subtotal_modal' => $line['subtotal_modal'],
                ]);

                // Kurangi stok lewat StokService
                $this->stokService->catat(
                    $barang,
                    'penjualan',
                    -$qty,
                    "Penjualan {$penjualan->nomor}",
                    $penjualan
                );
            }

            return $penjualan->load('detail');
        });
    }

    /**
     * Batalkan (void) nota transaksi penjualan secara atomik (satu DB transaction).
     *
     * @param Penjualan $penjualan
     * @param string $alasan
     * @param User|null $user Petugas yang melakukan pembatalan (kasir/admin)
     * @return Penjualan
     * @throws ValidationException
     */
    public function batalkan(Penjualan $penjualan, string $alasan, ?User $user = null): Penjualan
    {
        $user = $user ?? Auth::user();
        if (! $user) {
            throw ValidationException::withMessages([
                'auth' => 'Pengguna tidak terotentikasi.',
            ]);
        }

        $alasan = trim($alasan);
        if (mb_strlen($alasan) < 5) {
            throw ValidationException::withMessages([
                'alasan' => 'Alasan pembatalan wajib diisi minimal 5 karakter.',
            ]);
        }

        // Multi-tenant check
        if ((int) $penjualan->kios_id !== (int) $user->kios_id) {
            abort(404);
        }

        // Hak akses: kasir hanya boleh membatalkan nota miliknya sendiri
        if ($user->isKasir() && (int) $penjualan->user_id !== (int) $user->id) {
            abort(403, 'Anda tidak berhak membatalkan nota kasir lain.');
        }

        return DB::transaction(function () use ($penjualan, $alasan, $user) {
            // 1. Kunci baris penjualan
            $lockedPenjualan = Penjualan::withoutGlobalScopes()
                ->where('id', $penjualan->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPenjualan->status === 'dibatalkan') {
                throw ValidationException::withMessages([
                    'penjualan' => 'Nota sudah dibatalkan sebelumnya.',
                ]);
            }

            // Kunci baris shift milik nota
            $shift = null;
            if ($lockedPenjualan->shift_id) {
                $shift = ShiftKasir::withoutGlobalScopes()
                    ->where('id', $lockedPenjualan->shift_id)
                    ->lockForUpdate()
                    ->first();
            }

            if (! $shift || $shift->status !== 'berjalan') {
                throw ValidationException::withMessages([
                    'shift' => 'Shift sudah ditutup, nota tidak dapat dibatalkan.',
                ]);
            }

            // 4. Untuk tiap detail (urut barang_id agar urutan lock konsisten): kembalikan stok lewat StokService
            $details = DetailPenjualan::withoutGlobalScopes()
                ->where('penjualan_id', $lockedPenjualan->id)
                ->orderBy('barang_id', 'asc')
                ->get();

            foreach ($details as $detail) {
                // Cari barang (termasuk yang nonaktif)
                $barang = Barang::withoutGlobalScopes()->find($detail->barang_id);
                if ($barang) {
                    $this->stokService->catat(
                        $barang,
                        'void_penjualan',
                        (int) $detail->jumlah,
                        "Void {$lockedPenjualan->nomor}",
                        $lockedPenjualan
                    );
                }
            }

            // 5. Ubah status jadi dibatalkan
            $now = Carbon::now('Asia/Jayapura');
            $lockedPenjualan->update([
                'status' => 'dibatalkan',
                'dibatalkan_oleh' => $user->id,
                'dibatalkan_pada' => $now,
                'alasan_batal' => $alasan,
            ]);

            // 6. Log aktivitas "penjualan.void" berisi nomor nota, total, dan alasan
            LogAktivitas::withoutGlobalScopes()->create([
                'kios_id' => $lockedPenjualan->kios_id,
                'user_id' => $user->id,
                'aksi' => 'penjualan.void',
                'deskripsi' => "Membatalkan nota {$lockedPenjualan->nomor} senilai Rp " . number_format($lockedPenjualan->total, 0, ',', '.') . ". Alasan: {$alasan}",
                'data_baru' => [
                    'penjualan_id' => $lockedPenjualan->id,
                    'nomor' => $lockedPenjualan->nomor,
                    'total' => $lockedPenjualan->total,
                    'alasan' => $alasan,
                ],
            ]);

            return $lockedPenjualan;
        });
    }

    /**
     * Buat nomor unik nota penjualan TRX-YYYYMMDD-0001 urut per kios per hari.
     */
    public function generateNomor(int $kiosId, Carbon $waktu): string
    {
        $prefix = 'TRX-' . $waktu->format('Ymd') . '-';

        $lastRecord = Penjualan::withoutGlobalScopes()
            ->where('kios_id', $kiosId)
            ->whereDate('waktu_transaksi', $waktu->toDateString())
            ->where('nomor', 'LIKE', $prefix . '%')
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        $nextNumber = 1;
        if ($lastRecord && preg_match('/-(\d{4})$/', $lastRecord->nomor, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
