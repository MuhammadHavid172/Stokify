<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\DetailPenjualan;
use App\Models\MutasiStok;
use App\Models\Penjualan;
use App\Models\PenyesuaianStok;
use App\Models\ShiftKasir;
use App\Models\User;
use App\ValueObjects\PeriodeLaporan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LaporanService
{
    /**
     * LAPORAN A: RINGKASAN KEUANGAN
     *
     * Sumber data kebenaran tunggal untuk tampilan layar dan unduh PDF.
     * Menggunakan snapshot transaksi (penjualan.total, penjualan.total_modal,
     * detail_penjualan.subtotal, detail_penjualan.subtotal_modal).
     *
     * CATATAN MULTI-TENANCY:
     * Model Eloquent di bawah ini menggunakan trait BelongsToKios yang secara otomatis
     * memfilter kios_id melalui Global Scope. Untuk query mentah / DB::table(),
     * klausul `where kios_id = ?` wajib ditambahkan secara eksplisit.
     *
     * @param PeriodeLaporan $periode
     * @param int $kiosId
     * @param User $pencetak
     * @return array
     */
    public function keuangan(PeriodeLaporan $periode, int $kiosId, User $pencetak): array
    {
        $start = $periode->tanggalMulai;
        $end = $periode->tanggalAkhir;
        $kios = $pencetak->kios;

        // 1. Ringkasan Angka Penjualan Selesai
        $penjualanSelesaiQuery = Penjualan::query()
            ->where('kios_id', $kiosId)
            ->where('status', 'selesai')
            ->whereBetween('waktu_transaksi', [$start, $end]);

        $jumlahTransaksi = (int) (clone $penjualanSelesaiQuery)->count();
        $omzet = (int) (clone $penjualanSelesaiQuery)->sum('total');
        $hpp = (int) (clone $penjualanSelesaiQuery)->sum('total_modal');
        $labaKotor = $omzet - $hpp;
        $marginLabaKotor = $omzet > 0 ? round(($labaKotor / $omzet) * 100, 1) . '%' : '-';
        $rataRataTransaksi = $jumlahTransaksi > 0 ? (int) round($omzet / $jumlahTransaksi) : 0;

        // 2. Informasi Pendukung
        // A. Nota Dibatalkan
        $penjualanBatalQuery = Penjualan::query()
            ->where('kios_id', $kiosId)
            ->where('status', 'dibatalkan')
            ->whereBetween('waktu_transaksi', [$start, $end]);

        $jumlahNotaBatal = (int) (clone $penjualanBatalQuery)->count();
        $nilaiNotaBatal = (int) (clone $penjualanBatalQuery)->sum('total');

        // B. Total Kulakan Periode (Barang Masuk berstatus selesai)
        $totalKulakan = (int) BarangMasuk::query()
            ->where('kios_id', $kiosId)
            ->where('status', 'selesai')
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->sum('total');

        // C. Kerugian Stok Periode (Penyesuaian Stok)
        $kerugianStok = (int) PenyesuaianStok::query()
            ->where('kios_id', $kiosId)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->sum('total_kerugian');

        $labaKotorSetelahKerugian = $labaKotor - $kerugianStok;

        // 3. Rincian per Hari
        // Mengelompokkan transaksi per tanggal dalam zona Asia/Jayapura
        // Query agregasi harian untuk penjualan selesai
        $transaksiHarianRaw = DB::table('penjualan')
            ->where('kios_id', $kiosId)
            ->where('status', 'selesai')
            ->whereBetween('waktu_transaksi', [$start, $end])
            ->selectRaw('DATE(waktu_transaksi) as tgl, COUNT(*) as jml_trx, SUM(total) as omzet, SUM(total_modal) as hpp')
            ->groupBy(DB::raw('DATE(waktu_transaksi)'))
            ->get()
            ->keyBy('tgl');

        $rincianHarian = [];
        $totalRincianTrx = 0;
        $totalRincianOmzet = 0;
        $totalRincianHpp = 0;
        $totalRincianLaba = 0;

        // Jika periode pendek (rentang <= 31 hari), tampilkan seluruh hari termasuk yang bernilai 0
        if ($periode->rentangHari <= 31) {
            $cur = $start->copy();
            while ($cur->lessThanOrEqualTo($end)) {
                $tglStr = $cur->toDateString();
                $item = $transaksiHarianRaw->get($tglStr);

                $tOmzet = $item ? (int) $item->omzet : 0;
                $tHpp = $item ? (int) $item->hpp : 0;
                $tJml = $item ? (int) $item->jml_trx : 0;
                $tLaba = $tOmzet - $tHpp;

                $rincianHarian[] = [
                    'tanggal' => $tglStr,
                    'tanggal_label' => PeriodeLaporan::formatTanggalIndonesia($cur, true),
                    'jumlah_transaksi' => $tJml,
                    'omzet' => $tOmzet,
                    'hpp' => $tHpp,
                    'laba_kotor' => $tLaba,
                ];

                $totalRincianTrx += $tJml;
                $totalRincianOmzet += $tOmzet;
                $totalRincianHpp += $tHpp;
                $totalRincianLaba += $tLaba;

                $cur->addDay();
            }
        } else {
            // Periode panjang (> 31 hari): hanya tampilkan hari yang memiliki transaksi
            foreach ($transaksiHarianRaw as $tglStr => $item) {
                $tDate = Carbon::parse($tglStr, 'Asia/Jayapura');
                $tOmzet = (int) $item->omzet;
                $tHpp = (int) $item->hpp;
                $tJml = (int) $item->jml_trx;
                $tLaba = $tOmzet - $tHpp;

                $rincianHarian[] = [
                    'tanggal' => $tglStr,
                    'tanggal_label' => PeriodeLaporan::formatTanggalIndonesia($tDate, true),
                    'jumlah_transaksi' => $tJml,
                    'omzet' => $tOmzet,
                    'hpp' => $tHpp,
                    'laba_kotor' => $tLaba,
                ];

                $totalRincianTrx += $tJml;
                $totalRincianOmzet += $tOmzet;
                $totalRincianHpp += $tHpp;
                $totalRincianLaba += $tLaba;
            }
        }

        // 4. Rekap per Kasir
        // Query kasir yang bertransaksi pada nota selesai dalam periode
        $rekapKasir = DB::table('penjualan')
            ->join('users', 'penjualan.user_id', '=', 'users.id')
            ->where('penjualan.kios_id', $kiosId)
            ->where('penjualan.status', 'selesai')
            ->whereBetween('penjualan.waktu_transaksi', [$start, $end])
            ->selectRaw('penjualan.user_id, users.name as nama_kasir, COUNT(*) as jumlah_transaksi, SUM(penjualan.total) as omzet')
            ->groupBy('penjualan.user_id', 'users.name')
            ->orderByDesc('omzet')
            ->get()
            ->map(function ($row) {
                return [
                    'user_id' => $row->user_id,
                    'nama_kasir' => $row->nama_kasir,
                    'jumlah_transaksi' => (int) $row->jumlah_transaksi,
                    'omzet' => (int) $row->omzet,
                ];
            })
            ->all();

        // 5. 10 Barang Laba Terbesar & 10 Barang Paling Banyak Terjual
        // Menggunakan detail_penjualan pada nota selesai dalam periode
        $barangTerjualRaw = DB::table('detail_penjualan')
            ->join('penjualan', 'detail_penjualan.penjualan_id', '=', 'penjualan.id')
            ->where('detail_penjualan.kios_id', $kiosId)
            ->where('penjualan.kios_id', $kiosId)
            ->where('penjualan.status', 'selesai')
            ->whereBetween('penjualan.waktu_transaksi', [$start, $end])
            ->selectRaw('
                detail_penjualan.barang_id,
                detail_penjualan.kode_barang,
                detail_penjualan.nama_barang,
                detail_penjualan.satuan,
                SUM(detail_penjualan.jumlah) as total_terjual,
                SUM(detail_penjualan.subtotal) as total_omzet,
                SUM(detail_penjualan.subtotal - detail_penjualan.subtotal_modal) as total_laba
            ')
            ->groupBy(
                'detail_penjualan.barang_id',
                'detail_penjualan.kode_barang',
                'detail_penjualan.nama_barang',
                'detail_penjualan.satuan'
            )
            ->get();

        $sepuluhPalingLaku = $barangTerjualRaw
            ->sortByDesc('total_terjual')
            ->take(10)
            ->values()
            ->map(function ($row) {
                return [
                    'barang_id' => $row->barang_id,
                    'kode' => $row->kode_barang,
                    'nama' => $row->nama_barang,
                    'satuan' => $row->satuan,
                    'jumlah' => (int) $row->total_terjual,
                    'omzet' => (int) $row->total_omzet,
                    'laba_kotor' => (int) $row->total_laba,
                ];
            })
            ->all();

        $sepuluhLabaTerbesar = $barangTerjualRaw
            ->sortByDesc('total_laba')
            ->take(10)
            ->values()
            ->map(function ($row) {
                return [
                    'barang_id' => $row->barang_id,
                    'kode' => $row->kode_barang,
                    'nama' => $row->nama_barang,
                    'satuan' => $row->satuan,
                    'jumlah' => (int) $row->total_terjual,
                    'omzet' => (int) $row->total_omzet,
                    'laba_kotor' => (int) $row->total_laba,
                ];
            })
            ->all();

        // 6. Data Grafik Harian (bila periode <= 31 hari)
        $grafikHarian = [];
        if ($periode->rentangHari <= 31) {
            $maxOmzet = collect($rincianHarian)->max('omzet') ?: 1;
            foreach ($rincianHarian as $rh) {
                $grafikHarian[] = [
                    'tanggal' => $rh['tanggal'],
                    'label' => Carbon::parse($rh['tanggal'])->format('d/m'),
                    'omzet' => $rh['omzet'],
                    'persen' => round(($rh['omzet'] / $maxOmzet) * 100, 1),
                ];
            }
        }

        return [
            'kepala' => [
                'nama_kios' => $kios->nama_kios ?? 'Stokify Kios',
                'alamat' => $kios->alamat ?? '-',
                'judul' => 'Laporan Ringkasan Keuangan',
                'periode_label' => $periode->label,
                'waktu_cetak' => Carbon::now('Asia/Jayapura')->translatedFormat('d F Y, H:i') . ' WIT',
                'nama_pencetak' => $pencetak->name,
            ],
            'ringkasan' => [
                'jumlah_transaksi' => $jumlahTransaksi,
                'omzet' => $omzet,
                'hpp' => $hpp,
                'laba_kotor' => $labaKotor,
                'margin_laba_kotor' => $marginLabaKotor,
                'rata_rata_transaksi' => $rataRataTransaksi,
                'jumlah_nota_batal' => $jumlahNotaBatal,
                'nilai_nota_batal' => $nilaiNotaBatal,
                'total_kulakan' => $totalKulakan,
                'kerugian_stok' => $kerugianStok,
                'laba_kotor_setelah_kerugian_stok' => $labaKotorSetelahKerugian,
            ],
            'rincian_harian' => $rincianHarian,
            'total_rincian' => [
                'jumlah_transaksi' => $totalRincianTrx,
                'omzet' => $totalRincianOmzet,
                'hpp' => $totalRincianHpp,
                'laba_kotor' => $totalRincianLaba,
            ],
            'rekap_kasir' => $rekapKasir,
            'sepuluh_paling_laku' => $sepuluhPalingLaku,
            'sepuluh_laba_terbesar' => $sepuluhLabaTerbesar,
            'grafik_harian' => $grafikHarian,
            'catatan_kaki' => [
                'laba_kotor' => 'Laba kotor belum memperhitungkan biaya operasional (sewa, listrik, gaji, dan lain-lain).',
                'laba_setelah_kerugian' => 'Laba kotor dikurangi total nilai kerugian penyesuaian stok (barang rusak, hilang, atau selisih opname).',
            ],
        ];
    }

    /**
     * LAPORAN B: AUDIT TRANSAKSI DAN SHIFT
     *
     * Menampilkan rekapitulasi shift kasir, daftar transaksi penjualan lengkap,
     * dan rincian pembatalan (void) nota beserta alasan dan aktornya.
     * Menggunakan eager loading untuk menghindari masalah query N+1.
     *
     * @param PeriodeLaporan $periode
     * @param int $kiosId
     * @param User $pencetak
     * @return array
     */
    public function audit(PeriodeLaporan $periode, int $kiosId, User $pencetak): array
    {
        $start = $periode->tanggalMulai;
        $end = $periode->tanggalAkhir;
        $kios = $pencetak->kios;

        // 1. Rekap Shift (shift yang waktu_bukanya dalam periode)
        $shiftList = ShiftKasir::query()
            ->where('kios_id', $kiosId)
            ->whereBetween('waktu_buka', [$start, $end])
            ->with(['user', 'ditutupOlehUser'])
            ->withSum(['penjualan as live_penjualan_tunai' => function ($q) {
                $q->where('status', 'selesai');
            }], 'total')
            ->orderBy('waktu_buka', 'asc')
            ->get();

        $jumlahShift = $shiftList->count();
        $totalSelisihKas = 0;
        $jumlahShiftSelisih = 0;

        $rekapShift = $shiftList->map(function (ShiftKasir $s) use (&$totalSelisihKas, &$jumlahShiftSelisih) {
            $isDitutup = ($s->status === 'ditutup');
            $penjualanTunai = $isDitutup ? (int) $s->total_penjualan_tunai : (int) ($s->live_penjualan_tunai ?? 0);
            $uangSeharusnya = $isDitutup ? (int) $s->uang_seharusnya : ((int) $s->modal_awal + $penjualanTunai);
            $uangFisik = $isDitutup ? (int) $s->uang_fisik : null;
            $selisih = $isDitutup ? (int) $s->selisih : null;

            if ($isDitutup) {
                $totalSelisihKas += (int) $s->selisih;
                if ((int) $s->selisih !== 0) {
                    $jumlahShiftSelisih++;
                }
            }

            return [
                'id' => $s->id,
                'kasir' => $s->user->name ?? 'Kasir',
                'waktu_buka' => Carbon::parse($s->waktu_buka)->timezone('Asia/Jayapura')->format('d/m/Y H:i'),
                'waktu_tutup' => $s->waktu_tutup ? Carbon::parse($s->waktu_tutup)->timezone('Asia/Jayapura')->format('d/m/Y H:i') : '-',
                'modal_awal' => (int) $s->modal_awal,
                'penjualan_tunai' => $penjualanTunai,
                'uang_seharusnya' => $uangSeharusnya,
                'uang_fisik' => $uangFisik,
                'selisih' => $selisih,
                'status' => $s->status,
                'ditutup_paksa' => (bool) $s->ditutup_paksa,
                'ditutup_oleh' => $s->ditutupOlehUser->name ?? null,
                'catatan' => $s->catatan,
            ];
        })->all();

        // 2. Daftar Nota Penjualan Lengkap (urut waktu naik)
        // Eager loading detail_penjualan jumlah item menggunakan withCount untuk performa optimal tanpa N+1
        $penjualanList = Penjualan::query()
            ->where('kios_id', $kiosId)
            ->whereBetween('waktu_transaksi', [$start, $end])
            ->with(['user', 'dibatalkanOleh'])
            ->withCount('detail as total_item')
            ->orderBy('waktu_transaksi', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $jumlahNotaSelesai = 0;
        $jumlahNotaBatal = 0;
        $totalOmzet = 0;
        $totalHpp = 0;
        $totalLabaKotor = 0;

        $daftarNota = $penjualanList->map(function (Penjualan $p) use (
            &$jumlahNotaSelesai,
            &$jumlahNotaBatal,
            &$totalOmzet,
            &$totalHpp,
            &$totalLabaKotor
        ) {
            $isSelesai = ($p->status === 'selesai');
            $labaKotor = (int) $p->total - (int) $p->total_modal;

            if ($isSelesai) {
                $jumlahNotaSelesai++;
                $totalOmzet += (int) $p->total;
                $totalHpp += (int) $p->total_modal;
                $totalLabaKotor += $labaKotor;
            } else {
                $jumlahNotaBatal++;
            }

            return [
                'id' => $p->id,
                'nomor' => $p->nomor,
                'waktu_transaksi' => Carbon::parse($p->waktu_transaksi)->timezone('Asia/Jayapura')->format('d/m/Y H:i'),
                'kasir' => $p->user->name ?? 'Kasir',
                'jumlah_item' => (int) $p->total_item,
                'total' => (int) $p->total,
                'total_modal' => (int) $p->total_modal,
                'laba_kotor' => $labaKotor,
                'status' => $p->status,
            ];
        })->all();

        // 3. Daftar Pembatalan Nota (Void) disaring langsung dari koleksi penjualan
        $daftarVoid = $penjualanList
            ->where('status', 'dibatalkan')
            ->values()
            ->map(function (Penjualan $p) {
                return [
                    'id' => $p->id,
                    'nomor' => $p->nomor,
                    'waktu_transaksi' => Carbon::parse($p->waktu_transaksi)->timezone('Asia/Jayapura')->format('d/m/Y H:i'),
                    'waktu_dibatalkan' => $p->dibatalkan_pada ? Carbon::parse($p->dibatalkan_pada)->timezone('Asia/Jayapura')->format('d/m/Y H:i') : '-',
                    'total' => (int) $p->total,
                    'kasir' => $p->user->name ?? 'Kasir',
                    'pembatal' => $p->dibatalkanOleh->name ?? '-',
                    'alasan' => $p->alasan_batal ?? '-',
                ];
            })
            ->all();

        return [
            'kepala' => [
                'nama_kios' => $kios->nama_kios ?? 'Stokify Kios',
                'alamat' => $kios->alamat ?? '-',
                'judul' => 'Laporan Audit Transaksi dan Shift',
                'periode_label' => $periode->label,
                'waktu_cetak' => Carbon::now('Asia/Jayapura')->translatedFormat('d F Y, H:i') . ' WIT',
                'nama_pencetak' => $pencetak->name,
            ],
            'ringkasan' => [
                'jumlah_nota_selesai' => $jumlahNotaSelesai,
                'jumlah_nota_batal' => $jumlahNotaBatal,
                'total_omzet' => $totalOmzet,
                'jumlah_shift' => $jumlahShift,
                'total_selisih_kas' => $totalSelisihKas,
                'jumlah_shift_selisih' => $jumlahShiftSelisih,
            ],
            'rekap_shift' => $rekapShift,
            'total_selisih_kas' => $totalSelisihKas,
            'daftar_nota' => $daftarNota,
            'total_nota' => [
                'omzet' => $totalOmzet,
                'hpp' => $totalHpp,
                'laba_kotor' => $totalLabaKotor,
            ],
            'daftar_void' => $daftarVoid,
        ];
    }

    /**
     * LAPORAN C: MUTASI DAN SISA STOK
     *
     * Menghitung stok awal, mutasi masuk, terjual, retur void, penyesuaian,
     * dan stok akhir murni dari riwayat mutasi_stok menggunakan query teragregasi.
     * Menghitung nilai persediaan akhir dan menghasilkan daftar belanja kulakan.
     *
     * @param PeriodeLaporan $periode
     * @param int $kiosId
     * @param User $pencetak
     * @param array $filters (q, kategori_id, hanya_bergerak, hanya_perlu_kulakan)
     * @return array
     */
    public function stok(PeriodeLaporan $periode, int $kiosId, User $pencetak, array $filters = []): array
    {
        $start = $periode->tanggalMulai;
        $end = $periode->tanggalAkhir;
        $kios = $pencetak->kios;

        // 1. Dapatkan stok_sesudah dari mutasi terakhir SEBELUM awal periode untuk setiap barang
        // Query teragregasi cepat dengan self-join
        $stokAwalSubQuery = DB::table('mutasi_stok')
            ->select('barang_id', DB::raw('MAX(id) as max_id'))
            ->where('kios_id', $kiosId)
            ->where('created_at', '<', $start)
            ->groupBy('barang_id');

        $stokAwalMap = DB::table('mutasi_stok as m1')
            ->joinSub($stokAwalSubQuery, 'm2', function ($join) {
                $join->on('m1.id', '=', 'm2.max_id');
            })
            ->where('m1.kios_id', $kiosId)
            ->pluck('m1.stok_sesudah', 'm1.barang_id');

        // 2. Dapatkan seluruh pergerakan mutasi DALAM periode per barang (SUM kondisional)
        $mutasiPeriodeMap = DB::table('mutasi_stok')
            ->where('kios_id', $kiosId)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('
                barang_id,
                SUM(CASE WHEN tipe IN (\'barang_masuk\', \'stok_awal\') THEN jumlah ELSE 0 END) as masuk,
                SUM(CASE WHEN tipe = \'penjualan\' THEN ABS(jumlah) ELSE 0 END) as terjual,
                SUM(CASE WHEN tipe = \'void_penjualan\' THEN jumlah ELSE 0 END) as retur_void,
                SUM(CASE WHEN tipe = \'penyesuaian\' THEN jumlah ELSE 0 END) as penyesuaian,
                SUM(jumlah) as delta
            ')
            ->groupBy('barang_id')
            ->get()
            ->keyBy('barang_id');

        // 3. Ambil seluruh data barang milik kios
        $barangQuery = Barang::query()
            ->where('kios_id', $kiosId)
            ->with(['kategori']);

        // Filter kategori jika diberikan
        if (! empty($filters['kategori_id'])) {
            $barangQuery->where('kategori_id', $filters['kategori_id']);
        }

        // Filter pencarian nama / kode jika diberikan
        if (! empty($filters['q'])) {
            $keyword = trim((string) $filters['q']);
            $barangQuery->where(function ($sub) use ($keyword) {
                $sub->where('nama', 'LIKE', "%{$keyword}%")
                    ->orWhere('kode', 'LIKE', "%{$keyword}%");
            });
        }

        $barangList = $barangQuery->orderBy('kode', 'asc')->get();

        $rows = [];
        $totalUnitMasuk = 0;
        $totalUnitTerjual = 0;
        $totalNilaiPersediaan = 0;
        $daftarKulakan = [];

        foreach ($barangList as $b) {
            $stokAwal = (int) ($stokAwalMap[$b->id] ?? 0);
            $mutasi = $mutasiPeriodeMap->get($b->id);

            $masuk = $mutasi ? (int) $mutasi->masuk : 0;
            $terjual = $mutasi ? (int) $mutasi->terjual : 0;
            $returVoid = $mutasi ? (int) $mutasi->retur_void : 0;
            $penyesuaian = $mutasi ? (int) $mutasi->penyesuaian : 0;
            $delta = $mutasi ? (int) $mutasi->delta : 0;

            // stok_akhir = stok_awal + delta
            $stokAkhir = $stokAwal + $delta;

            $hasMovement = ($masuk > 0 || $terjual > 0 || $returVoid > 0 || $penyesuaian !== 0);

            // Barang nonaktif yang tidak memiliki pergerakan dan stoknya 0 disaring keluar
            if (! $b->is_aktif && ! $hasMovement && $stokAwal === 0 && $stokAkhir === 0) {
                continue;
            }

            // Filter opsi "Hanya yang bergerak"
            if (! empty($filters['hanya_bergerak']) && ! $hasMovement) {
                continue;
            }

            $perluKulakan = ($stokAkhir <= (int) $b->stok_minimum);

            // Filter opsi "Hanya perlu kulakan"
            if (! empty($filters['hanya_perlu_kulakan']) && ! $perluKulakan) {
                continue;
            }

            // Deteksi sinkronisasi dengan kolom barang.stok untuk periode berjalan/sampai hari ini
            $isSinkron = true;
            if ($periode->isBerjalan || $periode->tanggalAkhir->isToday() || $periode->tanggalAkhir->isFuture()) {
                $isSinkron = ($stokAkhir === (int) $b->stok);
            }

            $nilaiPersediaanBarang = max(0, $stokAkhir) * (int) $b->harga_modal;

            $rowItem = [
                'barang_id' => $b->id,
                'kode' => $b->kode,
                'nama' => $b->nama,
                'satuan' => $b->satuan,
                'kategori' => $b->kategori->nama ?? '-',
                'is_aktif' => (bool) $b->is_aktif,
                'stok_awal' => $stokAwal,
                'masuk' => $masuk,
                'terjual' => $terjual,
                'retur_void' => $returVoid,
                'penyesuaian' => $penyesuaian,
                'stok_akhir' => $stokAkhir,
                'stok_minimum' => (int) $b->stok_minimum,
                'perlu_kulakan' => $perluKulakan,
                'is_sinkron' => $isSinkron,
                'stok_sistem_saat_ini' => (int) $b->stok,
                'harga_modal_terkini' => (int) $b->harga_modal,
                'nilai_persediaan' => $nilaiPersediaanBarang,
            ];

            $rows[] = $rowItem;

            $totalUnitMasuk += $masuk;
            $totalUnitTerjual += $terjual;
            $totalNilaiPersediaan += $nilaiPersediaanBarang;

            if ($perluKulakan) {
                $kekurangan = max(0, (int) $b->stok_minimum - $stokAkhir);
                $daftarKulakan[] = [
                    'kode' => $b->kode,
                    'nama' => $b->nama,
                    'satuan' => $b->satuan,
                    'stok_akhir' => $stokAkhir,
                    'stok_minimum' => (int) $b->stok_minimum,
                    'kekurangan' => $kekurangan,
                ];
            }
        }

        return [
            'kepala' => [
                'nama_kios' => $kios->nama_kios ?? 'Stokify Kios',
                'alamat' => $kios->alamat ?? '-',
                'judul' => 'Laporan Mutasi dan Sisa Stok',
                'periode_label' => $periode->label,
                'waktu_cetak' => Carbon::now('Asia/Jayapura')->translatedFormat('d F Y, H:i') . ' WIT',
                'nama_pencetak' => $pencetak->name,
            ],
            'items' => $rows,
            'ringkasan' => [
                'jumlah_jenis_barang' => count($rows),
                'total_unit_masuk' => $totalUnitMasuk,
                'total_unit_terjual' => $totalUnitTerjual,
                'nilai_persediaan_akhir' => $totalNilaiPersediaan,
            ],
            'daftar_kulakan' => $daftarKulakan,
            'catatan_kaki' => [
                'persediaan' => 'Nilai persediaan akhir dihitung dengan harga modal terkini.',
                'masuk' => 'Kolom masuk mencakup transaksi barang masuk dan penambahan saldo awal sistem pada periode.',
            ],
        ];
    }
}
