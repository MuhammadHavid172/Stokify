<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\DetailBarangMasuk;
use App\Models\DetailPenjualan;
use App\Models\Kategori;
use App\Models\Kios;
use App\Models\LogAktivitas;
use App\Models\MutasiStok;
use App\Models\Penjualan;
use App\Models\PenyesuaianStok;
use App\Models\ShiftKasir;
use App\Models\Supplier;
use App\Models\User;
use App\Services\BarangMasukService;
use App\Services\LaporanService;
use App\Services\PenjualanService;
use App\Services\PenyesuaianStokService;
use App\Services\ShiftService;
use App\Services\StokService;
use App\ValueObjects\PeriodeLaporan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TahapSembilanTest extends TestCase
{
    use RefreshDatabase;

    protected Kios $kiosA;
    protected Kios $kiosB;
    protected User $adminA;
    protected User $kasirA;
    protected User $adminB;
    protected User $kasirB;
    protected Kategori $katA;
    protected Kategori $katB;
    protected Barang $barangA1;
    protected Barang $barangA2;
    protected Barang $barangB1;
    protected ShiftKasir $shiftA;
    protected ShiftKasir $shiftB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Kios A & Pengguna
        $this->kiosA = Kios::create([
            'nama_kios' => 'Kios Maju Makmur',
            'alamat' => 'Jl. Kios Usaha No. 10',
            'status' => 'aktif',
        ]);

        $this->adminA = User::create([
            'kios_id' => $this->kiosA->id,
            'name' => 'Admin Maju',
            'username' => 'admin_maju_9',
            'email' => 'admin_maju_9@kios.test',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        $this->kasirA = User::create([
            'kios_id' => $this->kiosA->id,
            'name' => 'Kasir Maju',
            'username' => 'kasir_maju_9',
            'email' => null,
            'password' => bcrypt('password123'),
            'role' => 'kasir',
            'is_aktif' => true,
        ]);

        // 2. Kios B & Pengguna
        $this->kiosB = Kios::create([
            'nama_kios' => 'Kios Sejahtera Berkah',
            'alamat' => 'Jl. Sentani No. 20',
            'status' => 'aktif',
        ]);

        $this->adminB = User::create([
            'kios_id' => $this->kiosB->id,
            'name' => 'Admin Sejahtera',
            'username' => 'admin_sej_9',
            'email' => 'admin_sej_9@kios.test',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        $this->kasirB = User::create([
            'kios_id' => $this->kiosB->id,
            'name' => 'Kasir Sejahtera',
            'username' => 'kasir_sej_9',
            'email' => null,
            'password' => bcrypt('password123'),
            'role' => 'kasir',
            'is_aktif' => true,
        ]);

        // 3. Kategori
        $this->katA = Kategori::create([
            'kios_id' => $this->kiosA->id,
            'nama' => 'Sembako A',
        ]);
        $this->katB = Kategori::create([
            'kios_id' => $this->kiosB->id,
            'nama' => 'Minuman B',
        ]);

        // 4. Barang Kios A
        $stokService = app(StokService::class);

        $this->barangA1 = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $this->katA->id,
            'kode' => 'BRG-A1',
            'nama' => 'Beras Ramos 5kg',
            'satuan' => 'karung',
            'harga_modal' => 50000,
            'harga_jual' => 60000,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_aktif' => true,
        ]);
        $stokService->catat($this->barangA1, 'stok_awal', 20, 'Stok awal sistem', null, Carbon::now('Asia/Jayapura')->subDays(10));

        $this->barangA2 = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $this->katA->id,
            'kode' => 'BRG-A2',
            'nama' => 'Minyak Goreng 1L',
            'satuan' => 'botol',
            'harga_modal' => 14000,
            'harga_jual' => 18000,
            'stok' => 0,
            'stok_minimum' => 10,
            'is_aktif' => true,
        ]);
        $stokService->catat($this->barangA2, 'stok_awal', 15, 'Stok awal sistem', null, Carbon::now('Asia/Jayapura')->subDays(10));

        // 5. Barang Kios B
        $this->barangB1 = Barang::create([
            'kios_id' => $this->kiosB->id,
            'kategori_id' => $this->katB->id,
            'kode' => 'BRG-B1',
            'nama' => 'Susu Kaleng B',
            'satuan' => 'kaleng',
            'harga_modal' => 10000,
            'harga_jual' => 13000,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_aktif' => true,
        ]);
        $stokService->catat($this->barangB1, 'stok_awal', 20, 'Stok awal sistem B', null, Carbon::now('Asia/Jayapura')->subDays(10));

        // 6. Shift Kasir Berjalan
        $shiftService = app(ShiftService::class);
        $this->shiftA = $shiftService->buka($this->kasirA, 100000, Carbon::now('Asia/Jayapura')->startOfDay()->addHours(8));
        $this->shiftB = $shiftService->buka($this->kasirB, 100000, Carbon::now('Asia/Jayapura')->startOfDay()->addHours(8));
    }

    /**
     * Test 1: Laporan keuangan: omzet, HPP, laba kotor, margin, dan jumlah transaksi benar untuk harian, mingguan, bulanan, dan kustom
     */
    public function test_01_laporan_keuangan_angka_benar_untuk_semua_jenis_periode(): void
    {
        $penjualanService = app(PenjualanService::class);
        $waktuTrx = Carbon::now('Asia/Jayapura');

        // Transaksi 1: 1 Beras (Jual: 60.000, Modal: 50.000, Laba: 10.000)
        $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-t9-1',
            user: $this->kasirA,
            waktuTransaksi: $waktuTrx
        );

        // Transaksi 2: 2 Minyak (Jual: 36.000, Modal: 28.000, Laba: 8.000)
        $penjualanService->buat(
            items: [['barang_id' => $this->barangA2->id, 'jumlah' => 2]],
            bayar: 50000,
            token: 'tok-t9-2',
            user: $this->kasirA,
            waktuTransaksi: $waktuTrx
        );

        // Total seharusnya: omzet = 96.000, hpp = 78.000, laba = 18.000, margin = 18.8%, transaksi = 2, rata-rata = 48.000
        $service = app(LaporanService::class);

        // A. Harian
        $reqHarian = Request::create(route('admin.laporan.keuangan'), 'GET', [
            'jenis' => 'harian',
            'tanggal_harian' => $waktuTrx->toDateString(),
        ]);
        $pHarian = PeriodeLaporan::fromRequest($reqHarian, 366);
        $dataHarian = $service->keuangan($pHarian, $this->kiosA->id, $this->adminA);

        $this->assertEquals(2, $dataHarian['ringkasan']['jumlah_transaksi']);
        $this->assertEquals(96000, $dataHarian['ringkasan']['omzet']);
        $this->assertEquals(78000, $dataHarian['ringkasan']['hpp']);
        $this->assertEquals(18000, $dataHarian['ringkasan']['laba_kotor']);
        $this->assertEquals(48000, $dataHarian['ringkasan']['rata_rata_transaksi']);
        $this->assertEquals('18.8%', $dataHarian['ringkasan']['margin_laba_kotor']);

        // B. Mingguan
        $reqMingguan = Request::create(route('admin.laporan.keuangan'), 'GET', [
            'jenis' => 'mingguan',
            'tanggal_mingguan' => $waktuTrx->toDateString(),
        ]);
        $pMingguan = PeriodeLaporan::fromRequest($reqMingguan, 366);
        $dataMingguan = $service->keuangan($pMingguan, $this->kiosA->id, $this->adminA);
        $this->assertEquals(96000, $dataMingguan['ringkasan']['omzet']);

        // C. Bulanan
        $reqBulanan = Request::create(route('admin.laporan.keuangan'), 'GET', [
            'jenis' => 'bulanan',
            'bulan' => $waktuTrx->month,
            'tahun' => $waktuTrx->year,
        ]);
        $pBulanan = PeriodeLaporan::fromRequest($reqBulanan, 366);
        $dataBulanan = $service->keuangan($pBulanan, $this->kiosA->id, $this->adminA);
        $this->assertEquals(96000, $dataBulanan['ringkasan']['omzet']);

        // D. Kustom
        $reqKustom = Request::create(route('admin.laporan.keuangan'), 'GET', [
            'jenis' => 'kustom',
            'tanggal_kustom_mulai' => $waktuTrx->copy()->subDays(2)->toDateString(),
            'tanggal_kustom_akhir' => $waktuTrx->toDateString(),
        ]);
        $pKustom = PeriodeLaporan::fromRequest($reqKustom, 366);
        $dataKustom = $service->keuangan($pKustom, $this->kiosA->id, $this->adminA);
        $this->assertEquals(96000, $dataKustom['ringkasan']['omzet']);
    }

    /**
     * Test 2: Nota dibatalkan tidak masuk omzet, HPP, maupun laba, tetapi tercatat di informasi pembatalan
     */
    public function test_02_nota_dibatalkan_tidak_masuk_omzet_hpp_maupun_laba_tetapi_tercatat_di_info_pembatalan(): void
    {
        $penjualanService = app(PenjualanService::class);
        $now = Carbon::now('Asia/Jayapura');

        // Nota Selesai (60.000)
        $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-selesai-2',
            user: $this->kasirA,
            waktuTransaksi: $now
        );

        // Nota Dibatalkan (18.000)
        $pBatal = $penjualanService->buat(
            items: [['barang_id' => $this->barangA2->id, 'jumlah' => 1]],
            bayar: 50000,
            token: 'tok-batal-2',
            user: $this->kasirA,
            waktuTransaksi: $now
        );
        $penjualanService->batalkan($pBatal, 'Pelanggan membatalkan pesanan', $this->kasirA);

        $service = app(LaporanService::class);
        $req = Request::create(route('admin.laporan.keuangan'), 'GET', [
            'jenis' => 'harian',
            'tanggal_harian' => $now->toDateString(),
        ]);
        $periode = PeriodeLaporan::fromRequest($req);
        $data = $service->keuangan($periode, $this->kiosA->id, $this->adminA);

        $this->assertEquals(1, $data['ringkasan']['jumlah_transaksi']);
        $this->assertEquals(60000, $data['ringkasan']['omzet']);
        $this->assertEquals(50000, $data['ringkasan']['hpp']);
        $this->assertEquals(10000, $data['ringkasan']['laba_kotor']);

        // Tercatat di informasi pembatalan
        $this->assertEquals(1, $data['ringkasan']['jumlah_nota_batal']);
        $this->assertEquals(18000, $data['ringkasan']['nilai_nota_batal']);
    }

    /**
     * Test 3: Laporan memakai snapshot: ubah harga_jual dan harga_modal barang setelah transaksi, angka laporan lama tidak berubah
     */
    public function test_03_laporan_memakai_snapshot_harga_barang_diubah_angka_laporan_lama_tidak_berubah(): void
    {
        $penjualanService = app(PenjualanService::class);
        $now = Carbon::now('Asia/Jayapura');

        $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]], // Jual: 60.000, Modal: 50.000
            bayar: 100000,
            token: 'tok-snap-3',
            user: $this->kasirA,
            waktuTransaksi: $now
        );

        // Ubah harga jual dan modal barang di database master barang
        $this->barangA1->update([
            'harga_jual' => 120000,
            'harga_modal' => 95000,
        ]);

        $service = app(LaporanService::class);
        $req = Request::create(route('admin.laporan.keuangan'), 'GET', [
            'jenis' => 'harian',
            'tanggal_harian' => $now->toDateString(),
        ]);
        $periode = PeriodeLaporan::fromRequest($req);
        $data = $service->keuangan($periode, $this->kiosA->id, $this->adminA);

        // Angka laporan lama harus tetap 60.000 dan 50.000 (tidak mengikuti harga baru)
        $this->assertEquals(60000, $data['ringkasan']['omzet']);
        $this->assertEquals(50000, $data['ringkasan']['hpp']);
        $this->assertEquals(10000, $data['ringkasan']['laba_kotor']);
    }

    /**
     * Test 4: Batas periode benar pada zona waktu Asia/Jayapura (transaksi 00:30 dan 23:45 masuk ke hari yang benar; transaksi di luar periode tidak ikut)
     */
    public function test_04_batas_periode_benar_pada_zona_waktu_asia_jayapura(): void
    {
        $penjualanService = app(PenjualanService::class);
        $targetDate = Carbon::parse('2026-11-10', 'Asia/Jayapura');

        // Buka shift khusus untuk transaksi tanggal target
        $shiftKhusus = ShiftKasir::create([
            'kios_id' => $this->kiosA->id,
            'user_id' => $this->kasirA->id,
            'waktu_buka' => $targetDate->copy()->startOfDay(),
            'modal_awal' => 50000,
            'status' => 'berjalan',
            'user_aktif' => null, // agar tidak bentrok
        ]);

        // Trx A: Pukul 00:30:00 WIT pada target date
        $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-jam-0030',
            user: $this->kasirA,
            waktuTransaksi: $targetDate->copy()->setHour(0)->setMinute(30)->setSecond(0)
        );

        // Trx B: Pukul 23:45:00 WIT pada target date
        $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-jam-2345',
            user: $this->kasirA,
            waktuTransaksi: $targetDate->copy()->setHour(23)->setMinute(45)->setSecond(0)
        );

        // Trx C: 1 menit sebelum target date (2026-11-09 23:59:00 WIT)
        $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-jam-sebelum',
            user: $this->kasirA,
            waktuTransaksi: $targetDate->copy()->subDay()->setHour(23)->setMinute(59)->setSecond(0)
        );

        // Trx D: 1 menit sesudah target date (2026-11-11 00:01:00 WIT)
        $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-jam-sesudah',
            user: $this->kasirA,
            waktuTransaksi: $targetDate->copy()->addDay()->setHour(0)->setMinute(1)->setSecond(0)
        );

        $service = app(LaporanService::class);
        $req = Request::create(route('admin.laporan.keuangan'), 'GET', [
            'jenis' => 'harian',
            'tanggal_harian' => '2026-11-10',
        ]);
        $periode = PeriodeLaporan::fromRequest($req);
        $data = $service->keuangan($periode, $this->kiosA->id, $this->adminA);

        // Hanya Trx A dan Trx B yang masuk (2 transaksi = 120.000 omzet)
        $this->assertEquals(2, $data['ringkasan']['jumlah_transaksi']);
        $this->assertEquals(120000, $data['ringkasan']['omzet']);
    }

    /**
     * Test 5: Rincian per hari menjumlahkan sama persis dengan ringkasan total
     */
    public function test_05_rincian_per_hari_menjumlahkan_sama_persis_dengan_ringkasan_total(): void
    {
        $penjualanService = app(PenjualanService::class);
        $today = Carbon::today('Asia/Jayapura');

        // Transaksi hari ini
        $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-rinc-1',
            user: $this->kasirA,
            waktuTransaksi: $today->copy()->setHour(10)
        );

        // Transaksi kemarin
        $penjualanService->buat(
            items: [['barang_id' => $this->barangA2->id, 'jumlah' => 2]],
            bayar: 50000,
            token: 'tok-rinc-2',
            user: $this->kasirA,
            waktuTransaksi: $today->copy()->subDay()->setHour(14)
        );

        $service = app(LaporanService::class);
        $req = Request::create(route('admin.laporan.keuangan'), 'GET', [
            'jenis' => 'kustom',
            'tanggal_kustom_mulai' => $today->copy()->subDays(3)->toDateString(),
            'tanggal_kustom_akhir' => $today->toDateString(),
        ]);
        $periode = PeriodeLaporan::fromRequest($req);
        $data = $service->keuangan($periode, $this->kiosA->id, $this->adminA);

        $sumTrx = collect($data['rincian_harian'])->sum('jumlah_transaksi');
        $sumOmzet = collect($data['rincian_harian'])->sum('omzet');
        $sumHpp = collect($data['rincian_harian'])->sum('hpp');
        $sumLaba = collect($data['rincian_harian'])->sum('laba_kotor');

        $this->assertEquals($data['ringkasan']['jumlah_transaksi'], $sumTrx);
        $this->assertEquals($data['ringkasan']['omzet'], $sumOmzet);
        $this->assertEquals($data['ringkasan']['hpp'], $sumHpp);
        $this->assertEquals($data['ringkasan']['laba_kotor'], $sumLaba);

        $this->assertEquals($data['total_rincian']['jumlah_transaksi'], $sumTrx);
        $this->assertEquals($data['total_rincian']['omzet'], $sumOmzet);
    }

    /**
     * Test 6: Kerugian stok dan total kulakan periode benar; laba kotor setelah kerugian stok benar
     */
    public function test_06_kerugian_stok_dan_total_kulakan_serta_laba_setelah_kerugian_benar(): void
    {
        $penjualanService = app(PenjualanService::class);
        $today = Carbon::today('Asia/Jayapura');

        // 1. Penjualan: Omzet 60.000, HPP 50.000 -> Laba Kotor 10.000
        $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-kulakan-test',
            user: $this->kasirA,
            waktuTransaksi: $today
        );

        // 2. Kulakan Barang Masuk: 5 Beras @ 48.000 = 240.000
        $supplier = Supplier::create([
            'kios_id' => $this->kiosA->id,
            'nama' => 'Supplier Test',
            'is_aktif' => true,
        ]);
        $bm = BarangMasuk::create([
            'kios_id' => $this->kiosA->id,
            'supplier_id' => $supplier->id,
            'user_id' => $this->adminA->id,
            'nomor' => 'BM-TEST-99',
            'tanggal' => $today->toDateString(),
            'total' => 240000,
            'status' => 'selesai',
        ]);

        // 3. Penyesuaian Stok Rusak: Kerugian 14.000
        $penyesuaian = PenyesuaianStok::create([
            'kios_id' => $this->kiosA->id,
            'user_id' => $this->adminA->id,
            'nomor' => 'ADJ-TEST-99',
            'tanggal' => $today->toDateString(),
            'mode' => 'manual',
            'total_kerugian' => 14000,
        ]);

        $service = app(LaporanService::class);
        $req = Request::create(route('admin.laporan.keuangan'), 'GET', [
            'jenis' => 'harian',
            'tanggal_harian' => $today->toDateString(),
        ]);
        $periode = PeriodeLaporan::fromRequest($req);
        $data = $service->keuangan($periode, $this->kiosA->id, $this->adminA);

        $this->assertEquals(240000, $data['ringkasan']['total_kulakan']);
        $this->assertEquals(14000, $data['ringkasan']['kerugian_stok']);
        $this->assertEquals(10000, $data['ringkasan']['laba_kotor']);
        // Laba kotor setelah kerugian = 10.000 - 14.000 = -4.000
        $this->assertEquals(-4000, $data['ringkasan']['laba_kotor_setelah_kerugian_stok']);
    }

    /**
     * Test 7: Laporan audit: rekap shift menampilkan selisih benar, shift berjalan berlabel sementara, tutup paksa ditandai
     */
    public function test_07_laporan_audit_rekap_shift_dan_tutup_paksa_ditandai(): void
    {
        $shiftService = app(ShiftService::class);
        $today = Carbon::today('Asia/Jayapura');

        // Shift 1: Sudah ditutup dengan selisih kurang Rp 5.000
        $s1 = ShiftKasir::create([
            'kios_id' => $this->kiosA->id,
            'user_id' => $this->kasirA->id,
            'waktu_buka' => $today->copy()->setHour(7),
            'waktu_tutup' => $today->copy()->setHour(12),
            'modal_awal' => 100000,
            'total_penjualan_tunai' => 50000,
            'uang_seharusnya' => 150000,
            'uang_fisik' => 145000,
            'selisih' => -5000,
            'status' => 'ditutup',
            'ditutup_paksa' => false,
            'catatan' => 'Kurang 5rb receh',
        ]);

        // Shift 2: Ditutup paksa oleh admin
        $s2 = ShiftKasir::create([
            'kios_id' => $this->kiosA->id,
            'user_id' => $this->kasirA->id,
            'waktu_buka' => $today->copy()->setHour(13),
            'waktu_tutup' => $today->copy()->setHour(16),
            'modal_awal' => 100000,
            'total_penjualan_tunai' => 20000,
            'uang_seharusnya' => 120000,
            'uang_fisik' => 120000,
            'selisih' => 0,
            'status' => 'ditutup',
            'ditutup_paksa' => true,
            'ditutup_oleh' => $this->adminA->id,
            'catatan' => 'Kasir sakit mendadak',
        ]);

        $service = app(LaporanService::class);
        $req = Request::create(route('admin.laporan.audit'), 'GET', [
            'jenis' => 'harian',
            'tanggal_harian' => $today->toDateString(),
        ]);
        $periode = PeriodeLaporan::fromRequest($req, 92);
        $data = $service->audit($periode, $this->kiosA->id, $this->adminA);

        $shiftIds = collect($data['rekap_shift'])->pluck('id');
        $this->assertTrue($shiftIds->contains($s1->id));
        $this->assertTrue($shiftIds->contains($s2->id));
        $this->assertTrue($shiftIds->contains($this->shiftA->id));

        $s1Item = collect($data['rekap_shift'])->firstWhere('id', $s1->id);
        $this->assertEquals(-5000, $s1Item['selisih']);

        $s2Item = collect($data['rekap_shift'])->firstWhere('id', $s2->id);
        $this->assertTrue($s2Item['ditutup_paksa']);
        $this->assertEquals('Admin Maju', $s2Item['ditutup_oleh']);

        $shiftAktifItem = collect($data['rekap_shift'])->firstWhere('id', $this->shiftA->id);
        $this->assertEquals('berjalan', $shiftAktifItem['status']);
        $this->assertNull($shiftAktifItem['uang_fisik']);
        $this->assertNull($shiftAktifItem['selisih']);
    }

    /**
     * Test 8: Daftar void menampilkan pelaku dan alasan
     */
    public function test_08_daftar_void_menampilkan_pelaku_dan_alasan(): void
    {
        $penjualanService = app(PenjualanService::class);
        $today = Carbon::today('Asia/Jayapura');

        $p = $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-audit-void',
            user: $this->kasirA,
            waktuTransaksi: $today
        );
        $penjualanService->batalkan($p, 'Pelanggan salah memilih varian beras', $this->kasirA);

        $service = app(LaporanService::class);
        $req = Request::create(route('admin.laporan.audit'), 'GET', [
            'jenis' => 'harian',
            'tanggal_harian' => $today->toDateString(),
        ]);
        $periode = PeriodeLaporan::fromRequest($req, 92);
        $data = $service->audit($periode, $this->kiosA->id, $this->adminA);

        $this->assertCount(1, $data['daftar_void']);
        $voidItem = $data['daftar_void'][0];
        $this->assertEquals($p->nomor, $voidItem['nomor']);
        $this->assertEquals('Kasir Maju', $voidItem['pembatal']);
        $this->assertEquals('Pelanggan salah memilih varian beras', $voidItem['alasan']);
    }

    /**
     * Test 9: Laporan stok: stok_awal + masuk - terjual + retur + penyesuaian = stok_akhir untuk setiap barang
     */
    public function test_09_laporan_stok_formula_rekonsiliasi_stok_pada_berbagai_skenario(): void
    {
        $stokService = app(StokService::class);
        $penjualanService = app(PenjualanService::class);
        $today = Carbon::today('Asia/Jayapura');

        // Barang A1: Saldo awal sebelum periode adalah 20.
        // Dalam periode hari ini:
        // 1. Barang masuk: +10
        $stokService->catat($this->barangA1, 'barang_masuk', 10, 'Kulakan test', null, $today->copy()->setHour(9));
        // 2. Terjual: -3
        $p = $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 3]],
            bayar: 200000,
            token: 'tok-stok-formula-1',
            user: $this->kasirA,
            waktuTransaksi: $today->copy()->setHour(10)
        );
        // 3. Void nota: +1 (buat nota 1 lalu batalkan)
        $pVoid = $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-stok-formula-void',
            user: $this->kasirA,
            waktuTransaksi: $today->copy()->setHour(11)
        );
        $penjualanService->batalkan($pVoid, 'Void untuk uji formula', $this->kasirA);
        // 4. Penyesuaian stok: -2
        $stokService->catat($this->barangA1, 'penyesuaian', -2, 'Barang rusak kemasan bocor', null, $today->copy()->setHour(12));

        // Stok akhir seharusnya: 20 (awal) + 10 (masuk) - 4 (terjual total) + 1 (retur void) - 2 (penyesuaian) = 25.
        // Note: penjualan total = 3 + 1 = 4 terjual, retur void = 1. Bersih terjual = 3.
        // Formula: awal (20) + masuk (10) - terjual (4) + retur (1) + penyesuaian (-2) = 25.

        $service = app(LaporanService::class);
        $req = Request::create(route('admin.laporan.stok'), 'GET', [
            'jenis' => 'harian',
            'tanggal_harian' => $today->toDateString(),
        ]);
        $periode = PeriodeLaporan::fromRequest($req, 92);
        $data = $service->stok($periode, $this->kiosA->id, $this->adminA);

        $itemA1 = collect($data['items'])->firstWhere('barang_id', $this->barangA1->id);
        $this->assertNotNull($itemA1);

        $this->assertEquals(20, $itemA1['stok_awal']);
        $this->assertEquals(10, $itemA1['masuk']);
        $this->assertEquals(4, $itemA1['terjual']);
        $this->assertEquals(1, $itemA1['retur_void']);
        $this->assertEquals(-2, $itemA1['penyesuaian']);
        $this->assertEquals(25, $itemA1['stok_akhir']);

        // Verifikasi kesamaan persis formula matematis
        $recalculated = $itemA1['stok_awal'] + $itemA1['masuk'] - $itemA1['terjual'] + $itemA1['retur_void'] + $itemA1['penyesuaian'];
        $this->assertEquals($itemA1['stok_akhir'], $recalculated);
    }

    /**
     * Test 10: Stok akhir periode sampai hari ini sama dengan kolom barang.stok; ketidaksinkronan memunculkan penanda
     */
    public function test_10_stok_akhir_periode_sampai_hari_ini_sama_dengan_kolom_barang_stok_dan_deteksi_tidak_sinkron(): void
    {
        $today = Carbon::today('Asia/Jayapura');
        $service = app(LaporanService::class);
        $req = Request::create(route('admin.laporan.stok'), 'GET', [
            'jenis' => 'harian',
            'tanggal_harian' => $today->toDateString(),
        ]);
        $periode = PeriodeLaporan::fromRequest($req, 92);

        // Kondisi 1: Normal sinkron
        $dataNormal = $service->stok($periode, $this->kiosA->id, $this->adminA);
        $itemA1 = collect($dataNormal['items'])->firstWhere('barang_id', $this->barangA1->id);
        $this->assertTrue($itemA1['is_sinkron']);

        // Kondisi 2: Manipulasi kolom barang.stok secara manual tanpa mutasi (anomali)
        DB::table('barang')->where('id', $this->barangA1->id)->update(['stok' => 999]);

        $dataAnomali = $service->stok($periode, $this->kiosA->id, $this->adminA);
        $itemA1Anomali = collect($dataAnomali['items'])->firstWhere('barang_id', $this->barangA1->id);
        $this->assertFalse($itemA1Anomali['is_sinkron']);
    }

    /**
     * Test 11: Nilai persediaan akhir dan daftar belanja kulakan benar
     */
    public function test_11_nilai_persediaan_akhir_dan_daftar_belanja_kulakan_benar(): void
    {
        // Barang A1: stok 20, minimum 5 -> tidak perlu kulakan
        // Barang A2: stok 15, minimum 10 -> kurangi stok A2 jadi 4 agar di bawah minimum (10)
        $stokService = app(StokService::class);
        $stokService->catat($this->barangA2, 'penyesuaian', -11, 'Selisih uji kulakan'); // sisa 4

        $service = app(LaporanService::class);
        $today = Carbon::today('Asia/Jayapura');
        $req = Request::create(route('admin.laporan.stok'), 'GET', [
            'jenis' => 'harian',
            'tanggal_harian' => $today->toDateString(),
        ]);
        $periode = PeriodeLaporan::fromRequest($req, 92);
        $data = $service->stok($periode, $this->kiosA->id, $this->adminA);

        // Daftar belanja kulakan harus berisi Barang A2
        $kulakanCodes = collect($data['daftar_kulakan'])->pluck('kode');
        $this->assertTrue($kulakanCodes->contains('BRG-A2'));
        $this->assertFalse($kulakanCodes->contains('BRG-A1'));

        $kulakanA2 = collect($data['daftar_kulakan'])->firstWhere('kode', 'BRG-A2');
        $this->assertEquals(4, $kulakanA2['stok_akhir']);
        $this->assertEquals(10, $kulakanA2['stok_minimum']);
        $this->assertEquals(6, $kulakanA2['kekurangan']); // 10 - 4 = 6
    }

    /**
     * Test 12: Unduhan PDF mengembalikan status 200, header application/pdf, dan nama file sesuai format
     */
    public function test_12_unduhan_pdf_mengembalikan_status_200_header_pdf_dan_nama_file_format(): void
    {
        $this->actingAs($this->adminA);

        // 1. PDF Keuangan
        $resKeuangan = $this->get(route('admin.laporan.keuangan.pdf', ['jenis' => 'harian']));
        $resKeuangan->assertOk();
        $this->assertEquals('application/pdf', $resKeuangan->headers->get('content-type'));
        $this->assertStringContainsString('Laporan-Keuangan_kios-maju-makmur_', $resKeuangan->headers->get('content-disposition'));

        // 2. PDF Audit
        $resAudit = $this->get(route('admin.laporan.audit.pdf', ['jenis' => 'harian']));
        $resAudit->assertOk();
        $this->assertEquals('application/pdf', $resAudit->headers->get('content-type'));
        $this->assertStringContainsString('Laporan-Audit_kios-maju-makmur_', $resAudit->headers->get('content-disposition'));

        // 3. PDF Stok
        $resStok = $this->get(route('admin.laporan.stok.pdf', ['jenis' => 'harian']));
        $resStok->assertOk();
        $this->assertEquals('application/pdf', $resStok->headers->get('content-type'));
        $this->assertStringContainsString('Laporan-Stok_kios-maju-makmur_', $resStok->headers->get('content-disposition'));
    }

    /**
     * Test 13: Parameter periode tidak valid ditolak dengan pesan validasi
     */
    public function test_13_parameter_periode_tidak_valid_ditolak_dengan_pesan_validasi(): void
    {
        $this->actingAs($this->adminA);

        // A. Tanggal masa depan (besok)
        $besok = Carbon::today('Asia/Jayapura')->addDay()->toDateString();
        $resFuture = $this->get(route('admin.laporan.keuangan', [
            'jenis' => 'harian',
            'tanggal_harian' => $besok,
        ]));
        $resFuture->assertSessionHasErrors('tanggal_harian');

        // B. Kustom: tanggal akhir < tanggal mulai
        $resInverted = $this->get(route('admin.laporan.keuangan', [
            'jenis' => 'kustom',
            'tanggal_kustom_mulai' => '2026-11-10',
            'tanggal_kustom_akhir' => '2026-11-05',
        ]));
        $resInverted->assertSessionHasErrors('tanggal_kustom_akhir');

        // C. Rentang melebihi batas (Audit > 92 hari)
        $resExcessive = $this->get(route('admin.laporan.audit', [
            'jenis' => 'kustom',
            'tanggal_kustom_mulai' => '2026-01-01',
            'tanggal_kustom_akhir' => '2026-06-01', // ~150 hari
        ]));
        $resExcessive->assertSessionHasErrors('rentang');
    }

    /**
     * Test 14: Admin kios A tidak pernah melihat data kios B dan filter kategori kios lain ditolak
     */
    public function test_14_admin_kios_a_tidak_pernah_melihat_data_kios_b_dan_filter_kategori_kios_lain_ditolak(): void
    {
        $penjualanService = app(PenjualanService::class);
        $today = Carbon::today('Asia/Jayapura');

        // Transaksi Kios A: 60.000
        $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-iso-a',
            user: $this->kasirA,
            waktuTransaksi: $today
        );

        // Transaksi Kios B: 26.000 (2 Susu)
        $penjualanService->buat(
            items: [['barang_id' => $this->barangB1->id, 'jumlah' => 2]],
            bayar: 50000,
            token: 'tok-iso-b',
            user: $this->kasirB,
            waktuTransaksi: $today
        );

        $this->actingAs($this->adminA);

        // Admin A membuka laporan keuangan
        $resA = $this->get(route('admin.laporan.keuangan', [
            'jenis' => 'harian',
            'tanggal_harian' => $today->toDateString(),
        ]));
        $resA->assertOk();
        $resA->assertSee('Rp 60.000');
        $resA->assertDontSee('Rp 26.000');
        $resA->assertDontSee($this->barangB1->nama);

        // Admin A mencoba memfilter dengan kategori milik Kios B -> ditolak validasi
        $resHackingCategory = $this->get(route('admin.laporan.stok', [
            'jenis' => 'harian',
            'kategori_id' => $this->katB->id,
        ]));
        $resHackingCategory->assertSessionHasErrors('kategori_id');
    }

    /**
     * Test 15: Kasir mendapat 403 pada seluruh route laporan
     */
    public function test_15_kasir_mendapat_403_pada_seluruh_route_laporan(): void
    {
        $this->actingAs($this->kasirA);

        $routes = [
            route('admin.laporan.index'),
            route('admin.laporan.keuangan'),
            route('admin.laporan.keuangan.pdf'),
            route('admin.laporan.audit'),
            route('admin.laporan.audit.pdf'),
            route('admin.laporan.stok'),
            route('admin.laporan.stok.pdf'),
        ];

        foreach ($routes as $url) {
            $response = $this->get($url);
            $response->assertForbidden();
        }
    }

    /**
     * Test 16: Log aktivitas laporan.cetak tercatat saat PDF diunduh
     */
    public function test_16_log_aktivitas_laporan_cetak_tercatat_saat_pdf_diunduh(): void
    {
        $this->actingAs($this->adminA);

        $this->get(route('admin.laporan.keuangan.pdf', ['jenis' => 'harian']));

        $log = LogAktivitas::where('aksi', 'laporan.cetak')
            ->where('kios_id', $this->kiosA->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('Laporan Ringkasan Keuangan', $log->deskripsi);
    }

    /**
     * Test 17: Tidak ada query N+1 pada laporan audit dan stok
     */
    public function test_17_tidak_ada_query_n_plus_satu_pada_laporan_audit_dan_stok(): void
    {
        // Buat beberapa barang tambahan di Kios A
        $stokService = app(StokService::class);
        for ($i = 3; $i <= 8; $i++) {
            $b = Barang::create([
                'kios_id' => $this->kiosA->id,
                'kategori_id' => $this->katA->id,
                'kode' => "BRG-A{$i}",
                'nama' => "Barang Test {$i}",
                'satuan' => 'pcs',
                'harga_modal' => 5000,
                'harga_jual' => 7000,
                'stok' => 0,
                'stok_minimum' => 2,
                'is_aktif' => true,
            ]);
            $stokService->catat($b, 'stok_awal', 10, 'Awal');
        }

        $service = app(LaporanService::class);
        $today = Carbon::today('Asia/Jayapura');
        $req = Request::create(route('admin.laporan.stok'), 'GET', [
            'jenis' => 'harian',
            'tanggal_harian' => $today->toDateString(),
        ]);
        $periode = PeriodeLaporan::fromRequest($req, 92);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $service->stok($periode, $this->kiosA->id, $this->adminA);

        $queries = DB::getQueryLog();
        // Jumlah query untuk stok harus konstan (~3-4 query teragregasi) dan tidak N+1 per barang
        $this->assertLessThanOrEqual(5, count($queries), 'Query laporan stok terdeteksi N+1');

        // Uji juga laporan audit
        DB::flushQueryLog();
        $service->audit($periode, $this->kiosA->id, $this->adminA);
        $queriesAudit = DB::getQueryLog();
        $this->assertLessThanOrEqual(5, count($queriesAudit), 'Query laporan audit terdeteksi N+1');
    }

    /**
     * Test 18: Tidak ada kata "laba bersih" pada seluruh output laporan
     */
    public function test_18_tidak_ada_kata_laba_bersih_pada_seluruh_output_laporan(): void
    {
        $this->actingAs($this->adminA);

        $resKeuangan = $this->get(route('admin.laporan.keuangan'));
        $resKeuangan->assertDontSee('laba bersih', false);
        $resKeuangan->assertDontSee('Laba Bersih', false);

        $resAudit = $this->get(route('admin.laporan.audit'));
        $resAudit->assertDontSee('laba bersih', false);
        $resAudit->assertDontSee('Laba Bersih', false);

        $resStok = $this->get(route('admin.laporan.stok'));
        $resStok->assertDontSee('laba bersih', false);
        $resStok->assertDontSee('Laba Bersih', false);
    }
}
