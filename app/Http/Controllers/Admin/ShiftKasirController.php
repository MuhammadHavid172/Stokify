<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShiftKasir;
use App\Models\User;
use App\Services\ShiftService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftKasirController extends Controller
{
    /**
     * Tampilkan daftar riwayat shift kasir (khusus admin).
     */
    public function index(Request $request): View
    {
        $kios = $request->user()->kios;

        $query = ShiftKasir::with(['user', 'ditutupOlehUser'])
            ->orderBy('waktu_buka', 'desc')
            ->orderBy('id', 'desc');

        // Filter kasir
        $kasirId = $request->query('kasir_id') ?? $request->query('user_id');
        if ($kasirId) {
            $query->where('user_id', $kasirId);
        }

        // Filter status ('berjalan', 'ditutup')
        $status = $request->query('status');
        if ($status && in_array($status, ['berjalan', 'ditutup'], true)) {
            $query->where('status', $status);
        }

        // Filter hanya yang ada selisih (hanya untuk status ditutup dengan selisih != 0)
        $hanyaSelisih = (bool) $request->query('hanya_selisih', false);
        if ($hanyaSelisih) {
            $query->where('status', 'ditutup')->where('selisih', '!=', 0);
        }

        // Filter rentang tanggal
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalSelesai = $request->query('tanggal_selesai');

        // Default rentang tanggal 7 hari terakhir jika tidak ada filter manual
        $hasDateFilter = $request->filled('tanggal_mulai') || $request->filled('tanggal_selesai');
        if (! $hasDateFilter) {
            $tanggalMulai = Carbon::now('Asia/Jayapura')->subDays(6)->toDateString();
            $tanggalSelesai = Carbon::now('Asia/Jayapura')->toDateString();
            $query->whereDate('waktu_buka', '>=', $tanggalMulai)
                  ->whereDate('waktu_buka', '<=', $tanggalSelesai);
        } else {
            if ($tanggalMulai) {
                $query->whereDate('waktu_buka', '>=', $tanggalMulai);
            }
            if ($tanggalSelesai) {
                $query->whereDate('waktu_buka', '<=', $tanggalSelesai);
            }
        }

        // Cek shift berjalan yang melebihi 16 jam untuk peringatan kuning
        $limit16Jam = Carbon::now('Asia/Jayapura')->subHours(16);
        $shiftLebih16JamCount = ShiftKasir::where('status', 'berjalan')
            ->where('waktu_buka', '<=', $limit16Jam)
            ->count();

        $shiftList = $query->paginate(15)->withQueryString();

        // Daftar semua user (admin & kasir) di kios ini untuk dropdown filter
        $kasirOptions = User::where('kios_id', $kios->id)->orderBy('name', 'asc')->get();

        return view('admin.shift.index', compact(
            'shiftList',
            'kasirOptions',
            'tanggalMulai',
            'tanggalSelesai',
            'kasirId',
            'status',
            'hanyaSelisih',
            'shiftLebih16JamCount'
        ));
    }

    /**
     * Tampilkan detail shift kasir beserta daftar nota transaksi di dalamnya.
     */
    public function show(ShiftKasir $shift): View
    {
        $shift->load(['user', 'ditutupOlehUser', 'penjualan' => function ($q) {
            $q->with(['user', 'dibatalkanOleh'])->orderBy('waktu_transaksi', 'desc');
        }]);

        return view('admin.shift.show', compact('shift'));
    }

    /**
     * Tutup paksa shift berjalan kasir oleh admin.
     */
    public function tutupPaksa(ShiftKasir $shift, Request $request, ShiftService $service): RedirectResponse
    {
        // Admin tidak dapat menutup paksa shift miliknya sendiri melalui jalur ini
        if ((int) $shift->user_id === (int) $request->user()->id) {
            return back()->withErrors([
                'catatan' => 'Anda tidak dapat menutup paksa shift Anda sendiri lewat menu ini. Silakan tutup shift Anda melalui halaman Kasir.',
            ]);
        }

        $request->validate([
            'uang_fisik' => 'required|numeric|min:0',
            'catatan' => 'required|string|min:5',
        ], [
            'uang_fisik.required' => 'Uang fisik di laci wajib diisi.',
            'uang_fisik.numeric' => 'Uang fisik harus berupa nominal angka.',
            'catatan.required' => 'Alasan/catatan penutupan paksa wajib diisi minimal 5 karakter.',
            'catatan.min' => 'Alasan/catatan penutupan paksa minimal 5 karakter.',
        ]);

        $service->tutup(
            shift: $shift,
            uangFisik: (int) $request->input('uang_fisik'),
            catatan: $request->input('catatan'),
            penutup: $request->user(),
            paksa: true
        );

        return redirect()->route('admin.shift.show', $shift->id)
            ->with('success', "Shift kasir {$shift->user->name} berhasil ditutup paksa.");
    }
}
