<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Kios;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service Analisis Performa Barang (Fast/Normal/Slow Moving) dan Rekomendasi Kulakan (Reorder Point).
 *
 * METODOLOGI PERHITUNGAN:
 * 1. Seluruh angka bersumber dari data riil transaksi selesai (detail_penjualan pada penjualan berstatus 'selesai').
 * 2. Menggunakan snapshot penjualan (subtotal dan subtotal_modal) agar tidak terpengaruh perubahan harga di kemudian hari.
 * 3. Klasifikasi Pareto berdasarkan frekuensi nota penjualan (bukan kuantitas volume barang)
 *    agar perbandingan objektif tanpa mencampuradukkan satuan kg, pcs, botol, pack, atau liter.
 * 4. Model Rekomendasi Kulakan memakai Reorder Point (ROP) dinamis:
 *    - Titik Pesan = MAX(Rata Harian x (Lead Time + Hari Pengaman), Stok Minimum)
 *    - Target Stok = MAX(Rata Harian x (Lead Time + Hari Pengaman + Cakupan Kulakan), Stok Minimum)
 * 5. PENTING KEAMANAN TENANT (MULTI-TENANCY):
 *    Setiap query mentah via DB::table() WAJIB menyertakan klausul `where kios_id = ?`
 *    secara eksplisit pada setiap level query dan join guna menjamin isolasi data 100% antar kios.
 */
