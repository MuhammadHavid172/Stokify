<?php

namespace App\Services;

use App\Models\LogAktivitas;
use App\Models\Penjualan;
use App\Models\ShiftKasir;
use App\Models\User;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShiftService
{
    /**
     * Buka shift kasir baru dalam satu DB transaction.
     *
     * @param User $user Pengguna yang membuka shift (kasir atau admin)
     * @param int $modalAwal Modal uang tunai awal di laci
     * @param DateTimeInterface|null $waktuBuka Waktu buka shift (opsional, default now)
     * @return ShiftKasir
     * @throws ValidationException
     */
    public function buka(User $user, int $modalAwal, ?DateTimeInterface $waktuBuka = null): ShiftKasir
    {
        if ($modalAwal < 0) {
            throw ValidationException::withMessages([
                'modal_awal' => 'Modal awal tidak boleh bernilai negatif.',
            ]);
        }

        // Cek shift berjalan milik user sebelum transaksi
        $existing = ShiftKasir::withoutGlobalScopes()
            ->where('kios_id', $user->kios_id)
            ->where('user_id', $user->id)
            ->where('status', 'berjalan')
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'shift' => 'Anda masih memiliki shift yang sedang berjalan.',
            ]);
        }

        return DB::transaction(function () use ($user, $modalAwal, $waktuBuka) {
            try {
                // Cek ulang dengan lock untuk mencegah race condition
                $existingInside = ShiftKasir::withoutGlobalScopes()
                    ->where('kios_id', $user->kios_id)
                    ->where('user_id', $user->id)
                    ->where('status', 'berjalan')
                    ->lockForUpdate()
                    ->first();

                if ($existingInside) {
                    throw ValidationException::withMessages([
                        'shift' => 'Anda masih memiliki shift yang sedang berjalan.',
                    ]);
                }

                $waktu = $waktuBuka ? Carbon::parse($waktuBuka) : Carbon::now('Asia/Jayapura');

                $shift = ShiftKasir::withoutGlobalScopes()->create([
                    'kios_id' => $user->kios_id,
                    'user_id' => $user->id,
                    'waktu_buka' => $waktu,
                    'modal_awal' => $modalAwal,
                    'status' => 'berjalan',
                    'user_aktif' => $user->id,
                ]);

                LogAktivitas::withoutGlobalScopes()->create([
                    'kios_id' => $user->kios_id,
                    'user_id' => $user->id,
                    'aksi' => 'shift.buka',
                    'deskripsi' => "Membuka shift kasir dengan modal awal Rp " . number_format($modalAwal, 0, ',', '.'),
                    'data_baru' => [
                        'shift_id' => $shift->id,
                        'modal_awal' => $modalAwal,
                        'waktu_buka' => $waktu->toDateTimeString(),
                    ],
                ]);

                return $shift;
            } catch (QueryException $e) {
                // Tangani pelanggaran unique constraint user_aktif
                if (str_contains($e->getMessage(), 'user_aktif') || $e->getCode() == 23000) {
                    throw ValidationException::withMessages([
                        'shift' => 'Anda masih memiliki shift yang sedang berjalan.',
                    ]);
                }
                throw $e;
            }
        });
    }

    /**
     * Tutup shift kasir dalam satu DB transaction.
     * Semua angka kalkulasi dihitung dari database (mengabaikan input client).
     *
     * @param ShiftKasir $shift Model shift kasir yang akan ditutup
     * @param int $uangFisik Uang fisik hasil hitung buta di laci kasir
     * @param string|null $catatan Catatan pertanggungjawaban (wajib jika selisih != 0 atau tutup paksa)
     * @param User $penutup Pengguna yang menutup shift
     * @param bool $paksa Ditutup paksa oleh admin
     * @param DateTimeInterface|null $waktuTutup Waktu penutupan (opsional, default now)
     * @return ShiftKasir
     * @throws ValidationException
     */
    public function tutup(
        ShiftKasir $shift,
        int $uangFisik,
        ?string $catatan,
        User $penutup,
        bool $paksa = false,
        ?DateTimeInterface $waktuTutup = null
    ): ShiftKasir {
        if ($uangFisik < 0) {
            throw ValidationException::withMessages([
                'uang_fisik' => 'Uang fisik tidak boleh bernilai negatif.',
            ]);
        }

        return DB::transaction(function () use ($shift, $uangFisik, $catatan, $penutup, $paksa, $waktuTutup) {
            // 1. Kunci baris shift
            $lockedShift = ShiftKasir::withoutGlobalScopes()
                ->where('id', $shift->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedShift->status === 'ditutup') {
                throw ValidationException::withMessages([
                    'shift' => 'Shift ini sudah ditutup sebelumnya.',
                ]);
            }

            // 2. Hitung dari database (bukan dari input):
            $penjualanSelesai = Penjualan::withoutGlobalScopes()
                ->where('shift_id', $lockedShift->id)
                ->where('status', 'selesai')
                ->get();

            $jumlahTransaksi = $penjualanSelesai->count();
            $totalPenjualanTunai = (int) $penjualanSelesai->sum('total');

            $jumlahNotaBatal = Penjualan::withoutGlobalScopes()
                ->where('shift_id', $lockedShift->id)
                ->where('status', 'dibatalkan')
                ->count();

            $uangSeharusnya = (int) $lockedShift->modal_awal + $totalPenjualanTunai;
            $selisih = (int) $uangFisik - $uangSeharusnya;

            // 3. Jika selisih tidak nol, catatan WAJIB (minimal 5 karakter)
            $cleanCatatan = trim((string) $catatan);
            if ($selisih !== 0 && mb_strlen($cleanCatatan) < 5) {
                $pesanSelisih = $selisih < 0
                    ? 'kurang Rp ' . number_format(abs($selisih), 0, ',', '.')
                    : 'lebih Rp ' . number_format(abs($selisih), 0, ',', '.');

                throw ValidationException::withMessages([
                    'catatan' => "Catatan wajib diisi minimal 5 karakter karena terdapat selisih uang fisik ({$pesanSelisih}).",
                ]);
            }

            // Jika tutup paksa, catatan juga wajib
            if ($paksa && mb_strlen($cleanCatatan) < 5) {
                throw ValidationException::withMessages([
                    'catatan' => 'Alasan/catatan penutupan paksa wajib diisi minimal 5 karakter.',
                ]);
            }

            // 4. Simpan snapshot penutupan
            $waktu = $waktuTutup ? Carbon::parse($waktuTutup) : Carbon::now('Asia/Jayapura');

            $lockedShift->update([
                'status' => 'ditutup',
                'user_aktif' => null,
                'waktu_tutup' => $waktu,
                'uang_fisik' => $uangFisik,
                'uang_seharusnya' => $uangSeharusnya,
                'selisih' => $selisih,
                'jumlah_transaksi' => $jumlahTransaksi,
                'total_penjualan_tunai' => $totalPenjualanTunai,
                'jumlah_nota_batal' => $jumlahNotaBatal,
                'catatan' => $cleanCatatan ?: null,
                'ditutup_oleh' => $penutup->id,
                'ditutup_paksa' => $paksa,
            ]);

            // 5. Log aktivitas
            $aksi = $paksa ? 'shift.tutup_paksa' : 'shift.tutup';
            $selisihLabel = $selisih === 0 ? 'Sesuai (Rp 0)' : ($selisih < 0 ? 'Kurang Rp ' . number_format(abs($selisih), 0, ',', '.') : 'Lebih Rp ' . number_format($selisih, 0, ',', '.'));
            $deskripsi = ($paksa ? 'Menutup paksa shift kasir ' : 'Menutup shift kasir ') .
                "dengan uang fisik Rp " . number_format($uangFisik, 0, ',', '.') .
                ", uang seharusnya Rp " . number_format($uangSeharusnya, 0, ',', '.') .
                ", selisih {$selisihLabel}.";

            LogAktivitas::withoutGlobalScopes()->create([
                'kios_id' => $lockedShift->kios_id,
                'user_id' => $penutup->id,
                'aksi' => $aksi,
                'deskripsi' => $deskripsi,
                'data_baru' => [
                    'shift_id' => $lockedShift->id,
                    'uang_fisik' => $uangFisik,
                    'uang_seharusnya' => $uangSeharusnya,
                    'selisih' => $selisih,
                    'ditutup_paksa' => $paksa,
                    'catatan' => $cleanCatatan,
                ],
            ]);

            return $lockedShift;
        });
    }
}
