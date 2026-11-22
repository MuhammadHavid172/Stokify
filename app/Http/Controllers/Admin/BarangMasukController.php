<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BatalBarangMasukRequest;
use App\Http\Requests\Admin\StoreBarangMasukRequest;
use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\Supplier;
use App\Services\BarangMasukService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class BarangMasukController extends Controller
{
    /**
     * Tampilkan daftar transaksi barang masuk.
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $supplierId = $request->query('supplier_id');
        $status = $request->query('status');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalSelesai = $request->query('tanggal_selesai');

        $query = BarangMasuk::with(['supplier', 'user'])
            ->withCount('detail')
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nomor', 'like', "%{$search}%")
                        ->orWhere('catatan', 'like', "%{$search}%");
                });
            })
            ->when($supplierId, function ($q, $supplierId) {
                $q->where('supplier_id', $supplierId);
            })
            ->when($status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($tanggalMulai, function ($q, $tanggalMulai) {
                $q->whereDate('tanggal', '>=', $tanggalMulai);
            })
            ->when($tanggalSelesai, function ($q, $tanggalSelesai) {
                $q->whereDate('tanggal', '<=', $tanggalSelesai);
            });

        $barangMasukList = $query->latest('tanggal')->latest('id')->paginate(15)->withQueryString();
        $supplierList = Supplier::orderBy('nama')->get();

        return view('admin.barang-masuk.index', [
            'barangMasukList' => $barangMasukList,
            'supplierList' => $supplierList,
            'search' => $search,
            'supplierId' => $supplierId,
            'status' => $status,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
        ]);
    }

    /**
     * Tampilkan form pembuatan nota barang masuk baru.
     */
    public function create(): View
    {
        // Hanya supplier aktif yang tampil di form pembuatan baru
        $supplierList = Supplier::aktif()->orderBy('nama')->get();

        // Hanya barang aktif milik kios ini
        $barangList = Barang::aktif()
            ->orderBy('nama')
            ->get(['id', 'kode', 'nama', 'satuan', 'stok', 'harga_modal']);

        return view('admin.barang-masuk.create', [
            'supplierList' => $supplierList,
            'barangList' => $barangList,
        ]);
    }

    /**
     * Simpan transaksi barang masuk ke database.
     */
    public function store(StoreBarangMasukRequest $request, BarangMasukService $service): RedirectResponse
    {
        try {
            $barangMasuk = $service->simpan($request->validated(), $request->user());

            return redirect()->route('admin.barang-masuk.show', $barangMasuk)
                ->with('success', "Barang masuk nomor {$barangMasuk->nomor} berhasil dicatat.");
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Tampilkan detail nota barang masuk beserta item-itemnya.
     */
    public function show(BarangMasuk $barangMasuk): View
    {
        $barangMasuk->load(['supplier', 'user', 'pembatal', 'detail.barang']);

        return view('admin.barang-masuk.show', [
            'barangMasuk' => $barangMasuk,
        ]);
    }

    /**
     * Batalkan nota barang masuk (void).
     */
    public function batal(BatalBarangMasukRequest $request, BarangMasuk $barangMasuk, BarangMasukService $service): RedirectResponse
    {
        try {
            $service->batalkan($barangMasuk, $request->validated('alasan'), $request->user());

            return redirect()->route('admin.barang-masuk.show', $barangMasuk)
                ->with('success', "Nota barang masuk {$barangMasuk->nomor} berhasil dibatalkan dan stok telah disesuaikan kembali.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
