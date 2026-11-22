<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKasirRequest;
use App\Http\Requests\Admin\UpdateKasirRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class KasirController extends Controller
{
    /**
     * Resolusi kasir khusus milik kios yang sedang login.
     * Mengembalikan 404 jika kasir bukan milik kios ini atau bukan ber-role kasir.
     */
    protected function findKasirOrFail(int|string|User $kasir): User
    {
        $id = $kasir instanceof User ? $kasir->id : $kasir;

        return auth()->user()->kios->users()
            ->where('role', 'kasir')
            ->where('id', $id)
            ->firstOrFail();
    }

    /**
     * Tampilkan daftar kasir milik kios yang sedang login.
     */
    public function index(Request $request): View
    {
        $kios = $request->user()->kios;
        $query = $kios->users()->where('role', 'kasir');

        // Filter status (semua/aktif/nonaktif)
        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->where('is_aktif', true);
            } elseif ($request->status === 'nonaktif') {
                $query->where('is_aktif', false);
            }
        }

        // Pencarian berdasarkan nama atau username
        if ($request->filled('q')) {
            $searchTerm = '%' . strtolower($request->q) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(username) LIKE ?', [$searchTerm]);
            });
        }

        $kasirs = $query->latest()->paginate(10)->withQueryString();

        return view('admin.kasir.index', [
            'kios' => $kios,
            'kasirs' => $kasirs,
            'totalKasir' => $kios->users()->where('role', 'kasir')->count(),
            'totalAktif' => $kios->users()->where('role', 'kasir')->where('is_aktif', true)->count(),
            'totalNonaktif' => $kios->users()->where('role', 'kasir')->where('is_aktif', false)->count(),
        ]);
    }

    /**
     * Tampilkan formulir pembuatan kasir baru.
     */
    public function create(): View
    {
        return view('admin.kasir.create');
    }

    /**
     * Simpan kasir baru ke database.
     */
    public function store(StoreKasirRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $kios = $request->user()->kios;

        // Kios_id diambil dari auth user, role di-hardcode 'kasir', is_aktif = true
        User::create([
            'kios_id' => $kios->id,
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => null,
            'password' => Hash::make($validated['password']),
            'role' => 'kasir',
            'is_aktif' => true,
        ]);

        return redirect()
            ->route('admin.kasir.index')
            ->with('success', 'Akun kasir baru berhasil dibuat dan siap bertugas.');
    }

    /**
     * Tampilkan formulir ubah data kasir.
     */
    public function edit(int|string|User $kasir): View
    {
        $kasirUser = $this->findKasirOrFail($kasir);

        return view('admin.kasir.edit', [
            'kasir' => $kasirUser,
        ]);
    }

    /**
     * Perbarui data kasir (nama dan username saja).
     */
    public function update(UpdateKasirRequest $request, int|string|User $kasir): RedirectResponse
    {
        $kasirUser = $this->findKasirOrFail($kasir);
        $validated = $request->validated();

        $kasirUser->update([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
        ]);

        return redirect()
            ->route('admin.kasir.index')
            ->with('success', 'Data kasir ' . $kasirUser->name . ' berhasil diperbarui.');
    }

    /**
     * Aktifkan atau nonaktifkan akun kasir.
     */
    public function toggle(int|string|User $kasir): RedirectResponse
    {
        $kasirUser = $this->findKasirOrFail($kasir);
        $newStatus = ! $kasirUser->is_aktif;

        $kasirUser->update([
            'is_aktif' => $newStatus,
        ]);

        $message = $newStatus
            ? 'Akun kasir ' . $kasirUser->name . ' berhasil diaktifkan kembali.'
            : 'Akun kasir ' . $kasirUser->name . ' dinonaktifkan. Kasir tidak dapat masuk ke sistem.';

        return back()->with('success', $message);
    }

    /**
     * Reset password kasir oleh admin.
     * Sesi lama kasir akan otomatis terputus melalui AuthenticateSession & pembaruan remember_token.
     */
    public function resetPassword(Request $request, int|string|User $kasir): RedirectResponse
    {
        $kasirUser = $this->findKasirOrFail($kasir);

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()->min(8)],
        ], [
            'password.required' => 'Password baru kasir wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $kasirUser->update([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ]);

        return back()->with('success', 'Password kasir ' . $kasirUser->name . ' berhasil direset. Sesi aktif kasir telah terputus.');
    }
}