class AnalisisBarangService
{
    /**
     * Hitung analisis performa dan rekomendasi kulakan untuk seluruh barang aktif milik kios.
     *
     * @param int $hariPeriode Pilihan rentang hari analisis: 7, 14, 30, 60, atau 90 hari.
     * @param int|null $kiosId ID kios (jika null, diambil dari user yang sedang login).
     * @param User|null $user Instance user (opsional).
     * @return Collection Koleksi data analisis per barang.
     * @throws InvalidArgumentException Jika rentang periode tidak valid.
     */
    public function hitung(int $hariPeriode = 30, ?int $kiosId = null, ?User $user = null): Collection
    {
        $validPeriode = [7, 14, 30, 60, 90];
        if (! in_array($hariPeriode, $validPeriode, true)) {
            throw new InvalidArgumentException("Periode analisis harus salah satu dari: " . implode(', ', $validPeriode) . " hari.");
        }

        // Tentukan Kios ID yang valid (Multi-tenancy aman)
        $kiosId = $kiosId ?? $user?->kios_id ?? Auth::user()?->kios_id;
        if (! $kiosId) {
            throw new InvalidArgumentException("Kios ID tidak dapat ditentukan untuk analisis barang.");
        }

        $kios = Kios::findOrFail($kiosId);

        // Ambil konfigurasi parameter kulakan kios (dengan nilai bawaan resmi)
        $leadTime = (int) ($kios->lead_time_hari ?? 2);
        $hariPengaman = (int) ($kios->hari_pengaman ?? 3);
        $cakupanKulakan = (int) ($kios->cakupan_kulakan_hari ?? 7);
        $batasStokBerlebih = (int) ($kios->batas_stok_berlebih_hari ?? 60);
        $marginTipis = (int) ($kios->margin_tipis_persen ?? 10);

        // Batas tanggal periode dalam zona waktu aplikasi (Asia/Jayapura)
        $end = Carbon::today('Asia/Jayapura')->endOfDay();
        $start = Carbon::today('Asia/Jayapura')->subDays($hariPeriode - 1)->startOfDay();

        // 1. QUERY TERAGREGASI DATA PENJUALAN DALAM PERIODE
        // KEAMANAN MULTI-TENANCY: Menyertakan 'kios_id = $kiosId' pada tabel penjualan maupun detail_penjualan.
        // Hanya menghitung nota berstatus 'selesai' (nota void/dibatalkan otomatis terabaikan).
        $penjualanAgg = DB::table('detail_penjualan as dp')
            ->join('penjualan as p', function ($join) use ($kiosId) {
                $join->on('dp.penjualan_id', '=', 'p.id')
                    ->where('p.kios_id', '=', $kiosId); // Proteksi multi-tenancy pada join
            })
            ->where('dp.kios_id', $kiosId) // Proteksi multi-tenancy pada detail
            ->where('p.status', 'selesai')
            ->whereBetween('p.waktu_transaksi', [$start, $end])
            ->selectRaw('
                dp.barang_id,
                SUM(dp.jumlah) as terjual_netto,
                COUNT(DISTINCT p.id) as frekuensi,
                SUM(dp.subtotal) as omzet,
                SUM(dp.subtotal - dp.subtotal_modal) as laba_kotor
            ')
            ->groupBy('dp.barang_id')
            ->get()
            ->keyBy('barang_id');

        // 2. AMBIL SEMUA BARANG AKTIF MILIK KIOS
        $barangList = Barang::withoutGlobalScopes()
            ->where('kios_id', $kiosId)
            ->where('is_aktif', true)
            ->with(['kategori'])
            ->orderBy('nama', 'asc')
            ->get();

        // 3. HITUNG INDIKATOR DASAR PER BARANG
        $items = collect();

        foreach ($barangList as $b) {
            $agg = $penjualanAgg->get($b->id);

            $terjualNetto = $agg ? (int) $agg->terjual_netto : 0;
            $frekuensi = $agg ? (int) $agg->frekuensi : 0;
            $omzet = $agg ? (int) $agg->omzet : 0;
            $labaKotor = $agg ? (int) $agg->laba_kotor : 0;

            // Hari efektif: sejak max(awal periode, tanggal dibuat) sampai hari ini
            $tglDibuat = Carbon::parse($b->created_at)->timezone('Asia/Jayapura')->startOfDay();
            $tglAwalEfektif = $tglDibuat->greaterThan($start) ? $tglDibuat : $start->copy();
            $hariEfektif = max(1, (int) $tglAwalEfektif->diffInDays($end->copy()->startOfDay()) + 1);

            $dataCukup = ($hariEfektif >= 7);
            $rataHarian = (float) ($terjualNetto / $hariEfektif);

            // Cakupan hari persediaan saat ini
            $stokSaatIni = (int) $b->stok;
            $hargaModalSaatIni = (int) $b->harga_modal;
            $hargaJualSaatIni = (int) $b->harga_jual;
            $stokMinimum = (int) $b->stok_minimum;

            $cakupanHari = null;
            if ($rataHarian > 0) {
                $cakupanHari = round($stokSaatIni / $rataHarian, 1);
            } elseif ($stokSaatIni == 0) {
                $cakupanHari = 0.0;
            }

            // Margin keuntungan saat ini
            $marginPersen = null;
            if ($hargaJualSaatIni > 0) {
                $marginPersen = round((($hargaJualSaatIni - $hargaModalSaatIni) / $hargaJualSaatIni) * 100, 1);
            }

            // Modal tertahan di stok saat ini
            $modalTertahan = $stokSaatIni * $hargaModalSaatIni;

            // Penanda tambahan (badge)
            $isMarginTipis = ($marginPersen !== null && $marginPersen < $marginTipis);
            $isStokBerlebih = ($cakupanHari !== null && $cakupanHari > $batasStokBerlebih);

            $items->put($b->id, [
                'barang_id' => $b->id,
                'kode' => $b->kode,
                'nama' => $b->nama,
                'satuan' => $b->satuan,
                'kategori_id' => $b->kategori_id,
                'kategori_nama' => $b->kategori->nama ?? 'Tanpa Kategori',
                'stok' => $stokSaatIni,
                'stok_minimum' => $stokMinimum,
                'harga_modal' => $hargaModalSaatIni,
                'harga_jual' => $hargaJualSaatIni,
                'terjual_netto' => $terjualNetto,
                'frekuensi' => $frekuensi,
                'omzet' => $omzet,
                'laba_kotor' => $labaKotor,
                'hari_efektif' => $hariEfektif,
                'data_cukup' => $dataCukup,
                'rata_harian' => $rataHarian,
                'cakupan_hari' => $cakupanHari,
                'margin_persen' => $marginPersen,
                'modal_tertahan' => $modalTertahan,
                'is_margin_tipis' => $isMarginTipis,
                'is_stok_berlebih' => $isStokBerlebih,
                // Akan diisi pada tahap klasifikasi & rekomendasi:
                'kelas' => 'belum_diketahui',
                'kelas_label' => '-',
                'titik_pesan' => 0.0,
                'target_stok' => 0.0,
                'jumlah_rekomendasi' => 0,
                'estimasi_biaya' => 0,
                'status_rekomendasi' => 'stok_cukup',
                'status_rekomendasi_label' => 'Stok Cukup',
                'catatan_rumus' => '',
            ]);
        }

        // 4. KLASIFIKASI PARETO ATAS FREKUENSI PENJUALAN
        // Ambil barang dengan data_cukup dan frekuensi > 0
        $eligibleForPareto = $items->filter(fn($row) => $row['data_cukup'] && $row['frekuensi'] > 0)
            ->sort(function ($a, $b) {
                // Urutan menurun frekuensi -> terjual_netto -> nama naik
                if ($b['frekuensi'] !== $a['frekuensi']) {
                    return $b['frekuensi'] <=> $a['frekuensi'];
                }
                if ($b['terjual_netto'] !== $a['terjual_netto']) {
                    return $b['terjual_netto'] <=> $a['terjual_netto'];
                }
                return strcasecmp($a['nama'], $b['nama']);
            });

        $totalFrekuensiSemua = (int) $eligibleForPareto->sum('frekuensi');
        $runningFrekuensi = 0;

        foreach ($eligibleForPareto as $bId => $row) {
            $kumulatifSebelumPersen = $totalFrekuensiSemua > 0
                ? ($runningFrekuensi / $totalFrekuensiSemua) * 100.0
                : 0.0;

            if ($kumulatifSebelumPersen < 80.0) {
                $kelas = 'fast';
                $kelasLabel = 'Fast Moving';
            } elseif ($kumulatifSebelumPersen < 95.0) {
                $kelas = 'normal';
                $kelasLabel = 'Normal';
            } else {
                $kelas = 'slow';
                $kelasLabel = 'Slow Moving';
            }

            $current = $items->get($bId);
            $current['kelas'] = $kelas;
            $current['kelas_label'] = $kelasLabel;
            $current['kumulatif_persen_sebelum'] = round($kumulatifSebelumPersen, 1);
            $items->put($bId, $current);

            $runningFrekuensi += $row['frekuensi'];
        }

        // Klasifikasi barang lainnya (frekuensi 0 atau data tidak cukup)
        foreach ($items as $bId => $row) {
            if ($row['kelas'] !== 'belum_diketahui') {
                continue;
            }

            $current = $items->get($bId);
            if (! $row['data_cukup']) {
                $current['kelas'] = 'belum_cukup';
                $current['kelas_label'] = 'Data Belum Cukup';
            } elseif ($row['frekuensi'] === 0 && $row['stok'] > 0) {
                $current['kelas'] = 'tidak_laku';
                $current['kelas_label'] = 'Tidak Laku';
            } else {
                $current['kelas'] = 'tidak_ada_aktivitas';
                $current['kelas_label'] = 'Tidak Ada Aktivitas';
            }
            $items->put($bId, $current);
        }

        // 5. PERHITUNGAN REKOMENDASI KULAKAN (TITIK PESAN ULANG)
        foreach ($items as $bId => $row) {
            $current = $items->get($bId);
            $stok = $row['stok'];
            $stokMin = $row['stok_minimum'];
            $rataHarian = $row['rata_harian'];
            $kelas = $row['kelas'];
            $cakupanHari = $row['cakupan_hari'];

            if ($row['data_cukup']) {
                $titikPesan = max($rataHarian * ($leadTime + $hariPengaman), (float) $stokMin);
                $targetStok = max($rataHarian * ($leadTime + $hariPengaman + $cakupanKulakan), (float) $stokMin);

                if ($stok <= $titikPesan) {
                    $rawRekomendasi = (int) ceil($targetStok - $stok);
                    $jumlahRekomendasi = max(1, $rawRekomendasi);
                } else {
                    $jumlahRekomendasi = 0;
                }
            } else {
                // Data belum cukup (< 7 hari): gunakan patokan stok minimum
                $titikPesan = (float) $stokMin;
                $targetStok = (float) ($stokMin * 2);

                if ($stokMin == 0) {
                    $jumlahRekomendasi = 0;
                } elseif ($stok <= $titikPesan) {
                    $rawRekomendasi = (int) ceil($targetStok - $stok);
                    $jumlahRekomendasi = max(1, $rawRekomendasi);
                } else {
                    $jumlahRekomendasi = 0;
                }
            }

            // ATURAN BISNIS "TAHAN KULAKAN":
            // Barang kelas Slow, Tidak Laku, atau Stok Berlebih TIDAK PERNAH boleh memiliki rekomendasi kulakan > 0.
            // Nilai rekomendasi dipaksa 0 dan berstatus "Tahan Kulakan".
            $isTahanKulakan = (
                $kelas === 'slow' ||
                $kelas === 'tidak_laku' ||
                ($cakupanHari !== null && $cakupanHari > $batasStokBerlebih)
            );

            if ($isTahanKulakan) {
                $statusRekomendasi = 'tahan_kulakan';
                $statusRekomendasiLabel = 'Tahan Restok';
                $jumlahRekomendasi = 0;

                if ($cakupanHari !== null && $cakupanHari > $batasStokBerlebih) {
                    $catatanRumus = "Stok berlebih (cakupan {$cakupanHari} hari > batas {$batasStokBerlebih} hari), tahan restok.";
                } else {
                    $catatanRumus = "Penjualan rendah ({$current['kelas_label']}), pertimbangkan tidak restok.";
                }
            } elseif ($jumlahRekomendasi > 0) {
                if ($kelas === 'fast') {
                    $statusRekomendasi = 'kulakan_banyak';
                    $statusRekomendasiLabel = 'Restok Banyak';
                } else {
                    $statusRekomendasi = 'kulakan_secukupnya';
                    $statusRekomendasiLabel = 'Restok Secukupnya';
                }

                if ($row['data_cukup']) {
                    $leadTotal = $leadTime + $hariPengaman + $cakupanKulakan;
                    $catatanRumus = "Rata-rata " . number_format($rataHarian, 1, ',', '.') . "/hari x ({$leadTime}+{$hariPengaman}+{$cakupanKulakan}={$leadTotal} hari) = " . number_format($targetStok, 1, ',', '.') . ", stok {$stok}, beli {$jumlahRekomendasi} {$row['satuan']}.";
                } else {
                    $catatanRumus = "Data < 7 hari. Target stok min ({$stokMin}) x 2 = {$targetStok}, stok {$stok}, beli {$jumlahRekomendasi} {$row['satuan']}.";
                }
            } else {
                $statusRekomendasi = 'stok_cukup';
                $statusRekomendasiLabel = 'Stok Cukup';
                $catatanRumus = "Stok saat ini ({$stok}) masih di atas titik pesan (" . number_format($titikPesan, 1, ',', '.') . "). Stok aman.";
            }

            $estimasiBiaya = $jumlahRekomendasi * $current['harga_modal'];

            $current['titik_pesan'] = round($titikPesan, 1);
            $current['target_stok'] = round($targetStok, 1);
            $current['jumlah_rekomendasi'] = $jumlahRekomendasi;
            $current['estimasi_biaya'] = $estimasiBiaya;
            $current['status_rekomendasi'] = $statusRekomendasi;
            $current['status_rekomendasi_label'] = $statusRekomendasiLabel;
            $current['catatan_rumus'] = $catatanRumus;

            $items->put($bId, $current);
        }

        return $items->values();
    }

    /**
     * Hasilkan ringkasan indikator performa dan kulakan dari koleksi barang hasil hitung().
     *
     * @param Collection $items Koleksi item hasil hitung().
     * @param Kios|null $kios Instance kios untuk informasi tambahan.
     * @return array
     */
    public function ringkasan(Collection $items, ?Kios $kios = null): array
    {
        $jumlahFast = $items->where('kelas', 'fast')->count();
        $jumlahNormal = $items->where('kelas', 'normal')->count();
        $jumlahSlow = $items->where('kelas', 'slow')->count();
        $jumlahTidakLaku = $items->where('kelas', 'tidak_laku')->count();
        $jumlahBelumCukup = $items->where('kelas', 'belum_cukup')->count();
        $jumlahTidakAdaAktivitas = $items->where('kelas', 'tidak_ada_aktivitas')->count();

        // Total modal tertahan pada kategori masalah: Slow, Tidak Laku, atau Stok Berlebih (tanpa duplikasi item)
        $barangMasalah = $items->filter(function ($row) {
            return in_array($row['kelas'], ['slow', 'tidak_laku'], true) || $row['is_stok_berlebih'];
        });
        $totalModalTertahanMasalah = (int) $barangMasalah->sum('modal_tertahan');

        // Total modal tertahan pada seluruh barang aktif
        $totalModalTertahanSemua = (int) $items->sum('modal_tertahan');

        // Barang yang memerlukan kulakan
        $barangPerluKulakan = $items->filter(fn($row) => $row['jumlah_rekomendasi'] > 0);
        $jumlahPerluKulakan = $barangPerluKulakan->count();
        $totalEstimasiBiaya = (int) $barangPerluKulakan->sum('estimasi_biaya');

        // 5 Teratas terlaris menurut frekuensi penjualan
        $top5Terlaris = $items->filter(fn($row) => $row['frekuensi'] > 0)
            ->sort(function ($a, $b) {
                if ($b['frekuensi'] !== $a['frekuensi']) {
                    return $b['frekuensi'] <=> $a['frekuensi'];
                }
                if ($b['terjual_netto'] !== $a['terjual_netto']) {
                    return $b['terjual_netto'] <=> $a['terjual_netto'];
                }
                return strcasecmp($a['nama'], $b['nama']);
            })
            ->take(5)
            ->values()
            ->all();

        return [
            'jumlah_total_barang' => $items->count(),
            'jumlah_fast' => $jumlahFast,
            'jumlah_normal' => $jumlahNormal,
            'jumlah_slow' => $jumlahSlow,
            'jumlah_tidak_laku' => $jumlahTidakLaku,
            'jumlah_belum_cukup' => $jumlahBelumCukup,
            'jumlah_tidak_ada_aktivitas' => $jumlahTidakAdaAktivitas,
            'total_modal_tertahan_masalah' => $totalModalTertahanMasalah,
            'total_modal_tertahan_semua' => $totalModalTertahanSemua,
            'jumlah_perlu_kulakan' => $jumlahPerluKulakan,
            'total_estimasi_biaya' => $totalEstimasiBiaya,
            'top_5_terlaris' => $top5Terlaris,
        ];
    }
}
