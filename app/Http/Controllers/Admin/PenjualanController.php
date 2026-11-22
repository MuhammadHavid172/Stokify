<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\User;
use App\Services\PenjualanService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PenjualanController extends Controller
{
    /**
     * Tampilkan daftar riwayat transaksi penjualan kios (khusus role admin).
     */
    public function index(Request $request): View
    {
        $kios = $request->user()->kios;

        $query = Penjualan::with(['user', 'detail'])
            ->orderBy('waktu_transaksi', 'desc')
            ->orderBy('id', 'desc');

        // Filter nomor nota (q atau nomor)
        $searchNomor = trim((string) ($request->query('nomor') ?? $request->query('q') ?? ''));
        if ($searchNomor !== '') {
            $query->where('nomor', 'LIKE', "%{$searchNomor}%");
        }

        // Filter kasir
        $kasirId = $request->query('kasir_id') ?? $request->query('user_id');
        if ($kasirId) {
            $query->where('user_id', $kasirId);
        }

        // Filter status ('selesai', 'dibatalkan')
        $status = $request->query('status');
        if ($status && in_array($status, ['selesai', 'dibatalkan'], true)) {
            $query->where('status', $status);
        }

        // Filter rentang tanggal
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalSelesai = $request->query('tanggal_selesai') ?? $request->query('tanggal_akhir');

        // Default rentang tanggal hari ini jika tidak ada filter tanggal dan tidak sedang mencari nomor
        $hasDateFilter = $request->filled('tanggal_mulai') || $request->filled('tanggal_selesai') || $request->filled('tanggal_akhir');
        if (! $hasDateFilter && $searchNomor === '') {
            $tanggalMulai = Carbon::now('Asia/Jayapura')->toDateString();
            $tanggalSelesai = Carbon::now('Asia/Jayapura')->toDateString();
            $query->whereDate('waktu_transaksi', '>=', $tanggalMulai)
                  ->whereDate('waktu_transaksi', '<=', $tanggalSelesai);
        } else {
            if ($tanggalMulai) {
                $query->whereDate('waktu_transaksi', '>=', $tanggalMulai);
            }
            if ($tanggalSelesai) {
                $query->whereDate('waktu_transaksi', '<=', $tanggalSelesai);
            }
        }

        // Ringkasan di atas tabel (jumlah transaksi, total omzet, laba kotor sesuai filter, hanya status selesai)
        $summaryQuery = clone $query;
        $totalOmzet = (int) (clone $summaryQuery)->where('status', 'selesai')->sum('total');
        $totalTransaksi = (int) (clone $summaryQuery)->where('status', 'selesai')->count();
        $totalModal = (int) (clone $summaryQuery)->where('status', 'selesai')->sum('total_modal');
        $totalLabaKotor = $totalOmzet - $totalModal;

        $penjualanList = $query->paginate(15)->withQueryString();

        // Daftar seluruh kasir & admin kios untuk dropdown filter
        $kasirOptions = User::where('kios_id', $kios->id)->orderBy('name', 'asc')->get();

        return view('admin.penjualan.index', compact(
            'penjualanList',
            'totalOmzet',
            'totalTransaksi',
            'totalLabaKotor',
            'kasirOptions',
            'tanggalMulai',
            'tanggalSelesai',
            'kasirId',
            'status',
            'searchNomor'
        ));
    }

    /**
     * Tampilkan detail nota penjualan lengkap beserta laba kotor (khusus role admin).
     */
    public function show(Penjualan $penjualan): View
    {
        $penjualan->load(['user', 'detail.barang']);

        return view('admin.penjualan.show', compact('penjualan'));
    }

    /**
     * Batalkan (void) transaksi penjualan oleh admin.
     */
    public function batalkan(Penjualan $penjualan, Request $request, PenjualanService $service): RedirectResponse
    {
        $request->validate([
            'alasan' => 'required|string|min:5',
        ], [
            'alasan.required' => 'Alasan pembatalan wajib diisi.',
            'alasan.min' => 'Alasan pembatalan minimal 5 karakter.',
        ]);

        $service->batalkan($penjualan, $request->input('alasan'), $request->user());

        return redirect()->route('admin.penjualan.show', $penjualan->id)
            ->with('success', "Transaksi {$penjualan->nomor} berhasil dibatalkan. Stok barang telah dikembalikan.");
    }
}
