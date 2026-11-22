<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogAktivitasController extends Controller
{
    /**
     * Tampilkan daftar log aktivitas kios yang sedang login (Hanya Baca).
     */
    public function index(Request $request): View
    {
        $kiosId = $request->user()->kios_id;

        // Default tanggal: 7 hari terakhir (Asia/Jayapura)
        $defaultMulai = Carbon::now('Asia/Jayapura')->subDays(6)->toDateString();
        $defaultSelesai = Carbon::now('Asia/Jayapura')->toDateString();

        $tanggalMulai = $request->input('tanggal_mulai', $defaultMulai);
        $tanggalSelesai = $request->input('tanggal_selesai', $defaultSelesai);
        $userId = $request->input('user_id');
        $kategoriAksi = $request->input('kategori_aksi');
        $q = $request->input('q');

        // Query Log Aktivitas (Global Scope BelongsToKios otomatis membatasi kios_id)
        $query = LogAktivitas::with('user')->latest('id');

        // Filter rentang tanggal
        if ($tanggalMulai) {
            $start = Carbon::parse($tanggalMulai, 'Asia/Jayapura')->startOfDay();
            $query->where('created_at', '>=', $start);
        }

        if ($tanggalSelesai) {
            $end = Carbon::parse($tanggalSelesai, 'Asia/Jayapura')->endOfDay();
            $query->where('created_at', '<=', $end);
        }

        // Filter user (harus milik kios sendiri)
        if ($userId) {
            $userKios = User::where('kios_id', $kiosId)->find($userId);
            if ($userKios) {
                $query->where('user_id', $userId);
            }
        }

        // Filter kelompok aksi
        if ($kategoriAksi) {
            $query->where(function ($sub) use ($kategoriAksi) {
                $sub->where('aksi', 'like', "{$kategoriAksi}.%")
                    ->orWhere('aksi', $kategoriAksi);
            });
        }

        // Pencarian deskripsi
        if ($q) {
            $query->where('deskripsi', 'like', '%' . trim($q) . '%');
        }

        $logs = $query->paginate(25)->withQueryString();

        // Transformasi data JSON log untuk menyembunyikan field sensitif (password, token, dsb)
        $logs->getCollection()->transform(function ($item) {
            $item->data_lama_masked = $this->maskSensitiveData($item->data_lama);
            $item->data_baru_masked = $this->maskSensitiveData($item->data_baru);
            return $item;
        });

        // Daftar pengguna kios untuk filter dropdown
        $users = User::where('kios_id', $kiosId)->orderBy('name')->get();

        // Kategori aksi yang tersedia
        $kategoriList = [
            'barang' => 'Barang & Kategori',
            'barang_masuk' => 'Barang Masuk',
            'penyesuaian' => 'Penyesuaian & Opname',
            'shift' => 'Shift Kasir',
            'penjualan' => 'Penjualan & Void',
            'laporan' => 'Laporan & PDF',
            'analisis' => 'Analisis Performa',
            'kios' => 'Profil Kios',
            'supplier' => 'Data Supplier',
        ];

        return view('admin.log.index', [
            'logs' => $logs,
            'users' => $users,
            'kategoriList' => $kategoriList,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'selectedUserId' => $userId,
            'selectedKategori' => $kategoriAksi,
            'searchQuery' => $q,
        ]);
    }

    /**
     * Masking nilai field sensitif secara rekursif sebelum ditampilkan di UI.
     */
    private function maskSensitiveData(mixed $data): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        $masked = [];
        $sensitiveKeys = ['password', 'password_confirmation', 'remember_token', 'token', 'pin', 'secret'];

        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), $sensitiveKeys, true)) {
                $masked[$key] = '********';
            } elseif (is_array($value)) {
                $masked[$key] = $this->maskSensitiveData($value);
            } else {
                $masked[$key] = $value;
            }
        }

        return $masked;
    }
}
