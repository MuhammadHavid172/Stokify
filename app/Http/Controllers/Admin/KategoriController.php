<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKategoriRequest;
use App\Http\Requests\Admin\UpdateKategoriRequest;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriController extends Controller
{
    /**
     * Tampilkan daftar kategori milik kios ini beserta jumlah barangnya.
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');

        $kategoriList = Kategori::withCount('barang')
            ->when($search, function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.kategori.index', [
            'kategoriList' => $kategoriList,
            'search' => $search,
        ]);
    }

    /**
     * Simpan kategori baru untuk kios ini.
     */
    public function store(StoreKategoriRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['kios_id'] = $request->user()->kios_id;

        $kategori = Kategori::create($validated);

        LogAktivitas::catat('kategori.tambah', "Menambahkan kategori '{$kategori->nama}'", null, $kategori->toArray());

        return redirect()->route('admin.kategori.index')
            ->with('success', "Kategori '{$kategori->nama}' berhasil ditambahkan.");
    }

    /**
     * Perbarui data kategori.
     */
    public function update(UpdateKategoriRequest $request, Kategori $kategori): RedirectResponse
    {
        $namaLama = $kategori->nama;
        $validated = $request->validated();

        $kategori->update($validated);

        LogAktivitas::catat(
            'kategori.ubah',
            "Mengubah kategori '{$namaLama}' menjadi '{$kategori->nama}'",
            ['nama' => $namaLama],
            ['nama' => $kategori->nama]
        );

        return redirect()->route('admin.kategori.index')
            ->with('success', "Kategori '{$kategori->nama}' berhasil diperbarui.");
    }

    /**
     * Hapus kategori (hanya jika belum dipakai barang).
     */
    public function destroy(Kategori $kategori): RedirectResponse
    {
        $barangCount = $kategori->barang()->count();

        if ($barangCount > 0) {
            return back()->with('error', "Kategori masih dipakai {$barangCount} barang.");
        }

        $nama = $kategori->nama;
        $kategori->delete();

        LogAktivitas::catat('kategori.hapus', "Menghapus kategori '{$nama}'");

        return redirect()->route('admin.kategori.index')
            ->with('success', "Kategori '{$nama}' berhasil dihapus.");
    }
}
