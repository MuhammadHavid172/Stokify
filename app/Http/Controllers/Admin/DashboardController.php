<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\DetailPenjualan;
use App\Models\LogAktivitas;
use App\Models\Penjualan;
use App\Models\PenyesuaianStok;
use App\Models\ShiftKasir;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dasbor admin kios (Tahap 7 - Data Riil Penjualan & Palet Forest).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $kios = $user->kios;

        // Hitung kasir aktif khusus milik kios yang sedang login
        $kasirAktifCount = $kios->users()
            ->where('role', 'kasir')
            ->where('is_aktif', true)
            ->count();

        // Hitung total kasir (aktif & nonaktif) untuk panduan langkah awal
        $totalKasirCount = $kios->users()
            ->where('role', 'kasir')
            ->count();

        // Ambil daftar petugas kasir kios ini (maksimal 4 untuk widget aktivitas)
        $kasirList = $kios->users()
            ->where('role', 'kasir')
            ->latest()
            ->take(4)
            ->get();

        // Tahap 4: Hitung barang stok menipis riil kios ini
        $barangMenipisCount = Barang::stokMenipis()->count();

        // Daftar maksimal 5 barang paling kritis untuk panel peringatan stok
        $barangMenipisList = Barang::stokMenipis()
            ->with('kategori')
            ->orderBy('stok', 'asc')
            ->limit(5)
            ->get();

        // Total barang untuk progres checklist langkah awal
        $totalBarangCount = Barang::count();

        // Tahap 5: Total kulakan bulan ini (status selesai pada bulan berjalan)
        $startOfMonth = Carbon::now('Asia/Jayapura')->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now('Asia/Jayapura')->endOfMonth()->toDateString();
        $kulakanBulanIni = (int) BarangMasuk::where('status', 'selesai')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('total');

        // Total nota barang masuk untuk checklist langkah awal
        $totalBarangMasukCount = BarangMasuk::count();

        // Tahap 6: Total kerugian stok bulan berjalan (kios sendiri)
        $kerugianBulanIni = (int) PenyesuaianStok::whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('total_kerugian');

        // Nilai aset persediaan barang riil (stok * harga modal)
        $nilaiAsetStok = (int) Barang::where('is_aktif', true)->get()->sum(function ($b) {
            return (int) $b->stok * (int) $b->harga_modal;
        });

        // ==============================================================
        // TAHAP 8: DATA SHIFT BERJALAN & REKONSILIASI KAS 7 HARI
        // ==============================================================
        $shiftBerjalanList = ShiftKasir::with('user')
            ->where('status', 'berjalan')
            ->get();
        $shiftBerjalanCount = $shiftBerjalanList->count();
        $shiftBerjalanNames = $shiftBerjalanList->pluck('user.name')->filter()->values()->all();

        // Cek shift berjalan lebih dari 16 jam
        $limit16Jam = Carbon::now('Asia/Jayapura')->subHours(16);
        $shiftLebih16JamCount = $shiftBerjalanList->filter(function ($s) use ($limit16Jam) {
            return Carbon::parse($s->waktu_buka)->timezone('Asia/Jayapura')->lessThanOrEqualTo($limit16Jam);
        })->count();

        // Selisih kas 7 hari terakhir dari shift yang sudah ditutup
        $start7Days = Carbon::now('Asia/Jayapura')->subDays(6)->startOfDay();
        $selisihKas7Hari = (int) ShiftKasir::where('status', 'ditutup')
            ->where('waktu_tutup', '>=', $start7Days)
            ->sum('selisih');

        // ==============================================================
        // TAHAP 7: DATA PENJUALAN RIIL BERDASARKAN ZONA ASIA/JAYAPURA
        // ==============================================================
        $startOfToday = Carbon::now('Asia/Jayapura')->startOfDay();
        $endOfToday = Carbon::now('Asia/Jayapura')->endOfDay();

        $penjualanHariIniQuery = Penjualan::where('status', 'selesai')
            ->whereBetween('waktu_transaksi', [$startOfToday, $endOfToday]);

        $omzetHariIni = (int) (clone $penjualanHariIniQuery)->sum('total');
        $totalTransaksiHariIni = (int) (clone $penjualanHariIniQuery)->count();
        $totalModalHariIni = (int) (clone $penjualanHariIniQuery)->sum('total_modal');
        $labaBersih = $omzetHariIni - $totalModalHariIni;

        // Total transaksi selesai secara keseluruhan untuk checklist langkah awal
        $totalPenjualanCount = Penjualan::where('status', 'selesai')->count();

        // Data Penjualan 7 Hari Terakhir untuk grafik batang asli
        $penjualan7Hari = [];
        $totalOmzet7Hari = 0;
        $hasPenjualan7Hari = false;

        for ($i = 6; $i >= 0; $i--) {
            $tgl = Carbon::now('Asia/Jayapura')->subDays($i);
            $startDay = (clone $tgl)->startOfDay();
            $endDay = (clone $tgl)->endOfDay();

            $dayOmzet = (int) Penjualan::where('status', 'selesai')
                ->whereBetween('waktu_transaksi', [$startDay, $endDay])
                ->sum('total');
            $dayCount = (int) Penjualan::where('status', 'selesai')
                ->whereBetween('waktu_transaksi', [$startDay, $endDay])
                ->count();

            if ($dayOmzet > 0) {
                $hasPenjualan7Hari = true;
            }
            $totalOmzet7Hari += $dayOmzet;

            $penjualan7Hari[] = [
                'date' => $tgl->format('Y-m-d'),
                'short_label' => $tgl->isoFormat('dd'),
                'full_label' => $tgl->translatedFormat('d M'),
                'omzet' => $dayOmzet,
                'count' => $dayCount,
            ];
        }

        $maxOmzet7Hari = max(1, ...array_column($penjualan7Hari, 'omzet'));

        // Tahap 10: Analisis Performa Barang 30 Hari Terakhir & Rekomendasi Kulakan
        $analisisService = app(\App\Services\AnalisisBarangService::class);
        $analisis30Hari = $analisisService->hitung(30, $kios->id, $user);
        $ringkasanAnalisis = $analisisService->ringkasan($analisis30Hari, $kios);
        $barangTerlaris30Hari = $ringkasanAnalisis['top_5_terlaris'];
        $jumlahPerluDikulak = $ringkasanAnalisis['jumlah_perlu_kulakan'];

        // 5 log aktivitas terbaru kios
        $logAktivitasTerbaru = LogAktivitas::with('user')
            ->latest()
            ->take(5)
            ->get();

        // Dokumen transaksi terakhir untuk panel notifikasi
        $latestBarangMasuk = BarangMasuk::with('supplier')->latest()->first();
        $latestPenyesuaian = PenyesuaianStok::latest()->first();
        $latestPenjualan = Penjualan::with('user')->latest()->first();

        return view('admin.dashboard', [
            'user' => $user,
            'kios' => $kios,
            'kasirAktifCount' => $kasirAktifCount,
            'totalKasirCount' => $totalKasirCount,
            'kasirList' => $kasirList,
            'barangMenipisCount' => $barangMenipisCount,
            'barangMenipisList' => $barangMenipisList,
            'totalBarangCount' => $totalBarangCount,
            'kulakanBulanIni' => $kulakanBulanIni,
            'totalBarangMasukCount' => $totalBarangMasukCount,
            'kerugianBulanIni' => $kerugianBulanIni,
            'nilaiAsetStok' => $nilaiAsetStok,
            'omzetHariIni' => $omzetHariIni,
            'labaBersih' => $labaBersih,
            'totalTransaksiHariIni' => $totalTransaksiHariIni,
            'totalPenjualanCount' => $totalPenjualanCount,
            'penjualan7Hari' => $penjualan7Hari,
            'hasPenjualan7Hari' => $hasPenjualan7Hari,
            'totalOmzet7Hari' => $totalOmzet7Hari,
            'maxOmzet7Hari' => $maxOmzet7Hari,
            'barangTerlaris30Hari' => $barangTerlaris30Hari,
            'jumlahPerluDikulak' => $jumlahPerluDikulak,
            'logAktivitasTerbaru' => $logAktivitasTerbaru,
            'latestBarangMasuk' => $latestBarangMasuk,
            'latestPenyesuaian' => $latestPenyesuaian,
            'latestPenjualan' => $latestPenjualan,
            'shiftBerjalanCount' => $shiftBerjalanCount,
            'shiftBerjalanNames' => $shiftBerjalanNames,
            'shiftLebih16JamCount' => $shiftLebih16JamCount,
            'selisihKas7Hari' => $selisihKas7Hari,
        ]);
    }
}
