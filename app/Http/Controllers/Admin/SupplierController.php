<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSupplierRequest;
use App\Http\Requests\Admin\UpdateSupplierRequest;
use App\Models\LogAktivitas;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    /**
     * Tampilkan daftar supplier milik kios ini.
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $status = $request->query('status');

        $query = Supplier::withCount('barangMasuk')
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama', 'like', "%{$search}%")
                        ->orWhere('no_telepon', 'like', "%{$search}%");
                });
            })
            ->when($status === 'aktif', function ($q) {
                $q->where('is_aktif', true);
            })
            ->when($status === 'nonaktif', function ($q) {
                $q->where('is_aktif', false);
            });

        $supplierList = $query->latest()->paginate(15)->withQueryString();

        return view('admin.supplier.index', [
            'supplierList' => $supplierList,
            'search' => $search,
            'status' => $status,
        ]);
    }

    /**
     * Simpan supplier baru untuk kios ini.
     */
    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['kios_id'] = $request->user()->kios_id;
        $validated['is_aktif'] = true;

        $supplier = Supplier::create($validated);

        LogAktivitas::catat('supplier.tambah', "Menambahkan supplier '{$supplier->nama}'", null, $supplier->toArray());

        return redirect()->route('admin.supplier.index')
            ->with('success', "Supplier '{$supplier->nama}' berhasil ditambahkan.");
    }

    /**
     * Perbarui data supplier.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $oldData = $supplier->only(['nama', 'no_telepon', 'alamat']);
        $validated = $request->validated();

        $supplier->update($validated);

        LogAktivitas::catat(
            'supplier.ubah',
            "Memperbarui data supplier '{$supplier->nama}'",
            $oldData,
            $supplier->only(['nama', 'no_telepon', 'alamat'])
        );

        return redirect()->route('admin.supplier.index')
            ->with('success', "Data supplier '{$supplier->nama}' berhasil diperbarui.");
    }

    /**
     * Aktifkan atau nonaktifkan supplier (tanpa hapus permanen).
     */
    public function toggle(Supplier $supplier): RedirectResponse
    {
        $supplier->is_aktif = ! $supplier->is_aktif;
        $supplier->save();

        $statusStr = $supplier->is_aktif ? 'diaktifkan' : 'dinonaktifkan';

        LogAktivitas::catat('supplier.toggle', "Supplier '{$supplier->nama}' {$statusStr}");

        return back()->with('success', "Supplier '{$supplier->nama}' berhasil {$statusStr}.");
    }
}
