<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBarangRequest;
use App\Http\Requests\Admin\UpdateBarangRequest;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Services\StokService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BarangController extends Controller
{
    /**
     * Tampilkan daftar master barang milik kios ini dengan pencarian dan filter lengkap.
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $kategoriId = $request->query('kategori_id');
        $status = $request->query('status');
        $menipis = $request->query('menipis');

        $query = Barang::with('kategori')
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama', 'like', "%{$search}%")
                        ->orWhere('kode', 'like', "%{$search}%");
                });
            })
            ->when($kategoriId, function ($q, $kategoriId) {
                $q->where('kategori_id', $kategoriId);
            })
            ->when($status === 'aktif', function ($q) {
                $q->where('is_aktif', true);
            })
            ->when($status === 'nonaktif', function ($q) {
                $q->where('is_aktif', false);
            })
            ->when($menipis === '1' || $menipis === 'true', function ($q) {
                $q->where('is_aktif', true)->whereColumn('stok', '<=', 'stok_minimum');
            });

        $barangList = $query->latest()->paginate(15)->withQueryString();
        $kategoriList = Kategori::orderBy('nama')->get();

        return view('admin.barang.index', [
            'barangList' => $barangList,
            'kategoriList' => $kategoriList,
            'search' => $search,
            'kategoriId' => $kategoriId,
            'status' => $status,
            'menipis' => $menipis,
        ]);
    }

    /**
     * Tampilkan form penambahan barang baru.
     */
    public function create(): View
    {
        $kategoriList = Kategori::orderBy('nama')->get();

        // Peta kode otomatis per kategori agar respon instan di frontend
        $kategoriKodeMap = [];
        foreach ($kategoriList as $kat) {
            $kategoriKodeMap[$kat->id] = $this->generateNextKode($kat->id);
        }

        // Rekomendasi kode otomatis umum per kios format BRG-0001 dst.
        $nextKode = $this->generateNextKode();

        $satuanOptions = ['pcs', 'bungkus', 'botol', 'kg', 'gram', 'liter', 'dus', 'pak', 'sachet', 'kotak', 'karung'];

        return view('admin.barang.create', [
            'kategoriList' => $kategoriList,
            'nextKode' => $nextKode,
            'kategoriKodeMap' => $kategoriKodeMap,
            'satuanOptions' => $satuanOptions,
        ]);
    }

    /**
     * Simpan barang baru ke database beserta mutasi stok awal jika ada.
     */
    public function store(StoreBarangRequest $request, StokService $stokService): RedirectResponse
    {
        $validated = $request->validated();
        $stokAwal = (int) ($validated['stok_awal'] ?? 0);

        // Pastikan field terlarang tidak dimasukkan
        unset($validated['stok_awal'], $validated['kios_id'], $validated['stok']);

        $validated['kios_id'] = $request->user()->kios_id;
        $validated['stok'] = 0; // Mulai dari 0, bertambah melalui StokService
        $validated['stok_minimum'] = (int) ($validated['stok_minimum'] ?? 0);
        $validated['is_aktif'] = true;

        $barang = null;

        DB::transaction(function () use ($validated, $stokAwal, $stokService, &$barang) {
            $barang = Barang::create($validated);

            if ($stokAwal > 0) {
                $stokService->catat(
                    $barang,
                    'stok_awal',
                    $stokAwal,
                    'Stok awal saat pendaftaran barang'
                );
            }

            LogAktivitas::catat('barang.tambah', "Menambahkan barang '{$barang->nama}' ({$barang->kode})", null, [
                'kode' => $barang->kode,
                'nama' => $barang->nama,
                'satuan' => $barang->satuan,
                'harga_modal' => $barang->harga_modal,
                'harga_jual' => $barang->harga_jual,
                'stok_awal' => $stokAwal,
                'stok_minimum' => $barang->stok_minimum,
            ]);
        });

        $message = "Barang '{$barang->nama}' ({$barang->kode}) berhasil ditambahkan.";

        // Peringatan jika harga jual di bawah harga modal
        if ($barang->harga_jual < $barang->harga_modal) {
            session()->flash('warning', "Peringatan: Harga jual (" . rupiah($barang->harga_jual) . ") berada di bawah harga modal (" . rupiah($barang->harga_modal) . ").");
        }

        return redirect()->route('admin.barang.index')->with('success', $message);
    }

    /**
     * Tampilkan detail informasi barang beserta riwayat mutasi stoknya.
     */
    public function show(Barang $barang): View
    {
        $mutasiList = $barang->mutasiStok()
            ->with('user')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $performa30Hari = app(\App\Services\AnalisisBarangService::class)
            ->hitung(30, $barang->kios_id)
            ->firstWhere('barang_id', $barang->id);

        return view('admin.barang.show', [
            'barang' => $barang,
            'mutasiList' => $mutasiList,
            'performa30Hari' => $performa30Hari,
        ]);
    }

    /**
     * Tampilkan form untuk mengubah data barang.
     */
    public function edit(Barang $barang): View
    {
        $kategoriList = Kategori::orderBy('nama')->get();
        $satuanOptions = ['pcs', 'bungkus', 'botol', 'kg', 'gram', 'liter', 'dus', 'pak', 'sachet', 'kotak', 'karung'];

        return view('admin.barang.edit', [
            'barang' => $barang,
            'kategoriList' => $kategoriList,
            'satuanOptions' => $satuanOptions,
        ]);
    }

    /**
     * Perbarui data barang (stok TIDAK boleh diubah dari form ini).
     */
    public function update(UpdateBarangRequest $request, Barang $barang): RedirectResponse
    {
        $validated = $request->validated();

        // Abaikan field stok jika dikirim form
        unset($validated['stok'], $validated['stok_awal'], $validated['kios_id']);

        $oldHargaModal = (int) $barang->harga_modal;
        $oldHargaJual = (int) $barang->harga_jual;
        $newHargaModal = (int) $validated['harga_modal'];
        $newHargaJual = (int) $validated['harga_jual'];

        $barang->update($validated);

        // Catat log aktivitas sesuai jenis perubahan
        if ($oldHargaModal !== $newHargaModal || $oldHargaJual !== $newHargaJual) {
            LogAktivitas::catat(
                'barang.ubah_harga',
                "Mengubah harga barang '{$barang->nama}' ({$barang->kode})",
                ['harga_modal' => $oldHargaModal, 'harga_jual' => $oldHargaJual],
                ['harga_modal' => $newHargaModal, 'harga_jual' => $newHargaJual]
            );
        } else {
            LogAktivitas::catat('barang.ubah', "Memperbarui informasi barang '{$barang->nama}' ({$barang->kode})");
        }

        // Peringatan jika harga jual di bawah harga modal
        if ($barang->harga_jual < $barang->harga_modal) {
            session()->flash('warning', "Peringatan: Harga jual (" . rupiah($barang->harga_jual) . ") berada di bawah harga modal (" . rupiah($barang->harga_modal) . ").");
        }

        return redirect()->route('admin.barang.index')
            ->with('success', "Barang '{$barang->nama}' ({$barang->kode}) berhasil diperbarui.");
    }

    /**
     * Aktifkan atau nonaktifkan barang (bukan dihapus permanen).
     */
    public function toggle(Barang $barang): RedirectResponse
    {
        $barang->is_aktif = ! $barang->is_aktif;
        $barang->save();

        $statusStr = $barang->is_aktif ? 'diaktifkan' : 'dinonaktifkan';

        LogAktivitas::catat('barang.toggle', "Barang '{$barang->nama}' ({$barang->kode}) {$statusStr}");

        return back()->with('success', "Barang '{$barang->nama}' berhasil {$statusStr}.");
    }

    /**
     * Helper untuk membuat kode barang otomatis unik per kios berdasarkan awalan kategori.
     * Contoh: Sembako -> SMB-0001, Rokok -> ROK-0001, Minuman -> MNM-0001, Default -> BRG-0001.
     */
    public function generateNextKode(?int $kategoriId = null): string
    {
        $prefix = 'BRG';

        if ($kategoriId) {
            $kategori = Kategori::find($kategoriId);
            if ($kategori) {
                $prefix = $kategori->getPrefixKode();
            }
        }

        // Cari nomor urut terakhir pada prefix ini untuk kios aktif
        $latest = Barang::where('kode', 'like', "{$prefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest && preg_match('/' . preg_quote($prefix, '/') . '-(\d+)/', $latest->kode, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $count = Barang::where('kode', 'like', "{$prefix}-%")->count();
            $nextNumber = $count + 1;
        }

        return sprintf('%s-%04d', $prefix, $nextNumber);
    }

    /**
     * Endpoint JSON untuk mendapatkan rekomendasi kode barang otomatis saat kategori dipilih.
     */
    public function generateKodeApi(Request $request): JsonResponse
    {
        $kategoriId = $request->query('kategori_id') ? (int) $request->query('kategori_id') : null;
        $kode = $this->generateNextKode($kategoriId);

        return response()->json([
            'kode' => $kode,
            'prefix' => explode('-', $kode)[0] ?? 'BRG',
        ]);
    }
}
