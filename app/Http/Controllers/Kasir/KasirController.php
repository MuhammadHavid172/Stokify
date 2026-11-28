<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kasir\StorePenjualanRequest;
use App\Models\Barang;
use App\Models\Penjualan;
use App\Models\ShiftKasir;
use App\Services\PenjualanService;
use App\Services\ShiftService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KasirController extends Controller
{
    /**
     * Tampilkan antarmuka kasir / Point of Sale (POS) atau layar Buka Shift jika belum aktif.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $kios = $user->kios;
        $shift = $user->shiftBerjalan();

        return view('kasir.index', compact('user', 'kios', 'shift'));
    }

    /**
     * Buka shift kasir baru.
     */
    public function bukaShift(Request $request, ShiftService $service): RedirectResponse
    {
        $request->validate([
            'modal_awal' => 'required|numeric|min:0',
        ], [
            'modal_awal.required' => 'Modal awal laci kasir wajib diisi.',
            'modal_awal.numeric' => 'Modal awal harus berupa nominal angka.',
            'modal_awal.min' => 'Modal awal tidak boleh bernilai negatif.',
        ]);

        $modalAwal = (int) $request->input('modal_awal');
        $shift = $service->buka($request->user(), $modalAwal);

        return redirect()->route('kasir.index')
            ->with('success', 'Shift kasir berhasil dibuka dengan modal awal Rp ' . number_format($modalAwal, 0, ',', '.') . '. Selamat bertransaksi!');
    }

    /**
     * Form penutupan shift kasir (Hitung Buta: kasir tidak melihat uang seharusnya).
     */
    public function tutupShiftForm(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $shift = $user->shiftBerjalan();

        if (! $shift) {
            return redirect()->route('kasir.index')
                ->with('error', 'Anda belum memiliki shift kasir yang sedang berjalan.');
        }

        return view('kasir.shift.tutup', compact('user', 'shift'));
    }

    /**
     * Proses simpan tutup shift kasir.
     */
    public function tutupShift(Request $request, ShiftService $service): RedirectResponse
    {
        $user = $request->user();
        $shift = $user->shiftBerjalan();

        if (! $shift) {
            return redirect()->route('kasir.index')
                ->with('error', 'Anda belum memiliki shift kasir yang sedang berjalan.');
        }

        $request->validate([
            'uang_fisik' => 'required|numeric|min:0',
            'catatan' => 'nullable|string',
        ], [
            'uang_fisik.required' => 'Uang fisik di laci kasir wajib diisi.',
            'uang_fisik.numeric' => 'Uang fisik harus berupa nominal angka.',
            'uang_fisik.min' => 'Uang fisik tidak boleh bernilai negatif.',
        ]);

        try {
            $service->tutup(
                shift: $shift,
                uangFisik: (int) $request->input('uang_fisik'),
                catatan: $request->input('catatan'),
                penutup: $user
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('kasir.shift.rekap', $shift->id)
            ->with('success', 'Shift kasir berhasil ditutup.');
    }

    /**
     * Halaman rekapitulasi shift kasir setelah ditutup.
     * Tidak memuat harga modal maupun laba kotor.
     */
    public function rekapShift(ShiftKasir $shift, Request $request): View
    {
        $user = $request->user();

        // Kasir hanya boleh melihat rekap shift miliknya
        if ($user->isKasir() && (int) $shift->user_id !== (int) $user->id) {
            abort(403, 'Anda tidak berhak melihat rekap shift kasir lain.');
        }

        $shift->load(['user', 'ditutupOlehUser']);

        return view('kasir.shift.rekap', compact('shift', 'user'));
    }

    /**
     * Endpoint JSON pencarian barang untuk POS kasir.
     * KEAMANAN DATA (WAJIB): Hanya barang aktif kios sendiri, maksimal 20 item,
     * dan TIDAK BOLEH memuat harga_modal, laba, atau kolom sensitif lainnya.
     */
    public function cariBarang(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));

        $query = Barang::query()
            ->select(['id', 'kode', 'nama', 'satuan', 'harga_jual', 'stok'])
            ->where('is_aktif', true);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'LIKE', "%{$q}%")
                    ->orWhere('kode', 'LIKE', "%{$q}%");
            });
        } else {
            $query->orderBy('nama', 'asc');
        }

        $items = $query->limit(20)->get();

        // Mapping eksplisit untuk memastikan TIDAK ADA harga_modal yang bocor
        $safeItems = $items->map(function ($b) {
            return [
                'id' => (int) $b->id,
                'kode' => (string) $b->kode,
                'nama' => (string) $b->nama,
                'satuan' => (string) $b->satuan,
                'harga_jual' => (int) $b->harga_jual,
                'stok' => (int) $b->stok,
            ];
        });

        return response()->json($safeItems);
    }

    /**
     * Proses simpan transaksi penjualan dari POS kasir.
     */
    public function store(StorePenjualanRequest $request, PenjualanService $service): JsonResponse|RedirectResponse
    {
        $penjualan = $service->buat(
            items: $request->input('items', []),
            bayar: (int) $request->input('bayar'),
            token: (string) $request->input('token'),
            user: $request->user(),
            metodeBayar: (string) $request->input('metode_bayar', 'tunai')
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan.',
                'penjualan' => [
                    'id' => $penjualan->id,
                    'nomor' => $penjualan->nomor,
                    'total' => $penjualan->total,
                    'bayar' => $penjualan->bayar,
                    'kembalian' => $penjualan->kembalian,
                    'metode_bayar' => $penjualan->metode_bayar,
                    'waktu_transaksi' => $penjualan->waktu_transaksi->format('d/m/Y H:i'),
                    'struk_url' => route('kasir.penjualan.struk', $penjualan->id),
                ],
            ]);
        }

        return redirect()->route('kasir.penjualan.struk', $penjualan->id)
            ->with('success', "Transaksi {$penjualan->nomor} berhasil disimpan.");
    }

    /**
     * Tampilan cetak struk belanja thermal (58mm / 80mm).
     */
    public function struk(Penjualan $penjualan, Request $request): View
    {
        $user = $request->user();

        // Kasir hanya boleh membuka struk miliknya sendiri; admin boleh membuka semua struk kios sendiri
        if ($user->role === 'kasir' && (int) $penjualan->user_id !== (int) $user->id) {
            abort(403, 'Anda tidak berhak melihat atau mencetak struk transaksi kasir lain.');
        }

        $penjualan->load(['user', 'dibatalkanOleh', 'detail']);
        $kios = $user->kios;

        return view('kasir.struk', compact('penjualan', 'kios', 'user'));
    }

    /**
     * Batalkan transaksi penjualan (void) oleh kasir (hanya nota miliknya pada shift berjalan).
     */
    public function batalkanPenjualan(Penjualan $penjualan, Request $request, PenjualanService $service): RedirectResponse
    {
        $request->validate([
            'alasan' => 'required|string|min:5',
        ], [
            'alasan.required' => 'Alasan pembatalan wajib diisi.',
            'alasan.min' => 'Alasan pembatalan minimal 5 karakter.',
        ]);

        $service->batalkan($penjualan, $request->input('alasan'), $request->user());

        return redirect()->back()
            ->with('success', "Transaksi {$penjualan->nomor} berhasil dibatalkan. Stok barang telah dikembalikan.");
    }

    /**
     * Riwayat transaksi pada shift berjalan milik kasir yang sedang login.
     * Tidak menampilkan harga modal atau laba kotor.
     */
    public function riwayat(Request $request): View
    {
        $user = $request->user();
        $shift = $user->shiftBerjalan();

        // Jika tidak ada shift berjalan, cari shift terakhir yang ditutup milik user ini
        if (! $shift) {
            $shift = ShiftKasir::where('user_id', $user->id)
                ->orderBy('waktu_buka', 'desc')
                ->first();
        }

        $penjualanList = collect();
        if ($shift) {
            $penjualanList = Penjualan::where('shift_id', $shift->id)
                ->with(['detail', 'dibatalkanOleh'])
                ->orderBy('id', 'desc')
                ->get();
        }

        // Hitung hanya transaksi yang berstatus selesai
        $penjualanSelesai = $penjualanList->where('status', 'selesai');
        $totalOmzetShift = (int) $penjualanSelesai->sum('total');
        $totalTransaksiShift = $penjualanSelesai->count();
        $totalItemShift = (int) $penjualanSelesai->sum(function ($p) {
            return $p->detail->sum('jumlah');
        });

        return view('kasir.riwayat', compact(
            'shift',
            'penjualanList',
            'totalOmzetShift',
            'totalTransaksiShift',
            'totalItemShift',
            'user'
        ));
    }

    /**
     * Endpoint JSON untuk memuat daftar notifikasi barang dengan stok menipis secara realtime.
     * Tidak memuat harga modal atau margin laba (Aman untuk Kasir & Admin).
     */
    public function notifikasiStok(Request $request): JsonResponse
    {
        $user = $request->user();
        $kiosId = $user->kios_id;
        $role = $user->role ?? 'kasir';

        $items = Barang::withoutGlobalScopes()
            ->where('kios_id', $kiosId)
            ->where('is_aktif', true)
            ->whereColumn('stok', '<=', 'stok_minimum')
            ->orderBy('stok', 'asc')
            ->limit(15)
            ->get(['id', 'nama', 'kode', 'stok', 'stok_minimum', 'satuan']);

        $total = Barang::withoutGlobalScopes()
            ->where('kios_id', $kiosId)
            ->where('is_aktif', true)
            ->whereColumn('stok', '<=', 'stok_minimum')
            ->count();

        $feed = \App\Services\NotifikasiService::getFeed($kiosId, $role, 15);

        return response()->json([
            'total' => $total,
            'items' => $items,
            'feed' => $feed,
        ]);
    }
}
