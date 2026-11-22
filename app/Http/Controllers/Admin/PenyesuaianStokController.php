<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePenyesuaianManualRequest;
use App\Http\Requests\Admin\StorePenyesuaianOpnameRequest;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\PenyesuaianStok;
use App\Services\PenyesuaianStokService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class PenyesuaianStokController extends Controller
{
    /**
     * Tampilkan daftar riwayat penyesuaian stok (manual & opname).
     */
    public function index(Request $request): View
    {
        $query = PenyesuaianStok::with(['user', 'detail.barang'])
            ->latest('tanggal')
            ->latest('id');

        // Filter pencarian nomor / catatan
        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor', 'LIKE', "%{$search}%")
                    ->orWhere('catatan', 'LIKE', "%{$search}%");
            });
        }

        // Filter mode: manual / opname
        if ($mode = $request->query('mode')) {
            if (in_array($mode, ['manual', 'opname'], true)) {
                $query->where('mode', $mode);
            }
        }

        // Filter rentang tanggal
        if ($tanggalMulai = $request->query('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $tanggalMulai);
        }
        if ($tanggalSelesai = $request->query('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $tanggalSelesai);
        }

        $penyesuaianList = $query->paginate(15)->withQueryString();

        return view('admin.penyesuaian.index', [
            'penyesuaianList' => $penyesuaianList,
        ]);
    }

    /**
     * Tampilkan formulir penyesuaian stok manual (rusak, hilang, kedaluwarsa, koreksi).
     */
    public function create(): View
    {
        // Ambil seluruh barang milik kios (aktif & nonaktif boleh dipilih untuk penyesuaian)
        $barangList = Barang::with('kategori')
            ->orderBy('is_aktif', 'desc')
            ->orderBy('nama', 'asc')
            ->get();

        return view('admin.penyesuaian.create', [
            'barangList' => $barangList,
        ]);
    }

    /**
     * Simpan penyesuaian stok manual.
     */
    public function store(StorePenyesuaianManualRequest $request, PenyesuaianStokService $service): RedirectResponse
    {
        try {
            $penyesuaian = $service->simpanManual($request->validated(), $request->user());

            return redirect()->route('admin.penyesuaian.show', $penyesuaian)
                ->with('success', "Penyesuaian stok nomor {$penyesuaian->nomor} berhasil dicatat.");
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Tampilkan halaman formulir stok opname sederhana.
     */
    public function opname(Request $request): View
    {
        $kategoriList = Kategori::orderBy('nama', 'asc')->get();

        $query = Barang::with('kategori');

        // Sertakan nonaktif bila dicentang, default hanya barang aktif
        if (! $request->boolean('sertakan_nonaktif')) {
            $query->where('is_aktif', true);
        }

        // Filter kategori
        if ($kategoriId = $request->query('kategori_id')) {
            $query->where('kategori_id', $kategoriId);
        }

        // Filter pencarian kode / nama
        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'LIKE', "%{$search}%")
                    ->orWhere('nama', 'LIKE', "%{$search}%");
            });
        }

        $barangList = $query->orderBy('nama', 'asc')->get();

        return view('admin.penyesuaian.opname', [
            'kategoriList' => $kategoriList,
            'barangList' => $barangList,
        ]);
    }

    /**
     * Simpan hasil stok opname.
     */
    public function storeOpname(StorePenyesuaianOpnameRequest $request, PenyesuaianStokService $service): RedirectResponse
    {
        try {
            $penyesuaian = $service->simpanOpname($request->validated(), $request->user());

            return redirect()->route('admin.penyesuaian.show', $penyesuaian)
                ->with('success', "Stok opname nomor {$penyesuaian->nomor} berhasil disimpan dan disesuaikan.");
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Tampilkan detail nota penyesuaian stok.
     */
    public function show(PenyesuaianStok $penyesuaianStok): View
    {
        $penyesuaianStok->load(['user', 'detail.barang.kategori']);

        return view('admin.penyesuaian.show', [
            'penyesuaian' => $penyesuaianStok,
        ]);
    }
}
