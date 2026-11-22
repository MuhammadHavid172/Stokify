<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\DetailPenyesuaianStok;
use App\Models\LogAktivitas;
use App\Models\PenyesuaianStok;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PenyesuaianStokService
{
    public function __construct(protected StokService $stokService)
    {
    }

    /**
     * Simpan penyesuaian stok manual (rusak, hilang, kedaluwarsa, koreksi_lain)
     * dalam satu transaksi database terkunci.
     *
     * @param array $data Input form: tanggal, catatan, items (array of [barang_id, jenis, arah, jumlah, alasan])
     * @param User $user Petugas admin pencatat
     * @return PenyesuaianStok
     * @throws InvalidArgumentException
     */
    public function simpanManual(array $data, User $user): PenyesuaianStok
    {
        return DB::transaction(function () use ($data, $user) {
            $rawItems = $data['items'] ?? [];
            if (empty($rawItems)) {
                throw new InvalidArgumentException('Daftar barang penyesuaian tidak boleh kosong.');
            }

            // Validasi duplikasi barang dan aturan jenis
            $seenBarang = [];
            $validatedItems = [];

            foreach ($rawItems as $item) {
                $bId = (int) ($item['barang_id'] ?? 0);
                if ($bId <= 0) {
                    throw new InvalidArgumentException('ID barang tidak valid.');
                }

                if (isset($seenBarang[$bId])) {
                    throw new InvalidArgumentException('Barang yang sama tidak boleh muncul lebih dari satu kali dalam satu penyesuaian.');
                }
                $seenBarang[$bId] = true;

                $jenis = $item['jenis'] ?? '';
                $allowedJenis = ['rusak', 'hilang', 'kedaluwarsa', 'koreksi_lain'];
                if (! in_array($jenis, $allowedJenis, true)) {
                    throw new InvalidArgumentException("Jenis penyesuaian '{$jenis}' tidak valid untuk penyesuaian manual.");
                }

                $qty = (int) ($item['jumlah'] ?? 0);
                if ($qty <= 0) {
                    throw new InvalidArgumentException('Jumlah penyesuaian harus lebih dari 0.');
                }

                $arah = $item['arah'] ?? 'kurang';
                if (in_array($jenis, ['rusak', 'hilang', 'kedaluwarsa'], true)) {
                    if ($arah === 'tambah') {
                        throw new InvalidArgumentException("Penyesuaian jenis '{$jenis}' hanya boleh mengurangi stok, tidak boleh menambah.");
                    }
                    $signedJumlah = -$qty;
                } else {
                    // koreksi_lain
                    $signedJumlah = ($arah === 'tambah') ? $qty : -$qty;
                }

                $alasan = trim((string) ($item['alasan'] ?? ''));
                if (strlen($alasan) < 3) {
                    throw new InvalidArgumentException('Alasan penyesuaian wajib diisi minimal 3 karakter.');
                }

                $validatedItems[] = [
                    'barang_id' => $bId,
                    'jenis' => $jenis,
                    'signed_jumlah' => $signedJumlah,
                    'alasan' => $alasan,
                ];
            }

            // Urutkan item berdasarkan barang_id agar urutan lock konsisten (mencegah deadlock)
            usort($validatedItems, fn ($a, $b) => $a['barang_id'] <=> $b['barang_id']);

            // Buat nomor nota penyesuaian berformat PS-YYYYMMDD-0001
            $tanggal = isset($data['tanggal']) ? Carbon::parse($data['tanggal']) : Carbon::today();
            $nomor = $this->generateNomor($user->kios_id, $tanggal);

            // Buat header penyesuaian stok
            $penyesuaian = PenyesuaianStok::withoutGlobalScopes()->create([
                'kios_id' => $user->kios_id,
                'user_id' => $user->id,
                'nomor' => $nomor,
                'tanggal' => $tanggal->toDateString(),
                'mode' => 'manual',
                'catatan' => $data['catatan'] ?? null,
                'total_kerugian' => 0,
            ]);

            $totalKerugian = 0;

            // Eksekusi setiap item penyesuaian
            foreach ($validatedItems as $item) {
                // Kunci baris barang (aktif maupun nonaktif diperbolehkan)
                $barang = Barang::withoutGlobalScopes()
                    ->where('id', $item['barang_id'])
                    ->where('kios_id', $user->kios_id)
                    ->lockForUpdate()
                    ->first();

                if (! $barang) {
                    throw new InvalidArgumentException("Barang dengan ID {$item['barang_id']} tidak ditemukan di kios Anda.");
                }

                $stokSebelum = (int) $barang->stok;
                $hargaModal = (int) $barang->harga_modal;
                $signedJumlah = (int) $item['signed_jumlah'];
                $stokSesudah = $stokSebelum + $signedJumlah;

                if ($stokSesudah < 0) {
                    $absJumlah = abs($signedJumlah);
                    throw new InvalidArgumentException(
                        "Stok '{$barang->nama}' ({$barang->kode}) tidak mencukupi untuk dikurangi sebanyak {$absJumlah}. Stok saat ini: {$stokSebelum}."
                    );
                }

                // Hitung nilai rupiah bertanda
                $nilai = $signedJumlah * $hargaModal;
                if ($signedJumlah < 0) {
                    $totalKerugian += abs($nilai);
                }

                // Ubah stok via StokService
                $this->stokService->catat(
                    $barang,
                    'penyesuaian',
                    $signedJumlah,
                    "Penyesuaian {$nomor} ({$item['jenis']}): {$item['alasan']}",
                    $penyesuaian
                );

                // Catat detail penyesuaian stok
                DetailPenyesuaianStok::withoutGlobalScopes()->create([
                    'kios_id' => $user->kios_id,
                    'penyesuaian_stok_id' => $penyesuaian->id,
                    'barang_id' => $barang->id,
                    'jenis' => $item['jenis'],
                    'jumlah' => $signedJumlah,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'harga_modal' => $hargaModal,
                    'nilai' => $nilai,
                    'alasan' => $item['alasan'],
                ]);
            }

            // Update total kerugian header
            $penyesuaian->total_kerugian = $totalKerugian;
            $penyesuaian->save();

            // Catat log aktivitas
            LogAktivitas::withoutGlobalScopes()->create([
                'kios_id' => $user->kios_id,
                'user_id' => $user->id,
                'aksi' => 'penyesuaian.tambah',
                'deskripsi' => "Mencatat penyesuaian stok manual nomor {$penyesuaian->nomor} (" . count($validatedItems) . " item, total kerugian " . rupiah($totalKerugian) . ")",
                'data_lama' => null,
                'data_baru' => [
                    'nomor' => $penyesuaian->nomor,
                    'mode' => 'manual',
                    'item_count' => count($validatedItems),
                    'total_kerugian' => $totalKerugian,
                ],
            ]);

            return $penyesuaian;
        });
    }

    /**
     * Simpan hasil stok opname sederhana dalam satu transaksi database terkunci.
     *
     * @param array $data Input form: tanggal, catatan, items (array of [barang_id, stok_sistem, stok_fisik, alasan])
     * @param User $user Petugas admin pencatat
     * @return PenyesuaianStok
     * @throws InvalidArgumentException
     */
    public function simpanOpname(array $data, User $user): PenyesuaianStok
    {
        return DB::transaction(function () use ($data, $user) {
            $rawItems = $data['items'] ?? [];
            if (empty($rawItems)) {
                throw new InvalidArgumentException('Daftar barang opname tidak boleh kosong.');
            }

            $seenBarang = [];
            $diffItems = [];

            foreach ($rawItems as $item) {
                $bId = (int) ($item['barang_id'] ?? 0);
                if ($bId <= 0) {
                    continue;
                }

                if (isset($seenBarang[$bId])) {
                    throw new InvalidArgumentException('Barang yang sama tidak boleh duplikat dalam stok opname.');
                }
                $seenBarang[$bId] = true;

                // Hanya proses jika stok_fisik diisi (tidak null dan bukan string kosong)
                if (! isset($item['stok_fisik']) || $item['stok_fisik'] === '' || $item['stok_fisik'] === null) {
                    continue;
                }

                $stokFisik = (int) $item['stok_fisik'];
                if ($stokFisik < 0) {
                    throw new InvalidArgumentException('Stok fisik tidak boleh bernilai negatif.');
                }

                $stokSistemSnapshot = (int) ($item['stok_sistem'] ?? 0);

                // Hanya proses jika terdapat selisih antara fisik dan sistem
                if ($stokFisik === $stokSistemSnapshot) {
                    continue;
                }

                $alasan = trim((string) ($item['alasan'] ?? ''));
                if ($alasan === '') {
                    $alasan = 'Selisih stok opname';
                }

                $diffItems[] = [
                    'barang_id' => $bId,
                    'stok_sistem_snapshot' => $stokSistemSnapshot,
                    'stok_fisik' => $stokFisik,
                    'alasan' => $alasan,
                ];
            }

            if (empty($diffItems)) {
                throw new InvalidArgumentException('Tidak ada selisih stok fisik dengan stok sistem yang perlu disesuaikan.');
            }

            // Urutkan item berdasarkan barang_id agar urutan lock konsisten
            usort($diffItems, fn ($a, $b) => $a['barang_id'] <=> $b['barang_id']);

            // Buat nomor nota penyesuaian berformat PS-YYYYMMDD-0001
            $tanggal = isset($data['tanggal']) ? Carbon::parse($data['tanggal']) : Carbon::today();
            $nomor = $this->generateNomor($user->kios_id, $tanggal);

            // Buat header penyesuaian stok opname
            $penyesuaian = PenyesuaianStok::withoutGlobalScopes()->create([
                'kios_id' => $user->kios_id,
                'user_id' => $user->id,
                'nomor' => $nomor,
                'tanggal' => $tanggal->toDateString(),
                'mode' => 'opname',
                'catatan' => $data['catatan'] ?? null,
                'total_kerugian' => 0,
            ]);

            $totalKerugian = 0;

            foreach ($diffItems as $item) {
                // Kunci baris barang untuk update
                $barang = Barang::withoutGlobalScopes()
                    ->where('id', $item['barang_id'])
                    ->where('kios_id', $user->kios_id)
                    ->lockForUpdate()
                    ->first();

                if (! $barang) {
                    throw new InvalidArgumentException("Barang dengan ID {$item['barang_id']} tidak ditemukan di kios Anda.");
                }

                // VALIDASI KRITIS OPNAME:
                // Jika stok sistem saat ini berbeda dari snapshot stok sistem yang dilihat admin saat form dibuka,
                // tolak seluruh proses agar tidak terjadi overwrite buta!
                if ((int) $barang->stok !== (int) $item['stok_sistem_snapshot']) {
                    throw new InvalidArgumentException(
                        "Stok {$barang->nama} ({$barang->kode}) berubah sejak halaman dibuka. Muat ulang dan hitung ulang."
                    );
                }

                $stokSebelum = (int) $barang->stok;
                $stokSesudah = (int) $item['stok_fisik'];
                $signedJumlah = $stokSesudah - $stokSebelum;
                $hargaModal = (int) $barang->harga_modal;

                $nilai = $signedJumlah * $hargaModal;
                if ($signedJumlah < 0) {
                    $totalKerugian += abs($nilai);
                }

                // Ubah stok via StokService
                $this->stokService->catat(
                    $barang,
                    'penyesuaian',
                    $signedJumlah,
                    "Stok opname {$nomor}: {$item['alasan']}",
                    $penyesuaian
                );

                // Catat detail penyesuaian stok opname
                DetailPenyesuaianStok::withoutGlobalScopes()->create([
                    'kios_id' => $user->kios_id,
                    'penyesuaian_stok_id' => $penyesuaian->id,
                    'barang_id' => $barang->id,
                    'jenis' => 'selisih_opname',
                    'jumlah' => $signedJumlah,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'harga_modal' => $hargaModal,
                    'nilai' => $nilai,
                    'alasan' => $item['alasan'],
                ]);
            }

            // Update total kerugian header
            $penyesuaian->total_kerugian = $totalKerugian;
            $penyesuaian->save();

            // Catat log aktivitas opname
            LogAktivitas::withoutGlobalScopes()->create([
                'kios_id' => $user->kios_id,
                'user_id' => $user->id,
                'aksi' => 'penyesuaian.opname',
                'deskripsi' => "Mencatat penyesuaian stok opname nomor {$penyesuaian->nomor} (" . count($diffItems) . " item selisih)",
                'data_lama' => null,
                'data_baru' => [
                    'nomor' => $penyesuaian->nomor,
                    'mode' => 'opname',
                    'item_count' => count($diffItems),
                    'total_kerugian' => $totalKerugian,
                ],
            ]);

            return $penyesuaian;
        });
    }

    /**
     * Buat nomor unik nota penyesuaian berformat PS-YYYYMMDD-0001 urut per kios per hari.
     */
    public function generateNomor(int $kiosId, Carbon $tanggal): string
    {
        $prefix = 'PS-' . $tanggal->format('Ymd') . '-';

        $lastRecord = PenyesuaianStok::withoutGlobalScopes()
            ->where('kios_id', $kiosId)
            ->where('tanggal', $tanggal->toDateString())
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
