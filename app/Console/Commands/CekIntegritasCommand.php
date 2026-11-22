<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CekIntegritasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stokify:cek-integritas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memeriksa integritas data stok, konsistensi nota penjualan, shift lama, dan isolasi tenant (Hanya Baca)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("=== PEMERIKSAAN INTEGRITAS DATA STOKIFY ===");
        $this->info("Waktu pemeriksaan: " . Carbon::now('Asia/Jayapura')->format('Y-m-d H:i:s') . " WIT\n");

        $hasil = [];
        $totalTemuan = 0;

        // 1. Pemeriksaan (a): barang.stok vs stok_sesudah mutasi terakhir
        $barangs = DB::table('barang')->select('id', 'kios_id', 'nama', 'kode', 'stok')->get();
        $selisihStokCount = 0;
        $detailSelisihStok = [];

        foreach ($barangs as $b) {
            $latestMutasi = DB::table('mutasi_stok')
                ->where('kios_id', $b->kios_id)
                ->where('barang_id', $b->id)
                ->latest('id')
                ->first();

            if ($latestMutasi) {
                if ((int) $b->stok !== (int) $latestMutasi->stok_sesudah) {
                    $selisihStokCount++;
                    $detailSelisihStok[] = "Barang ID {$b->id} ({$b->nama}): stok master {$b->stok} != mutasi terakhir {$latestMutasi->stok_sesudah}";
                }
            } elseif ((int) $b->stok !== 0) {
                $selisihStokCount++;
                $detailSelisihStok[] = "Barang ID {$b->id} ({$b->nama}): stok {$b->stok} tanpa catatan mutasi";
            }
        }

        $totalTemuan += $selisihStokCount;
        $hasil[] = [
            '1',
            'Sinkronisasi Stok Barang vs Mutasi Terakhir',
            $selisihStokCount === 0 ? '<info>AMAN</info>' : '<error>SELISIH</error>',
            $selisihStokCount,
            $selisihStokCount === 0 ? 'Semua stok master cocok dengan saldo mutasi terakhir' : implode('; ', array_slice($detailSelisihStok, 0, 3)),
        ];

        // 2. Pemeriksaan (b): nota selesai dengan total != SUM(detail_penjualan.subtotal)
        $penjualanInkonsisten = DB::table('penjualan as p')
            ->join('detail_penjualan as d', 'd.penjualan_id', '=', 'p.id')
            ->where('p.status', 'selesai')
            ->groupBy('p.id', 'p.nomor', 'p.total')
            ->select('p.id', 'p.nomor', 'p.total', DB::raw('SUM(d.subtotal) as total_detail'))
            ->havingRaw('p.total != total_detail')
            ->get();

        $jumlahNotaInkonsisten = $penjualanInkonsisten->count();
        $totalTemuan += $jumlahNotaInkonsisten;
        $hasil[] = [
            '2',
            'Konsistensi Total Nota Penjualan vs Subtotal Detail',
            $jumlahNotaInkonsisten === 0 ? '<info>AMAN</info>' : '<error>SELISIH</error>',
            $jumlahNotaInkonsisten,
            $jumlahNotaInkonsisten === 0 ? 'Semua nota selesai memiliki total matematis yang konsisten' : "Ditemukan {$jumlahNotaInkonsisten} nota dengan total berbeda",
        ];

        // 3. Pemeriksaan (c): shift berjalan lebih dari 16 jam
        $batasWaktuShift = Carbon::now()->subHours(16);
        $shiftLama = DB::table('shift_kasir')
            ->where('status', 'berjalan')
            ->where('waktu_buka', '<', $batasWaktuShift)
            ->get();

        $jumlahShiftLama = $shiftLama->count();
        $totalTemuan += $jumlahShiftLama;
        $hasil[] = [
            '3',
            'Shift Kasir Menggantung (> 16 Jam)',
            $jumlahShiftLama === 0 ? '<info>AMAN</info>' : '<comment>PERINGATAN</comment>',
            $jumlahShiftLama,
            $jumlahShiftLama === 0 ? 'Tidak ada shift berjalan yang melebihi 16 jam' : "Ditemukan {$jumlahShiftLama} shift terbuka > 16 jam (perlu tutup paksa)",
        ];

        // 4. Pemeriksaan (d): pengguna kasir/admin tanpa kios yang valid
        $penggunaTanpaKios = DB::table('users as u')
            ->leftJoin('kios as k', 'k.id', '=', 'u.kios_id')
            ->whereNull('u.kios_id')
            ->orWhereNull('k.id')
            ->select('u.id', 'u.name', 'u.email')
            ->get();

        $jumlahPenggunaYatim = $penggunaTanpaKios->count();
        $totalTemuan += $jumlahPenggunaYatim;
        $hasil[] = [
            '4',
            'Pengguna Tanpa Kios Terdaftar (Orphaned Users)',
            $jumlahPenggunaYatim === 0 ? '<info>AMAN</info>' : '<error>ANOMALI</error>',
            $jumlahPenggunaYatim,
            $jumlahPenggunaYatim === 0 ? 'Seluruh user terikat pada relasi tenant Kios yang valid' : "Ditemukan {$jumlahPenggunaYatim} user tanpa kios",
        ];

        // 5. Pemeriksaan (e): baris di tabel bertrait BelongsToKios dengan kios_id tidak valid
        $tenantTables = [
            'barang',
            'kategori',
            'supplier',
            'barang_masuk',
            'detail_barang_masuk',
            'penyesuaian_stok',
            'detail_penyesuaian_stok',
            'penjualan',
            'detail_penjualan',
            'shift_kasir',
            'mutasi_stok',
            'log_aktivitas',
        ];

        $tabelTidakValid = [];
        $totalBarisYatim = 0;

        foreach ($tenantTables as $tbl) {
            $count = DB::table($tbl . ' as t')
                ->leftJoin('kios as k', 'k.id', '=', 't.kios_id')
                ->whereNull('k.id')
                ->count();

            if ($count > 0) {
                $totalBarisYatim += $count;
                $tabelTidakValid[] = "{$tbl} ({$count} baris)";
            }
        }

        $totalTemuan += $totalBarisYatim;
        $hasil[] = [
            '5',
            'Validasi Foreign Key Kios pada Seluruh Tabel Tenant',
            $totalBarisYatim === 0 ? '<info>AMAN</info>' : '<error>ANOMALI</error>',
            $totalBarisYatim,
            $totalBarisYatim === 0 ? 'Seluruh baris tabel tenant merujuk ke kios_id yang valid' : 'Baris yatim: ' . implode(', ', $tabelTidakValid),
        ];

        // Render tabel konsol
        $this->table(
            ['No', 'Indikator Integritas Data', 'Status', 'Temuan', 'Catatan Audit'],
            $hasil
        );

        if ($totalTemuan > 0) {
            $this->error("\n[GAGAL] Ditemukan {$totalTemuan} temuan anomali atau ketidakkonsistenan data!");
            return Command::FAILURE;
        }

        $this->info("\n[LULUS] Seluruh pemeriksaan integritas berhasil dengan 0 temuan.");
        return Command::SUCCESS;
    }
}
