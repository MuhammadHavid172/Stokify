<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\LogAktivitas;
use Carbon\Carbon;

class NotifikasiService
{
    /**
     * Cache memori lokal per siklus request untuk mencegah duplikasi query jika lonceng dipanggil berulang (mobile & desktop).
     */
    protected static array $feedCache = [];

    /**
     * Dapatkan 15 notifikasi terbaru gabungan (stok kritis + log aktivitas operasional).
     * Menggunakan sistem antrean FIFO 15 item untuk kios yang sedang aktif.
     *
     * @param int $kiosId
     * @param string $role
     * @param int $limit
     * @return array
     */
    public static function getFeed(int $kiosId, string $role = 'admin', int $limit = 15): array
    {
        $cacheKey = "{$kiosId}_{$role}_{$limit}";
        if (isset(static::$feedCache[$cacheKey])) {
            return static::$feedCache[$cacheKey];
        }

        $feed = collect();

        // 1. Ambil Barang yang Stoknya Menipis / Kritis (Realtime)
        $barangMenipis = Barang::withoutGlobalScopes()
            ->where('kios_id', $kiosId)
            ->where('is_aktif', true)
            ->whereColumn('stok', '<=', 'stok_minimum')
            ->orderBy('stok', 'asc')
            ->limit($limit)
            ->get(['id', 'nama', 'kode', 'stok', 'stok_minimum', 'satuan', 'updated_at']);

        foreach ($barangMenipis as $b) {
            $isHabis = $b->stok <= 0;
            $updatedAt = $b->updated_at ?? now();

            $feed->push([
                'id' => 'stok_' . $b->id,
                'kategori' => 'stok',
                'badge' => $isHabis ? 'Stok Habis' : 'Stok Kritis',
                'judul' => ($isHabis ? 'Stok Habis: ' : 'Stok Menipis: ') . $b->nama,
                'pesan' => "Sisa stok {$b->stok} {$b->satuan} (Batas minimum: {$b->stok_minimum} {$b->satuan}). Kode: {$b->kode}",
                'color' => 'rose',
                'icon' => 'alert',
                'url' => $role === 'admin' ? route('admin.barang.index', ['filter_stok' => 'menipis']) : null,
                'waktu' => Carbon::parse($updatedAt)->timezone('Asia/Jayapura')->diffForHumans(),
                'timestamp' => Carbon::parse($updatedAt)->timestamp,
            ]);
        }

        // 2. Ambil Log Aktivitas Operasional Kios Terbaru (Barang Masuk, Shift, Penjualan, Penyesuaian)
        $logs = LogAktivitas::withoutGlobalScopes()
            ->leftJoin('users', 'log_aktivitas.user_id', '=', 'users.id')
            ->where('log_aktivitas.kios_id', $kiosId)
            ->latest('log_aktivitas.id')
            ->limit($limit)
            ->get([
                'log_aktivitas.id',
                'log_aktivitas.aksi',
                'log_aktivitas.deskripsi',
                'log_aktivitas.created_at',
                'users.name as user_nama',
            ]);

        foreach ($logs as $log) {
            $aksi = strtolower($log->aksi);
            $userNama = $log->user_nama ?? 'Petugas';
            $createdAt = $log->created_at ?? now();

            if (str_starts_with($aksi, 'barang_masuk.')) {
                $kategori = 'barang_masuk';
                $badge = 'Barang Masuk';
                $judul = 'Pencatatan Barang Masuk';
                $color = 'blue';
                $icon = 'truck';
                $url = $role === 'admin' ? route('admin.barang-masuk.index') : null;
            } elseif (str_starts_with($aksi, 'shift.')) {
                $kategori = 'shift';
                $badge = 'Shift Kasir';
                $judul = str_contains($aksi, 'tutup') ? 'Shift Kasir Ditutup' : 'Shift Kasir Dibuka';
                $color = 'purple';
                $icon = 'clock';
                $url = $role === 'admin' ? route('admin.shift.index') : null;
            } elseif ($aksi === 'penjualan.void') {
                $kategori = 'penjualan';
                $badge = 'Void Transaksi';
                $judul = 'Pembatalan Transaksi Nota';
                $color = 'rose';
                $icon = 'x-circle';
                $url = $role === 'admin' ? route('admin.penjualan.index') : null;
            } elseif (str_starts_with($aksi, 'penjualan.')) {
                $kategori = 'penjualan';
                $badge = 'Penjualan';
                $judul = 'Transaksi Penjualan Baru';
                $color = 'emerald';
                $icon = 'receipt';
                $url = $role === 'admin' ? route('admin.penjualan.index') : route('kasir.riwayat');
            } elseif (str_starts_with($aksi, 'penyesuaian.')) {
                $kategori = 'penyesuaian';
                $badge = 'Penyesuaian';
                $judul = 'Penyesuaian Stok Fisik';
                $color = 'amber';
                $icon = 'scale';
                $url = $role === 'admin' ? route('admin.penyesuaian.index') : null;
            } elseif (str_starts_with($aksi, 'barang.')) {
                $kategori = 'barang';
                $badge = 'Data Barang';
                $judul = 'Perubahan Data Barang';
                $color = 'teal';
                $icon = 'tag';
                $url = $role === 'admin' ? route('admin.barang.index') : null;
            } else {
                $kategori = 'aktivitas';
                $badge = 'Aktivitas Kios';
                $judul = 'Aktivitas Baru Kios';
                $color = 'emerald';
                $icon = 'bell';
                $url = $role === 'admin' ? route('admin.log.index') : null;
            }

            $feed->push([
                'id' => 'log_' . $log->id,
                'kategori' => $kategori,
                'badge' => $badge,
                'judul' => $judul,
                'pesan' => $log->deskripsi,
                'petugas' => $userNama,
                'color' => $color,
                'icon' => $icon,
                'url' => $url,
                'waktu' => Carbon::parse($createdAt)->timezone('Asia/Jayapura')->diffForHumans(),
                'timestamp' => Carbon::parse($createdAt)->timestamp,
            ]);
        }

        // 3. Gabungkan, Urutkan Berdasarkan Waktu Terbaru, dan Ambil Tepat $limit Item (FIFO)
        $result = $feed->sortByDesc('timestamp')->take($limit)->values()->all();
        static::$feedCache[$cacheKey] = $result;

        return $result;
    }

    /**
     * Bersihkan cache statis (berguna untuk pengujian atau webhook).
     */
    public static function clearCache(): void
    {
        static::$feedCache = [];
    }
}
