<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\DetailPenjualan;
use App\Models\Kategori;
use App\Models\Kios;
use App\Models\LogAktivitas;
use App\Models\MutasiStok;
use App\Models\Penjualan;
use App\Models\ShiftKasir;
use App\Models\User;
use App\Services\AnalisisBarangService;
use App\Services\PenjualanService;
use App\Services\ShiftService;
use App\Services\StokService;
use App\Services\VoidService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TahapSepuluhTest extends TestCase
{
    use RefreshDatabase;

    protected Kios $kiosA;
    protected Kios $kiosB;
    protected User $adminA;
    protected User $kasirA;
    protected User $adminB;
    protected User $kasirB;
    protected Kategori $katA1;
    protected Kategori $katA2;
    protected Kategori $katB1;
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
            'nama_kios' => 'Kios Barokah Maju',
            'alamat' => 'Jl. Abepura No. 12, Jayapura',
            'status' => 'aktif',
            'lead_time_hari' => 2,
            'hari_pengaman' => 3,
            'cakupan_kulakan_hari' => 7,
            'batas_stok_berlebih_hari' => 60,
            'margin_tipis_persen' => 10,
        ]);

        $this->adminA = User::create([
            'kios_id' => $this->kiosA->id,
            'name' => 'Admin Barokah',
            'username' => 'admin_barokah',
            'email' => 'admin_barokah@kios.test',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        $this->kasirA = User::create([
            'kios_id' => $this->kiosA->id,
            'name' => 'Kasir Barokah',
            'username' => 'kasir_barokah',
            'email' => null,
            'password' => bcrypt('password123'),
            'role' => 'kasir',
            'is_aktif' => true,
        ]);

        // 2. Kios B & Pengguna
        $this->kiosB = Kios::create([
            'nama_kios' => 'Kios Sentosa Sejahtera',
            'alamat' => 'Jl. Sentani No. 5, Jayapura',
            'status' => 'aktif',
            'lead_time_hari' => 2,
            'hari_pengaman' => 3,
            'cakupan_kulakan_hari' => 7,
            'batas_stok_berlebih_hari' => 60,
            'margin_tipis_persen' => 10,
        ]);

        $this->adminB = User::create([
            'kios_id' => $this->kiosB->id,
            'name' => 'Admin Sentosa',
            'username' => 'admin_sentosa',
            'email' => 'admin_sentosa@kios.test',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        $this->kasirB = User::create([
            'kios_id' => $this->kiosB->id,
            'name' => 'Kasir Sentosa',
            'username' => 'kasir_sentosa',
            'email' => null,
            'password' => bcrypt('password123'),
            'role' => 'kasir',
            'is_aktif' => true,
        ]);

        // 3. Kategori
        $this->katA1 = Kategori::create([
            'kios_id' => $this->kiosA->id,
            'nama' => 'Sembako',
        ]);
        $this->katA2 = Kategori::create([
            'kios_id' => $this->kiosA->id,
            'nama' => 'Minuman Kemasan',
        ]);
        $this->katB1 = Kategori::create([
            'kios_id' => $this->kiosB->id,
            'nama' => 'Elektronik Kios B',
        ]);

        // 4. Barang A1 & A2 (dibuat 30 hari lalu agar data_cukup = true)
        $stokService = app(StokService::class);

        $this->barangA1 = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $this->katA1->id,
            'kode' => 'BRG-A01',
            'nama' => 'Beras Medium 5kg',
            'satuan' => 'karung',
            'harga_modal' => 50000,
            'harga_jual' => 60000,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_aktif' => true,
            'created_at' => Carbon::now('Asia/Jayapura')->subDays(30),
            'updated_at' => Carbon::now('Asia/Jayapura')->subDays(30),
        ]);
        $stokService->catat($this->barangA1, 'stok_awal', 20, 'Stok awal A1', null, Carbon::now('Asia/Jayapura')->subDays(30));

        $this->barangA2 = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $this->katA2->id,
            'kode' => 'BRG-A02',
            'nama' => 'Teh Kotak 200ml',
            'satuan' => 'kotak',
            'harga_modal' => 3000,
            'harga_jual' => 4500,
            'stok' => 0,
            'stok_minimum' => 10,
            'is_aktif' => true,
            'created_at' => Carbon::now('Asia/Jayapura')->subDays(30),
            'updated_at' => Carbon::now('Asia/Jayapura')->subDays(30),
        ]);
        $stokService->catat($this->barangA2, 'stok_awal', 25, 'Stok awal A2', null, Carbon::now('Asia/Jayapura')->subDays(30));

        // 5. Barang B1 (Kios B)
        $this->barangB1 = Barang::create([
            'kios_id' => $this->kiosB->id,
            'kategori_id' => $this->katB1->id,
            'kode' => 'BRG-B01',
            'nama' => 'Kopi Sachet B',
            'satuan' => 'sachet',
            'harga_modal' => 1000,
            'harga_jual' => 1500,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_aktif' => true,
            'created_at' => Carbon::now('Asia/Jayapura')->subDays(30),
            'updated_at' => Carbon::now('Asia/Jayapura')->subDays(30),
        ]);
        $stokService->catat($this->barangB1, 'stok_awal', 30, 'Stok awal B1', null, Carbon::now('Asia/Jayapura')->subDays(30));

        // 6. Buka Shift untuk Kasir A & Kasir B
        $shiftService = app(ShiftService::class);
        $this->shiftA = $shiftService->buka($this->kasirA, 50000, Carbon::now('Asia/Jayapura')->startOfDay()->addHours(8));
        $this->shiftB = $shiftService->buka($this->kasirB, 50000, Carbon::now('Asia/Jayapura')->startOfDay()->addHours(8));
    }

    /**
     * Test 1: terjual_netto dan frekuensi benar, nota void diabaikan
     */
    public function test_01_terjual_netto_dan_frekuensi_benar_nota_void_diabaikan(): void
    {
        $penjualanService = app(PenjualanService::class);
        $voidService = app(VoidService::class);

        // Nota 1 (selesai): 2 Beras
        $p1 = $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 2]],
            bayar: 150000,
            token: 'test-trx-netto-1',
            user: $this->kasirA
        );

        // Nota 2 (selesai): 3 Beras, 1 Teh
        $p2 = $penjualanService->buat(
            items: [
                ['barang_id' => $this->barangA1->id, 'jumlah' => 3],
                ['barang_id' => $this->barangA2->id, 'jumlah' => 1],
            ],
            bayar: 200000,
            token: 'test-trx-netto-2',
            user: $this->kasirA
        );

        // Nota 3 (dibatalkan / void): 5 Beras, 4 Teh
        $p3 = $penjualanService->buat(
            items: [
                ['barang_id' => $this->barangA1->id, 'jumlah' => 5],
                ['barang_id' => $this->barangA2->id, 'jumlah' => 4],
            ],
            bayar: 350000,
            token: 'test-trx-netto-3',
            user: $this->kasirA
        );
        $voidService->batalkan($p3, 'Salah input item kasir', $this->kasirA);

        // Transaksi Kios B: 10 Kopi
        $penjualanService->buat(
            items: [['barang_id' => $this->barangB1->id, 'jumlah' => 10]],
            bayar: 20000,
            token: 'test-trx-netto-b1',
            user: $this->kasirB
        );

        // Analisis Kios A periode 30 hari
        $service = app(AnalisisBarangService::class);
        $items = $service->hitung(30, $this->kiosA->id);

        $rowA1 = $items->firstWhere('barang_id', $this->barangA1->id);
        $rowA2 = $items->firstWhere('barang_id', $this->barangA2->id);
        $rowB1 = $items->firstWhere('barang_id', $this->barangB1->id);

        // Barang B1 tidak boleh ada di hasil Kios A
        $this->assertNull($rowB1, 'Barang Kios B bocor ke analisis Kios A');

        // Barang A1: terjual_netto = 2 + 3 = 5 (void 5 diabaikan), frekuensi = 2 nota selesai
        $this->assertNotNull($rowA1);
        $this->assertEquals(5, $rowA1['terjual_netto']);
        $this->assertEquals(2, $rowA1['frekuensi']);

        // Barang A2: terjual_netto = 1, frekuensi = 1 nota selesai
        $this->assertNotNull($rowA2);
        $this->assertEquals(1, $rowA2['terjual_netto']);
        $this->assertEquals(1, $rowA2['frekuensi']);
    }

    /**
     * Test 2: Klasifikasi Pareto benar pada dataset manual (<80% Fast, <95% Normal, >=95% Slow)
     */
    public function test_02_klasifikasi_pareto_benar_pada_dataset_manual(): void
    {
        $penjualanService = app(PenjualanService::class);
        $stokService = app(StokService::class);

        // Buat 4 barang baru dengan stok dan umur cukup
        $b1 = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $this->katA1->id,
            'kode' => 'BRG-P1',
            'nama' => 'Barang P1',
            'satuan' => 'pcs',
            'harga_modal' => 1000,
            'harga_jual' => 2000,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_aktif' => true,
            'created_at' => Carbon::now('Asia/Jayapura')->subDays(20),
            'updated_at' => Carbon::now('Asia/Jayapura')->subDays(20),
        ]);
        $stokService->catat($b1, 'stok_awal', 200, 'Stok awal');

        $b2 = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $this->katA1->id,
            'kode' => 'BRG-P2',
            'nama' => 'Barang P2',
            'satuan' => 'pcs',
            'harga_modal' => 1000,
            'harga_jual' => 2000,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_aktif' => true,
            'created_at' => Carbon::now('Asia/Jayapura')->subDays(20),
            'updated_at' => Carbon::now('Asia/Jayapura')->subDays(20),
        ]);
        $stokService->catat($b2, 'stok_awal', 200, 'Stok awal');

        $b3 = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $this->katA1->id,
            'kode' => 'BRG-P3',
            'nama' => 'Barang P3',
            'satuan' => 'pcs',
            'harga_modal' => 1000,
            'harga_jual' => 2000,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_aktif' => true,
            'created_at' => Carbon::now('Asia/Jayapura')->subDays(20),
            'updated_at' => Carbon::now('Asia/Jayapura')->subDays(20),
        ]);
        $stokService->catat($b3, 'stok_awal', 200, 'Stok awal');

        $b4 = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $this->katA1->id,
            'kode' => 'BRG-P4',
            'nama' => 'Barang P4',
            'satuan' => 'pcs',
            'harga_modal' => 1000,
            'harga_jual' => 2000,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_aktif' => true,
            'created_at' => Carbon::now('Asia/Jayapura')->subDays(20),
            'updated_at' => Carbon::now('Asia/Jayapura')->subDays(20),
        ]);
        $stokService->catat($b4, 'stok_awal', 200, 'Stok awal');

        // Nonaktifkan barang setup lain agar dataset bersih
        Barang::where('kios_id', $this->kiosA->id)->whereNotIn('id', [$b1->id, $b2->id, $b3->id, $b4->id])->update(['is_aktif' => false]);

        // Simulasikan transaksi:
        // Frekuensi:
        // b1 = 50 nota
        // b2 = 30 nota
        // b3 = 15 nota
        // b4 = 5 nota
        // Total frekuensi = 100 nota
        // Kumulatif persen sebelum:
        // b1: 0% -> Fast
        // b2: 50% -> Fast
        // b3: 80% -> Normal (< 95%)
        // b4: 95% -> Slow (>= 95%)

        for ($i = 1; $i <= 50; $i++) {
            $penjualanService->buat([['barang_id' => $b1->id, 'jumlah' => 1]], 5000, "pareto-b1-{$i}", $this->kasirA);
        }
        for ($i = 1; $i <= 30; $i++) {
            $penjualanService->buat([['barang_id' => $b2->id, 'jumlah' => 1]], 5000, "pareto-b2-{$i}", $this->kasirA);
        }
        for ($i = 1; $i <= 15; $i++) {
            $penjualanService->buat([['barang_id' => $b3->id, 'jumlah' => 1]], 5000, "pareto-b3-{$i}", $this->kasirA);
        }
        for ($i = 1; $i <= 5; $i++) {
            $penjualanService->buat([['barang_id' => $b4->id, 'jumlah' => 1]], 5000, "pareto-b4-{$i}", $this->kasirA);
        }

        $service = app(AnalisisBarangService::class);
        $items = $service->hitung(30, $this->kiosA->id);

        $row1 = $items->firstWhere('barang_id', $b1->id);
        $row2 = $items->firstWhere('barang_id', $b2->id);
        $row3 = $items->firstWhere('barang_id', $b3->id);
        $row4 = $items->firstWhere('barang_id', $b4->id);

        $this->assertEquals('fast', $row1['kelas']);
        $this->assertEquals('Fast Moving', $row1['kelas_label']);

        $this->assertEquals('fast', $row2['kelas']);
        $this->assertEquals('Fast Moving', $row2['kelas_label']);

        $this->assertEquals('normal', $row3['kelas']);
        $this->assertEquals('Normal', $row3['kelas_label']);

        $this->assertEquals('slow', $row4['kelas']);
        $this->assertEquals('Slow Moving', $row4['kelas_label']);
    }

    /**
     * Test 3: Barang frekuensi nol dengan stok positif masuk tidak_laku, stok nol masuk tidak_ada_aktivitas
     */
    public function test_03_barang_frekuensi_nol_stok_positif_tidak_laku_stok_nol_diabaikan(): void
    {
        $stokService = app(StokService::class);

        // Barang dengan stok > 0 dan tanpa penjualan sama sekali
        $bTidakLaku = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $this->katA1->id,
            'kode' => 'BRG-TL',
            'nama' => 'Barang Tidak Laku',
            'satuan' => 'pcs',
            'harga_modal' => 10000,
            'harga_jual' => 15000,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_aktif' => true,
            'created_at' => Carbon::now('Asia/Jayapura')->subDays(20),
            'updated_at' => Carbon::now('Asia/Jayapura')->subDays(20),
        ]);
        $stokService->catat($bTidakLaku, 'stok_awal', 15, 'Stok awal');

        // Barang dengan stok = 0 dan tanpa penjualan
        $bKosong = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $this->katA1->id,
            'kode' => 'BRG-KOSONG',
            'nama' => 'Barang Habis Tanpa Transaksi',
            'satuan' => 'pcs',
            'harga_modal' => 5000,
            'harga_jual' => 7000,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_aktif' => true,
            'created_at' => Carbon::now('Asia/Jayapura')->subDays(20),
            'updated_at' => Carbon::now('Asia/Jayapura')->subDays(20),
        ]);

        $service = app(AnalisisBarangService::class);
        $items = $service->hitung(30, $this->kiosA->id);

        $rowTL = $items->firstWhere('barang_id', $bTidakLaku->id);
        $rowKosong = $items->firstWhere('barang_id', $bKosong->id);

        $this->assertEquals('tidak_laku', $rowTL['kelas']);
        $this->assertEquals('Tidak Laku', $rowTL['kelas_label']);
        $this->assertEquals(150000, $rowTL['modal_tertahan']); // 15 x 10000

        $this->assertEquals('tidak_ada_aktivitas', $rowKosong['kelas']);
        $this->assertEquals('Tidak Ada Aktivitas', $rowKosong['kelas_label']);

        // Uji ringkasan
        $ringkasan = $service->ringkasan($items, $this->kiosA);
        $this->assertGreaterThanOrEqual(1, $ringkasan['jumlah_tidak_laku']);
        $this->assertGreaterThanOrEqual(150000, $ringkasan['total_modal_tertahan_masalah']);
    }

    /**
     * Test 4: Barang hari efektif kurang dari tujuh berlabel data belum cukup
     */
    public function test_04_barang_hari_efektif_kurang_dari_tujuh_berlabel_data_belum_cukup(): void
    {
        $penjualanService = app(PenjualanService::class);
        $stokService = app(StokService::class);

        // Barang baru dibuat 3 hari lalu
        $bBaru = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $this->katA1->id,
            'kode' => 'BRG-BARU',
            'nama' => 'Produk Baru Rilis',
            'satuan' => 'pcs',
            'harga_modal' => 5000,
            'harga_jual' => 8000,
            'stok' => 0,
            'stok_minimum' => 10,
            'is_aktif' => true,
            'created_at' => Carbon::now('Asia/Jayapura')->subDays(3),
            'updated_at' => Carbon::now('Asia/Jayapura')->subDays(3),
        ]);
        $stokService->catat($bBaru, 'stok_awal', 4, 'Stok awal');

        // Transaksi penjualan untuk barang baru
        $penjualanService->buat([['barang_id' => $bBaru->id, 'jumlah' => 1]], 10000, 'baru-trx-1', $this->kasirA);

        $service = app(AnalisisBarangService::class);
        $items = $service->hitung(30, $this->kiosA->id);

        $rowBaru = $items->firstWhere('barang_id', $bBaru->id);

        $this->assertFalse($rowBaru['data_cukup']);
        $this->assertEquals('belum_cukup', $rowBaru['kelas']);
        $this->assertEquals('Data Belum Cukup', $rowBaru['kelas_label']);
        $this->assertLessThan(7, $rowBaru['hari_efektif']);

        // Rekomendasi kulakan berpatokan pada target stok minimum:
        // stok_sekarang = 4 - 1 = 3, stok_min = 10, target = 10 * 2 = 20.
        // rekomendasi = 20 - 3 = 17
        $this->assertEquals(17, $rowBaru['jumlah_rekomendasi']);
    }

    /**
     * Test 5: Rumus rekomendasi kulakan benar untuk berbagai skenario
     */
    public function test_05_rumus_rekomendasi_benar_untuk_berbagai_skenario(): void
    {
        // Parameter kios: lead_time = 2, hari_pengaman = 3, cakupan_kulakan = 7.
        // Lead buffer = 2 + 3 = 5 hari.
        // Target buffer = 2 + 3 + 7 = 12 hari.
        $stokService = app(StokService::class);
        $penjualanService = app(PenjualanService::class);

        // Barang Fast: Rata-rata 2/hari over 30 hari (terjual 60)
        $bFast = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $this->katA1->id,
            'kode' => 'BRG-ROP-1',
            'nama' => 'Barang ROP 1',
            'satuan' => 'pcs',
            'harga_modal' => 1000,
            'harga_jual' => 2000,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_aktif' => true,
            'created_at' => Carbon::now('Asia/Jayapura')->subDays(30),
            'updated_at' => Carbon::now('Asia/Jayapura')->subDays(30),
        ]);
        $stokService->catat($bFast, 'stok_awal', 100, 'Stok awal');

        // 30 nota x 2 pcs = 60 pcs terjual
        for ($i = 1; $i <= 30; $i++) {
            $penjualanService->buat([['barang_id' => $bFast->id, 'jumlah' => 2]], 10000, "rop-fast-{$i}", $this->kasirA);
        }

        // Sisa stok saat ini = 100 - 60 = 40.
        // Titik Pesan = max(2.0 * 5, 5) = 10.
        // Target Stok = max(2.0 * 12, 5) = 24.
        // Karena stok 40 > 10, rekomendasi harus 0 (stok cukup).
        $service = app(AnalisisBarangService::class);
        $items = $service->hitung(30, $this->kiosA->id);
        $row1 = $items->firstWhere('barang_id', $bFast->id);

        $this->assertEquals(10.0, $row1['titik_pesan']);
        $this->assertEquals(24.0, $row1['target_stok']);
        $this->assertEquals(0, $row1['jumlah_rekomendasi']);
        $this->assertEquals('stok_cukup', $row1['status_rekomendasi']);

        // Sekarang kita kurangi stok menjadi 6 (stok <= titik_pesan 10)
        // Rekomendasi = ceil(24 - 6) = 18.
        $bFast->update(['stok' => 6]);

        $itemsAfter = $service->hitung(30, $this->kiosA->id);
        $row1After = $itemsAfter->firstWhere('barang_id', $bFast->id);

        $this->assertEquals(18, $row1After['jumlah_rekomendasi']);
        $this->assertEquals('kulakan_banyak', $row1After['status_rekomendasi']);
    }

    /**
     * Test 6: Barang slow atau tidak laku tidak pernah punya rekomendasi positif (Tahan Kulakan)
     */
    public function test_06_barang_slow_atau_tidak_laku_tidak_pernah_punya_rekomendasi_positif(): void
    {
        $stokService = app(StokService::class);

        // Barang Tidak Laku tapi stok = 0 dan stok_min = 10
        $bTidakLaku = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $this->katA1->id,
            'kode' => 'BRG-TL-TAHAN',
            'nama' => 'Barang TL Tahan Restok',
            'satuan' => 'pcs',
            'harga_modal' => 2000,
            'harga_jual' => 3000,
            'stok' => 0,
            'stok_minimum' => 10,
            'is_aktif' => true,
            'created_at' => Carbon::now('Asia/Jayapura')->subDays(20),
            'updated_at' => Carbon::now('Asia/Jayapura')->subDays(20),
        ]);
        $stokService->catat($bTidakLaku, 'stok_awal', 2, 'Stok awal'); // stok 2 <= min 10

        $service = app(AnalisisBarangService::class);
        $items = $service->hitung(30, $this->kiosA->id);
        $row = $items->firstWhere('barang_id', $bTidakLaku->id);

        $this->assertEquals('tidak_laku', $row['kelas']);
        $this->assertEquals(0, $row['jumlah_rekomendasi'], 'Rekomendasi barang tidak laku harus 0');
        $this->assertEquals('tahan_kulakan', $row['status_rekomendasi']);
        $this->assertEquals('Tahan Restok', $row['status_rekomendasi_label']);
    }

    /**
     * Test 7: Penanda margin tipis dan stok berlebih benar
     */
    public function test_07_penanda_margin_tipis_dan_stok_berlebih_benar(): void
    {
        $penjualanService = app(PenjualanService::class);
        $stokService = app(StokService::class);

        // 1. Barang Margin Tipis: Modal 9.500, Jual 10.000 -> Margin 5% (< 10%)
        $bTipis = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $this->katA1->id,
            'kode' => 'BRG-TIPIS',
            'nama' => 'Minyak Curah Murah',
            'satuan' => 'liter',
            'harga_modal' => 9500,
            'harga_jual' => 10000,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_aktif' => true,
            'created_at' => Carbon::now('Asia/Jayapura')->subDays(20),
            'updated_at' => Carbon::now('Asia/Jayapura')->subDays(20),
        ]);
        $stokService->catat($bTipis, 'stok_awal', 10, 'Stok awal');

        // 2. Barang Stok Berlebih: Terjual 1/hari (30 pcs), stok 100 -> cakupan 100 hari (> 60 hari)
        $bBerlebih = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $this->katA1->id,
            'kode' => 'BRG-LEBIH',
            'nama' => 'Garam Stok Menumpuk',
            'satuan' => 'bungkus',
            'harga_modal' => 2000,
            'harga_jual' => 3000,
            'stok' => 0,
            'stok_minimum' => 10,
            'is_aktif' => true,
            'created_at' => Carbon::now('Asia/Jayapura')->subDays(30),
            'updated_at' => Carbon::now('Asia/Jayapura')->subDays(30),
        ]);
        $stokService->catat($bBerlebih, 'stok_awal', 130, 'Stok awal');
        for ($i = 1; $i <= 30; $i++) {
            $penjualanService->buat([['barang_id' => $bBerlebih->id, 'jumlah' => 1]], 5000, "lebih-trx-{$i}", $this->kasirA);
        }

        $service = app(AnalisisBarangService::class);
        $items = $service->hitung(30, $this->kiosA->id);

        $rowTipis = $items->firstWhere('barang_id', $bTipis->id);
        $rowBerlebih = $items->firstWhere('barang_id', $bBerlebih->id);

        $this->assertTrue($rowTipis['is_margin_tipis']);
        $this->assertEquals(5.0, $rowTipis['margin_persen']);

        $this->assertTrue($rowBerlebih['is_stok_berlebih']);
        $this->assertEquals(100.0, $rowBerlebih['cakupan_hari']);
        $this->assertEquals('tahan_kulakan', $rowBerlebih['status_rekomendasi']);
    }

    /**
     * Test 8: Perubahan pengaturan mengubah hasil dan tercatat log
     */
    public function test_08_perubahan_pengaturan_mengubah_hasil_dan_tercatat_log(): void
    {
        $this->actingAs($this->adminA);

        // Update parameter kulakan kios
        $res = $this->put(route('admin.analisis.pengaturan.update'), [
            'lead_time_hari' => 4,
            'hari_pengaman' => 4,
            'cakupan_kulakan_hari' => 14,
            'batas_stok_berlebih_hari' => 90,
            'margin_tipis_persen' => 15,
        ]);

        $res->assertRedirect(route('admin.analisis.pengaturan'));
        $res->assertSessionHas('success');

        $this->kiosA->refresh();
        $this->assertEquals(4, $this->kiosA->lead_time_hari);
        $this->assertEquals(4, $this->kiosA->hari_pengaman);
        $this->assertEquals(14, $this->kiosA->cakupan_kulakan_hari);
        $this->assertEquals(90, $this->kiosA->batas_stok_berlebih_hari);
        $this->assertEquals(15, $this->kiosA->margin_tipis_persen);

        // Verifikasi log aktivitas
        $log = LogAktivitas::where('kios_id', $this->kiosA->id)
            ->where('aksi', 'analisis.ubah_pengaturan')
            ->latest('id')
            ->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Memperbarui parameter model restok', $log->deskripsi);

        // Uji validasi input batas
        $resInvalid = $this->put(route('admin.analisis.pengaturan.update'), [
            'lead_time_hari' => 35, // max 30
            'hari_pengaman' => -1,  // min 0
            'cakupan_kulakan_hari' => 0, // min 1
            'batas_stok_berlebih_hari' => 5, // min 14
            'margin_tipis_persen' => 105, // max 100
        ]);
        $resInvalid->assertSessionHasErrors(['lead_time_hari', 'hari_pengaman', 'cakupan_kulakan_hari', 'batas_stok_berlebih_hari', 'margin_tipis_persen']);

        // Uji Reset ke Bawaan
        $resReset = $this->post(route('admin.analisis.pengaturan.reset'));
        $resReset->assertRedirect(route('admin.analisis.pengaturan'));

        $this->kiosA->refresh();
        $this->assertEquals(2, $this->kiosA->lead_time_hari);
        $this->assertEquals(3, $this->kiosA->hari_pengaman);
        $this->assertEquals(7, $this->kiosA->cakupan_kulakan_hari);
        $this->assertEquals(60, $this->kiosA->batas_stok_berlebih_hari);
        $this->assertEquals(10, $this->kiosA->margin_tipis_persen);
    }

    /**
     * Test 9: Analisis memakai snapshot detail penjualan, harga barang berubah tidak mempengaruhi omzet/laba historis
     */
    public function test_09_analisis_memakai_snapshot_detail_penjualan(): void
    {
        $penjualanService = app(PenjualanService::class);

        // Jual 1 Beras (Modal: 50.000, Jual: 60.000)
        $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'snapshot-test-token',
            user: $this->kasirA
        );

        // Ubah harga barang saat ini
        $this->barangA1->update([
            'harga_modal' => 80000,
            'harga_jual' => 95000,
        ]);

        $service = app(AnalisisBarangService::class);
        $items = $service->hitung(30, $this->kiosA->id);
        $row = $items->firstWhere('barang_id', $this->barangA1->id);

        // Omzet dan Laba Kotor tetap dari transaksi masa lalu
        $this->assertEquals(60000, $row['omzet']);
        $this->assertEquals(10000, $row['laba_kotor']); // 60.000 - 50.000
    }

    /**
     * Test 10: Periode memakai batas benar Asia/Jayapura dan nilai selain 7, 14, 30, 60, 90 ditolak
     */
    public function test_10_periode_memakai_batas_benar_asia_jayapura_dan_nilai_lain_ditolak(): void
    {
        $this->actingAs($this->adminA);

        // Nilai periode tidak valid (misal 15, 45, huruf)
        $res15 = $this->get(route('admin.analisis.index', ['periode' => 15]));
        $res15->assertSessionHasErrors('periode');

        $resAbc = $this->get(route('admin.analisis.index', ['periode' => 'abc']));
        $resAbc->assertSessionHasErrors('periode');

        // Nilai periode valid
        foreach ([7, 14, 30, 60, 90] as $hari) {
            $res = $this->get(route('admin.analisis.index', ['periode' => $hari]));
            $res->assertOk();
        }
    }

    /**
     * Test 11: Fitur prefill barang masuk dihitung ulang server tanpa nota otomatis
     */
    public function test_11_fitur_prefill_barang_masuk_dihitung_ulang_server_tanpa_nota_otomatis(): void
    {
        $this->actingAs($this->adminA);
        $initialBmCount = BarangMasuk::count();

        // Kirim POST prefill untuk barang A1
        $res = $this->post(route('admin.analisis.prefill'), [
            'barang_ids' => [$this->barangA1->id],
            'periode' => 30,
        ]);

        $res->assertRedirect(route('admin.barang-masuk.create'));
        $res->assertSessionHas('prefill_barang_masuk');

        // Tidak ada nota barang masuk otomatis yang tersimpan di DB
        $this->assertEquals($initialBmCount, BarangMasuk::count());

        $sessionData = session('prefill_barang_masuk');
        $this->assertCount(1, $sessionData);
        $this->assertEquals($this->barangA1->id, $sessionData[0]['barang_id']);

        // Uji jika mengirimkan ID milik Kios B (ditolak validasi)
        $resB = $this->post(route('admin.analisis.prefill'), [
            'barang_ids' => [$this->barangB1->id],
        ]);
        $resB->assertSessionHasErrors('barang_ids.0');

        // Uji jika array kosong
        $resEmpty = $this->post(route('admin.analisis.prefill'), [
            'barang_ids' => [],
        ]);
        $resEmpty->assertSessionHasErrors('barang_ids');
    }

    /**
     * Test 12: Unduhan PDF daftar kulakan berhasil dan tercatat log
     */
    public function test_12_unduhan_pdf_daftar_kulakan_berhasil_dan_tercatat_log(): void
    {
        $this->actingAs($this->adminA);

        // Pastikan ada barang yang butuh kulakan
        $this->barangA1->update(['stok' => 0, 'stok_minimum' => 10]);

        $res = $this->get(route('admin.analisis.pdf', ['periode' => 30]));
        $res->assertOk();
        $this->assertStringContainsString('application/pdf', $res->headers->get('content-type'));
        $this->assertStringContainsString('Daftar-Belanja-Stok', $res->headers->get('content-disposition'));

        // Cek log aktivitas
        $log = LogAktivitas::where('kios_id', $this->kiosA->id)
            ->where('aksi', 'analisis.cetak')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('Mengunduh PDF Daftar Belanja Stok', $log->deskripsi);
    }

    /**
     * Test 13: Admin Kios A tidak pernah melihat data Kios B dan filter kategori Kios lain ditolak
     */
    public function test_13_admin_kios_a_tidak_pernah_melihat_data_kios_b_dan_filter_kategori_kios_lain_ditolak(): void
    {
        $penjualanService = app(PenjualanService::class);
        $penjualanService->buat([['barang_id' => $this->barangA1->id, 'jumlah' => 1]], 100000, 'iso-a-trx', $this->kasirA);
        $penjualanService->buat([['barang_id' => $this->barangB1->id, 'jumlah' => 1]], 100000, 'iso-b-trx', $this->kasirB);

        $this->actingAs($this->adminA);

        // Index Tab 1
        $resIndex = $this->get(route('admin.analisis.index'));
        $resIndex->assertOk();
        $resIndex->assertSee($this->barangA1->nama);
        $resIndex->assertDontSee($this->barangB1->nama);

        // Rekomendasi Tab 2
        $resRek = $this->get(route('admin.analisis.rekomendasi'));
        $resRek->assertOk();
        $resRek->assertSee($this->barangA1->nama);
        $resRek->assertDontSee($this->barangB1->nama);

        // Filter dengan kategori_id milik Kios B ditolak
        $resFilterInvalid = $this->get(route('admin.analisis.rekomendasi', [
            'kategori_id' => $this->katB1->id,
        ]));
        $resFilterInvalid->assertSessionHasErrors('kategori_id');
    }

    /**
     * Test 14: Kasir mendapat 403 pada seluruh route analisis
     */
    public function test_14_kasir_mendapat_403_pada_seluruh_route_analisis(): void
    {
        $this->actingAs($this->kasirA);

        $this->get(route('admin.analisis.index'))->assertForbidden();
        $this->get(route('admin.analisis.rekomendasi'))->assertForbidden();
        $this->get(route('admin.analisis.pdf'))->assertForbidden();
        $this->post(route('admin.analisis.prefill'), ['barang_ids' => [$this->barangA1->id]])->assertForbidden();
        $this->get(route('admin.analisis.pengaturan'))->assertForbidden();
        $this->put(route('admin.analisis.pengaturan.update'), ['lead_time_hari' => 2])->assertForbidden();
        $this->post(route('admin.analisis.pengaturan.reset'))->assertForbidden();
    }

    /**
     * Test 15: Dashboard panel terlaris dan kartu perlu dikulak benar dan hanya kios sendiri
     */
    public function test_15_dashboard_panel_terlaris_dan_kartu_perlu_dikulak_benar_dan_hanya_kios_sendiri(): void
    {
        $penjualanService = app(PenjualanService::class);

        // Transaksi Kios A: Beras A1 terjual 3
        $penjualanService->buat([['barang_id' => $this->barangA1->id, 'jumlah' => 3]], 200000, 'dash-trx-a', $this->kasirA);

        // Transaksi Kios B: Kopi B1 terjual 15
        $penjualanService->buat([['barang_id' => $this->barangB1->id, 'jumlah' => 15]], 50000, 'dash-trx-b', $this->kasirB);

        $this->actingAs($this->adminA);
        $res = $this->get(route('admin.dashboard'));
        $res->assertOk();

        // Menampilkan kartu perlu restok
        $res->assertSee('Perlu Restok (30 Hari)');
        $res->assertSee(route('admin.analisis.rekomendasi'));

        // Menampilkan barang terlaris Kios A
        $res->assertSee('Barang Terlaris 30 Hari Terakhir');
        $res->assertSee($this->barangA1->nama);
        $res->assertDontSee($this->barangB1->nama);
    }

    /**
     * Test 16: Tidak ada query N+1 pada analisis barang
     */
    public function test_16_tidak_ada_query_n_plus_satu_pada_analisis_barang(): void
    {
        $stokService = app(StokService::class);

        // Buat 20 barang tambahan di Kios A
        for ($i = 1; $i <= 20; $i++) {
            $b = Barang::create([
                'kios_id' => $this->kiosA->id,
                'kategori_id' => $this->katA1->id,
                'kode' => "BRG-NPLUS-{$i}",
                'nama' => "Barang Skala {$i}",
                'satuan' => 'pcs',
                'harga_modal' => 1000,
                'harga_jual' => 2000,
                'stok' => 0,
                'stok_minimum' => 5,
                'is_aktif' => true,
                'created_at' => Carbon::now('Asia/Jayapura')->subDays(20),
                'updated_at' => Carbon::now('Asia/Jayapura')->subDays(20),
            ]);
            $stokService->catat($b, 'stok_awal', 10, 'Stok awal');
        }

        $service = app(AnalisisBarangService::class);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $service->hitung(30, $this->kiosA->id);

        $queries = DB::getQueryLog();
        // Query harus konstan (~3-4 query teragregasi) dan tidak bertambah sesuai jumlah barang
        $this->assertLessThanOrEqual(6, count($queries), 'Query analisis barang terdeteksi N+1');
    }

    /**
     * Test 17: Tidak ada route update atau destroy pada barang, stok, dan transaksi
     */
    public function test_17_tidak_ada_route_update_atau_destroy_pada_barang_stok_transaksi(): void
    {
        // Transaksi penjualan tidak boleh punya route update atau destroy
        $this->assertFalse(Route::has('admin.penjualan.update'));
        $this->assertFalse(Route::has('admin.penjualan.destroy'));

        // Mutasi stok tidak boleh punya route update atau destroy
        $this->assertFalse(Route::has('admin.mutasi-stok.update'));
        $this->assertFalse(Route::has('admin.mutasi-stok.destroy'));

        // Shift kasir tidak boleh punya route update atau destroy
        $this->assertFalse(Route::has('admin.shift.update'));
        $this->assertFalse(Route::has('admin.shift.destroy'));

        // Barang masuk tidak boleh punya route update atau destroy
        $this->assertFalse(Route::has('admin.barang-masuk.update'));
        $this->assertFalse(Route::has('admin.barang-masuk.destroy'));

        // Penyesuaian stok tidak boleh punya route update atau destroy
        $this->assertFalse(Route::has('admin.penyesuaian-stok.update'));
        $this->assertFalse(Route::has('admin.penyesuaian-stok.destroy'));
    }
}
