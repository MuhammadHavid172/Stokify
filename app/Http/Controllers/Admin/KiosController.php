<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KiosController extends Controller
{
    /**
     * Tampilkan formulir pengaturan profil kios dan statistik kios.
     */
    public function index(Request $request): View
    {
        $kios = $request->user()->kios;

        // Informasi statistik read-only
        $kasirAktifCount = $kios->users()
            ->where('role', 'kasir')
            ->where('is_aktif', true)
            ->count();

        $barangCount = $kios->barang()->count();

        return view('admin.kios.index', [
            'kios' => $kios,
            'kasirAktifCount' => $kasirAktifCount,
            'barangCount' => $barangCount,
        ]);
    }

    /**
     * Simpan perubahan profil kios.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kios' => ['required', 'string', 'max:100'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
        ], [
            'nama_kios.required' => 'Nama kios wajib diisi.',
            'nama_kios.max' => 'Nama kios maksimal 100 karakter.',
            'alamat.max' => 'Alamat maksimal 255 karakter.',
            'no_telepon.max' => 'Nomor telepon maksimal 20 karakter.',
        ]);

        $kios = $request->user()->kios;

        $dataLama = [
            'nama_kios' => $kios->nama_kios,
            'alamat' => $kios->alamat,
            'no_telepon' => $kios->no_telepon,
        ];

        // Simpan hanya data yang divalidasi ke kios milik admin saat ini
        $kios->update([
            'nama_kios' => $validated['nama_kios'],
            'alamat' => $validated['alamat'] ?? null,
            'no_telepon' => $validated['no_telepon'] ?? null,
        ]);

        $dataBaru = [
            'nama_kios' => $kios->nama_kios,
            'alamat' => $kios->alamat,
            'no_telepon' => $kios->no_telepon,
        ];

        // Catat log aktivitas perubahan profil kios
        LogAktivitas::catat(
            'kios.ubah',
            "Memperbarui profil kios '{$kios->nama_kios}'",
            $dataLama,
            $dataBaru
        );

        return redirect()->route('admin.kios.index')->with('success', 'Profil kios berhasil diperbarui.');
    }
}
