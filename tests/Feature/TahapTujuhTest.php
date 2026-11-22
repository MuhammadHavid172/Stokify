<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\DetailPenjualan;
use App\Models\Kategori;
use App\Models\Kios;
use App\Models\MutasiStok;
use App\Models\Penjualan;
use App\Models\User;
use App\Services\PenjualanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TahapTujuhTest extends TestCase
{
    use RefreshDatabase;

    protected Kios $kiosA;
    protected Kios $kiosB;
    protected User $adminA;
    protected User $kasirA;
    protected User $kasirA2;
    protected User $adminB;
    protected User $kasirB;
    protected Barang $barangA1;
    protected Barang $barangA2;
    protected Barang $barangAOff;
    protected Barang $barangB1;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Kios A & Pengguna
        $this->kiosA = Kios::create([
            'nama_kios' => 'Kios Maju',
            'status' => 'aktif',
        ]);

        $this->adminA = User::create([
            'kios_id' => $this->kiosA->id,
            'name' => 'Admin Maju',
            'username' => 'admin_maju',
            'email' => 'admin_maju@kios.test',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        $this->kasirA = User::create([
            'kios_id' => $this->kiosA->id,
            'name' => 'Kasir Maju',
            'username' => 'kasir_maju',
            'email' => null,
            'password' => bcrypt('password123'),
            'role' => 'kasir',
            'is_aktif' => true,
        ]);

        $this->kasirA2 = User::create([
            'kios_id' => $this->kiosA->id,
            'name' => 'Kasir Dua Maju',
            'username' => 'kasir_dua',
            'email' => null,
            'password' => bcrypt('password123'),
            'role' => 'kasir',
            'is_aktif' => true,
        ]);

        // 2. Kios B & Pengguna
        $this->kiosB = Kios::create([
            'nama_kios' => 'Kios Sejahtera',
            'status' => 'aktif',
        ]);

        $this->adminB = User::create([
            'kios_id' => $this->kiosB->id,
            'name' => 'Admin Sejahtera',
            'username' => 'admin_sejahtera',
            'email' => 'admin_sejahtera@kios.test',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        $this->kasirB = User::create([
            'kios_id' => $this->kiosB->id,
            'name' => 'Kasir Sejahtera',
            'username' => 'kasir_sejahtera',
            'email' => null,
            'password' => bcrypt('password123'),
            'role' => 'kasir',
            'is_aktif' => true,
        ]);

        // 3. Kategori & Barang Kios A
        $katA = Kategori::create([
            'kios_id' => $this->kiosA->id,
            'nama' => 'Sembako',
        ]);

        $this->barangA1 = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $katA->id,
            'kode' => 'BRG-A001',
            'nama' => 'Beras 5kg',
            'satuan' => 'karung',
            'harga_modal' => 50000,
            'harga_jual' => 60000,
            'stok' => 15,
            'stok_minimum' => 2,
            'is_aktif' => true,
        ]);

        $this->barangA2 = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $katA->id,
            'kode' => 'BRG-A002',
            'nama' => 'Minyak Goreng 1L',
            'satuan' => 'botol',
            'harga_modal' => 14000,
            'harga_jual' => 17000,
            'stok' => 20,
            'stok_minimum' => 5,
            'is_aktif' => true,
        ]);

        $this->barangAOff = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $katA->id,
            'kode' => 'BRG-AOFF',
            'nama' => 'Barang Nonaktif A',
            'satuan' => 'pcs',
            'harga_modal' => 5000,
            'harga_jual' => 8000,
            'stok' => 10,
            'stok_minimum' => 1,
            'is_aktif' => false,
        ]);

        // 4. Kategori & Barang Kios B
        $katB = Kategori::create([
            'kios_id' => $this->kiosB->id,
            'nama' => 'Minuman',
        ]);

        $this->barangB1 = Barang::create([
            'kios_id' => $this->kiosB->id,
            'kategori_id' => $katB->id,
            'kode' => 'BRG-B001',
            'nama' => 'Susu Kental Manis',
            'satuan' => 'kaleng',
            'harga_modal' => 11000,
            'harga_jual' => 14000,
            'stok' => 25,
            'stok_minimum' => 5,
            'is_aktif' => true,
        ]);

        // Buat shift berjalan untuk testing Tahap 7 agar kompatibel dengan validasi shift Tahap 8
        foreach ([$this->kasirA, $this->adminA, $this->kasirA2, $this->kasirB, $this->adminB] as $u) {
            \App\Models\ShiftKasir::create([
                'kios_id' => $u->kios_id,
                'user_id' => $u->id,
                'user_aktif' => $u->id,
                'waktu_buka' => Carbon::now('Asia/Jayapura')->subHours(4),
                'modal_awal' => 100000,
                'status' => 'berjalan',
            ]);
        }
    }

    /**
     * Test 1: Penjualan tersimpan: stok berkurang, mutasi_stok bertipe penjualan dengan jumlah negatif terbentuk, total, bayar, kembalian benar
     */
    public function test_01_penjualan_tersimpan_dan_stok_serta_mutasi_berkurang_benar(): void
    {
        $this->actingAs($this->kasirA);

        $payload = [
            'token' => 'token-test-01',
            'bayar' => 200000,
            'items' => [
                ['barang_id' => $this->barangA1->id, 'jumlah' => 3], // 3 x 60.000 = 180.000
            ],
        ];

        $response = $this->postJson(route('kasir.penjualan.store'), $payload);

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Header penjualan
        $penjualan = Penjualan::withoutGlobalScopes()->where('token', 'token-test-01')->first();
        $this->assertNotNull($penjualan);
        $this->assertEquals(180000, $penjualan->total);
        $this->assertEquals(200000, $penjualan->bayar);
        $this->assertEquals(20000, $penjualan->kembalian);
        $this->assertEquals(150000, $penjualan->total_modal); // 3 x 50.000
        $this->assertEquals($this->kasirA->id, $penjualan->user_id);
        $this->assertEquals($this->kiosA->id, $penjualan->kios_id);

        // Stok berkurang
        $this->assertEquals(12, $this->barangA1->fresh()->stok);

        // Mutasi stok bertipe penjualan negatif
        $mutasi = MutasiStok::withoutGlobalScopes()
            ->where('barang_id', $this->barangA1->id)
            ->where('tipe', 'penjualan')
            ->first();

        $this->assertNotNull($mutasi);
        $this->assertEquals(-3, $mutasi->jumlah);
        $this->assertEquals(15, $mutasi->stok_sebelum);
        $this->assertEquals(12, $mutasi->stok_sesudah);
        $this->assertEquals($penjualan->id, $mutasi->referensi_id);
    }

    /**
     * Test 2: Harga jual dan modal diambil dari database; harga atau total dari input client diabaikan
     */
    public function test_02_harga_jual_dan_modal_diambil_dari_database_input_client_diabaikan(): void
    {
        $this->actingAs($this->kasirA);

        // Client mencoba mengirim manipulasi harga_jual = 100 dan total = 100
        $payload = [
            'token' => 'token-test-02',
            'bayar' => 100000,
            'harga_jual' => 100,
            'total' => 100,
            'items' => [
                [
                    'barang_id' => $this->barangA1->id,
                    'jumlah' => 1,
                    'harga_jual' => 100,
                    'harga_modal' => 50,
                    'subtotal' => 100,
                ],
            ],
        ];

        $response = $this->postJson(route('kasir.penjualan.store'), $payload);
        $response->assertOk();

        $penjualan = Penjualan::withoutGlobalScopes()->where('token', 'token-test-02')->first();
        $this->assertEquals(60000, $penjualan->total); // Diambil dari DB asli: 60.000
        $this->assertEquals(50000, $penjualan->total_modal); // Modal asli: 50.000
        $this->assertEquals(40000, $penjualan->kembalian); // 100.000 - 60.000
    }

    /**
     * Test 3: Snapshot harga_jual, harga_modal, nama_barang di detail tidak berubah saat barang diubah kemudian
     */
    public function test_03_snapshot_di_detail_tidak_berubah_saat_barang_diubah_kemudian(): void
    {
        $this->actingAs($this->kasirA);

        $this->postJson(route('kasir.penjualan.store'), [
            'token' => 'token-test-03',
            'bayar' => 100000,
            'items' => [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
        ]);

        $detail = DetailPenjualan::withoutGlobalScopes()->where('kode_barang', 'BRG-A001')->first();
        $this->assertEquals('Beras 5kg', $detail->nama_barang);
        $this->assertEquals(60000, $detail->harga_jual);
        $this->assertEquals(50000, $detail->harga_modal);

        // Ubah barang di master data
        $this->barangA1->update([
            'nama' => 'Beras Pandan Wangi Premium',
            'harga_jual' => 85000,
            'harga_modal' => 70000,
        ]);

        // Cek snapshot di detail tetap utuh
        $detailSegar = $detail->fresh();
        $this->assertEquals('Beras 5kg', $detailSegar->nama_barang);
        $this->assertEquals(60000, $detailSegar->harga_jual);
        $this->assertEquals(50000, $detailSegar->harga_modal);
    }

    /**
     * Test 4: total_modal = jumlah subtotal_modal, sehingga laba kotor = total - total_modal benar
     */
    public function test_04_total_modal_dan_laba_kotor_terhitung_benar(): void
    {
        $this->actingAs($this->kasirA);

        // Beras A1: 2 x 60.000 = 120.000 (modal 2 x 50.000 = 100.000)
        // Minyak A2: 3 x 17.000 = 51.000 (modal 3 x 14.000 = 42.000)
        // Total Jual = 171.000, Total Modal = 142.000, Laba = 29.000
        $this->postJson(route('kasir.penjualan.store'), [
            'token' => 'token-test-04',
            'bayar' => 200000,
            'items' => [
                ['barang_id' => $this->barangA1->id, 'jumlah' => 2],
                ['barang_id' => $this->barangA2->id, 'jumlah' => 3],
            ],
        ]);

        $penjualan = Penjualan::withoutGlobalScopes()->where('token', 'token-test-04')->first();
        $this->assertEquals(171000, $penjualan->total);
        $this->assertEquals(142000, $penjualan->total_modal);
        $this->assertEquals(29000, $penjualan->laba_kotor);
    }

    /**
     * Test 5: Stok tidak cukup ditolak, tidak ada perubahan apa pun (rollback total), pesan menyebut nama barang
     */
    public function test_05_stok_tidak_cukup_ditolak_rollback_total_dan_pesan_sebut_nama_barang(): void
    {
        $this->actingAs($this->kasirA);

        // Stok barangA1 adalah 15, dicoba beli 16
        $response = $this->postJson(route('kasir.penjualan.store'), [
            'token' => 'token-test-05',
            'bayar' => 1000000,
            'items' => [
                ['barang_id' => $this->barangA1->id, 'jumlah' => 16],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);

        $errorMsg = $response->json('errors.items.0');
        $this->assertStringContainsString('Beras 5kg', $errorMsg);

        // Pastikan tidak ada nota dan stok tidak berubah
        $this->assertDatabaseMissing('penjualan', ['token' => 'token-test-05']);
        $this->assertEquals(15, $this->barangA1->fresh()->stok);
    }

    /**
     * Test 6: Uang bayar kurang dari total ditolak
     */
    public function test_06_uang_bayar_kurang_dari_total_ditolak(): void
    {
        $this->actingAs($this->kasirA);

        // Total beli 60.000, dibayar 50.000
        $response = $this->postJson(route('kasir.penjualan.store'), [
            'token' => 'token-test-06',
            'bayar' => 50000,
            'items' => [
                ['barang_id' => $this->barangA1->id, 'jumlah' => 1],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bayar']);

        $this->assertDatabaseMissing('penjualan', ['token' => 'token-test-06']);
        $this->assertEquals(15, $this->barangA1->fresh()->stok);
    }

    /**
     * Test 7: Barang nonaktif dan barang kios lain ditolak
     */
    public function test_07_barang_nonaktif_dan_barang_kios_lain_ditolak(): void
    {
        $this->actingAs($this->kasirA);

        // 1. Barang nonaktif milik kios sendiri
        $resNonaktif = $this->postJson(route('kasir.penjualan.store'), [
            'token' => 'token-test-07-off',
            'bayar' => 50000,
            'items' => [['barang_id' => $this->barangAOff->id, 'jumlah' => 1]],
        ]);
        $resNonaktif->assertStatus(422);

        // 2. Barang milik kios B
        $resKiosB = $this->postJson(route('kasir.penjualan.store'), [
            'token' => 'token-test-07-b',
            'bayar' => 50000,
            'items' => [['barang_id' => $this->barangB1->id, 'jumlah' => 1]],
        ]);
        $resKiosB->assertStatus(422);
    }

    /**
     * Test 8: Item duplikat digabung dengan benar
     */
    public function test_08_item_duplikat_digabung_dengan_benar(): void
    {
        $this->actingAs($this->kasirA);

        // Mengirim barangA1 2 kali (qty 2 dan qty 3)
        $response = $this->postJson(route('kasir.penjualan.store'), [
            'token' => 'token-test-08',
            'bayar' => 500000,
            'items' => [
                ['barang_id' => $this->barangA1->id, 'jumlah' => 2],
                ['barang_id' => $this->barangA1->id, 'jumlah' => 3],
            ],
        ]);

        $response->assertOk();

        $penjualan = Penjualan::withoutGlobalScopes()->where('token', 'token-test-08')->first();
        // Total harus 5 x 60.000 = 300.000
        $this->assertEquals(300000, $penjualan->total);
        $this->assertEquals(10, $this->barangA1->fresh()->stok); // 15 - 5 = 10
    }

    /**
     * Test 9: Token yang sama dikirim dua kali tidak membuat nota atau pemotongan stok kedua (Idempotensi)
     */
    public function test_09_token_yang_sama_tidak_membuat_nota_atau_pemotongan_ganda(): void
    {
        $this->actingAs($this->kasirA);

        $payload = [
            'token' => 'token-idempotent-99',
            'bayar' => 100000,
            'items' => [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
        ];

        // Request 1
        $res1 = $this->postJson(route('kasir.penjualan.store'), $payload);
        $res1->assertOk();
        $this->assertEquals(14, $this->barangA1->fresh()->stok);

        // Request 2 (token sama)
        $res2 = $this->postJson(route('kasir.penjualan.store'), $payload);
        $res2->assertOk();

        // Stok TETAP 14 (tidak berkurang lagi menjadi 13)
        $this->assertEquals(14, $this->barangA1->fresh()->stok);

        // Jumlah nota dengan token ini hanya 1
        $count = Penjualan::withoutGlobalScopes()->where('token', 'token-idempotent-99')->count();
        $this->assertEquals(1, $count);
    }

    /**
     * Test 10: Dua transaksi berurutan pada stok terbatas: yang kedua ditolak bila stok habis
     */
    public function test_10_dua_transaksi_berurutan_pada_stok_terbatas(): void
    {
        $this->actingAs($this->kasirA);

        // Set stok barangA1 sisa 2
        $this->barangA1->update(['stok' => 2]);

        // Transaksi 1: Beli 2 -> Sukses
        $res1 = $this->postJson(route('kasir.penjualan.store'), [
            'token' => 'trx-berurutan-1',
            'bayar' => 200000,
            'items' => [['barang_id' => $this->barangA1->id, 'jumlah' => 2]],
        ]);
        $res1->assertOk();
        $this->assertEquals(0, $this->barangA1->fresh()->stok);

        // Transaksi 2: Beli 1 lagi -> Gagal karena stok habis
        $res2 = $this->postJson(route('kasir.penjualan.store'), [
            'token' => 'trx-berurutan-2',
            'bayar' => 100000,
            'items' => [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
        ]);
        $res2->assertStatus(422);

        // Stok tetap 0 (tidak pernah negatif)
        $this->assertEquals(0, $this->barangA1->fresh()->stok);
    }

    /**
     * Test 11: Nomor nota unik per kios dan urut harian; kios lain boleh nomor sama
     */
    public function test_11_nomor_nota_unik_per_kios_dan_urut_harian(): void
    {
        $service = app(PenjualanService::class);
        $today = Carbon::now('Asia/Jayapura');

        // Transaksi 1 Kios A
        $pA1 = $service->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'token-nomor-a1',
            user: $this->kasirA,
            waktuTransaksi: $today
        );

        // Transaksi 2 Kios A
        $pA2 = $service->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'token-nomor-a2',
            user: $this->kasirA,
            waktuTransaksi: $today
        );

        $expectedPrefix = 'TRX-' . $today->format('Ymd') . '-';
        $this->assertEquals($expectedPrefix . '0001', $pA1->nomor);
        $this->assertEquals($expectedPrefix . '0002', $pA2->nomor);

        // Transaksi 1 Kios B pada hari yang sama boleh nomor 0001
        $pB1 = $service->buat(
            items: [['barang_id' => $this->barangB1->id, 'jumlah' => 1]],
            bayar: 50000,
            token: 'token-nomor-b1',
            user: $this->kasirB,
            waktuTransaksi: $today
        );

        $this->assertEquals($expectedPrefix . '0001', $pB1->nomor);
    }

    /**
     * Test 12: Endpoint pencarian hanya mengembalikan barang aktif milik kios sendiri, maksimal 20, dan TIDAK memuat harga_modal
     */
    public function test_12_endpoint_pencarian_hanya_barang_aktif_kios_sendiri_dan_tanpa_harga_modal(): void
    {
        $this->actingAs($this->kasirA);

        $response = $this->getJson(route('kasir.barang.cari', ['q' => 'Beras']));
        $response->assertOk();

        $data = $response->json();
        $this->assertNotEmpty($data);

        foreach ($data as $item) {
            // Verifikasi tidak memuat harga_modal
            $this->assertArrayNotHasKey('harga_modal', $item);
            $this->assertArrayNotHasKey('total_modal', $item);
            $this->assertArrayNotHasKey('laba', $item);

            // Kolom aman harus ada
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('kode', $item);
            $this->assertArrayHasKey('nama', $item);
            $this->assertArrayHasKey('satuan', $item);
            $this->assertArrayHasKey('harga_jual', $item);
            $this->assertArrayHasKey('stok', $item);
        }

        // Pastikan barang B (Kios B) dan barang nonaktif tidak muncul
        $codes = array_column($data, 'kode');
        $this->assertContains('BRG-A001', $codes);
        $this->assertNotContains('BRG-B001', $codes);
        $this->assertNotContains('BRG-AOFF', $codes);

        // Buat 25 barang baru di kios A untuk cek limit 20
        $kat = Kategori::where('kios_id', $this->kiosA->id)->first();
        for ($i = 10; $i <= 35; $i++) {
            Barang::create([
                'kios_id' => $this->kiosA->id,
                'kategori_id' => $kat->id,
                'kode' => "BRG-LIMIT-{$i}",
                'nama' => "Barang Banyak {$i}",
                'satuan' => 'pcs',
                'harga_modal' => 1000,
                'harga_jual' => 2000,
                'stok' => 10,
                'stok_minimum' => 1,
                'is_aktif' => true,
            ]);
        }

        $resLimit = $this->getJson(route('kasir.barang.cari'));
        $resLimit->assertOk();
        $this->assertLessThanOrEqual(20, count($resLimit->json()));
    }

    /**
     * Test 13: Kasir mendapat 403 di /admin/penjualan; admin mendapat 404 untuk nota kios lain
     */
    public function test_13_kasir_dapat_403_di_admin_penjualan_dan_admin_dapat_404_nota_kios_lain(): void
    {
        // 1. Kasir mencoba akses admin penjualan -> 403
        $this->actingAs($this->kasirA);
        $this->get(route('admin.penjualan.index'))->assertForbidden();

        // 2. Buat nota di Kios B
        $this->flushSession();
        $this->actingAs($this->adminB);
        $service = app(PenjualanService::class);
        $pB = $service->buat(
            items: [['barang_id' => $this->barangB1->id, 'jumlah' => 1]],
            bayar: 50000,
            token: 'token-nota-b',
            user: $this->adminB
        );

        // 3. Admin A mencoba melihat nota Kios B -> 404
        $this->flushSession();
        $this->actingAs($this->adminA);
        $this->get(route('admin.penjualan.show', $pB->id))->assertNotFound();
    }

    /**
     * Test 14: Kasir tidak bisa membuka struk kasir lain (403/404), admin bisa membuka struk kios sendiri
     */
    public function test_14_hak_akses_struk_kasir_dan_admin(): void
    {
        $service = app(PenjualanService::class);

        // Transaksi oleh Kasir A
        $pA = $service->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'token-struk-a',
            user: $this->kasirA
        );

        // Kasir A bisa membuka struknya sendiri
        $this->actingAs($this->kasirA);
        $this->get(route('kasir.penjualan.struk', $pA->id))->assertOk();

        // Kasir A2 (kios sama, tapi kasir beda) mendapat 403
        $this->flushSession();
        $this->actingAs($this->kasirA2);
        $this->get(route('kasir.penjualan.struk', $pA->id))->assertForbidden();

        // Admin A (admin kios sendiri) BISA membuka struk
        $this->flushSession();
        $this->actingAs($this->adminA);
        $this->get(route('kasir.penjualan.struk', $pA->id))->assertOk();

        // Kasir B (kios lain) mendapat 404
        $this->flushSession();
        $this->actingAs($this->kasirB);
        $this->get(route('kasir.penjualan.struk', $pA->id))->assertNotFound();
    }

    /**
     * Test 15: Riwayat kasir hanya menampilkan transaksi hari ini milik kasir itu
     */
    public function test_15_riwayat_kasir_hanya_menampilkan_transaksi_hari_ini_milik_sendiri(): void
    {
        $service = app(PenjualanService::class);

        // 1. Transaksi Kasir A HARI INI
        $pToday = $service->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'token-kasir-a-today',
            user: $this->kasirA,
            waktuTransaksi: Carbon::now('Asia/Jayapura')
        );

        // 2. Transaksi Kasir A KEMARIN (pada shift kemarin yang sudah ditutup)
        $shiftKemarin = \App\Models\ShiftKasir::create([
            'kios_id' => $this->kiosA->id,
            'user_id' => $this->kasirA->id,
            'user_aktif' => null,
            'waktu_buka' => Carbon::now('Asia/Jayapura')->subDays(2),
            'waktu_tutup' => Carbon::now('Asia/Jayapura')->subDay(),
            'modal_awal' => 100000,
            'status' => 'ditutup',
        ]);
        $pYesterday = $service->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'token-kasir-a-yesterday',
            user: $this->kasirA,
            waktuTransaksi: Carbon::now('Asia/Jayapura')->subDay()
        );
        $pYesterday->shift_id = $shiftKemarin->id;
        $pYesterday->save();

        // 3. Transaksi Kasir A2 HARI INI
        $pA2 = $service->buat(
            items: [['barang_id' => $this->barangA2->id, 'jumlah' => 1]],
            bayar: 50000,
            token: 'token-kasir-a2-today',
            user: $this->kasirA2,
            waktuTransaksi: Carbon::now('Asia/Jayapura')
        );

        // Login sebagai Kasir A
        $this->flushSession();
        $this->actingAs($this->kasirA);
        $response = $this->get(route('kasir.riwayat'));
        $response->assertOk();

        // Hanya memuat nota Kasir A hari ini
        $response->assertSee($pToday->nomor);
        $response->assertDontSee($pYesterday->nomor);
        $response->assertDontSee($pA2->nomor);
    }

    /**
     * Test 16: Admin dapat memakai halaman kasir dan transaksinya tercatat atas namanya
     */
    public function test_16_admin_dapat_memakai_halaman_kasir_dan_tercatat_atas_nama_admin(): void
    {
        $this->actingAs($this->adminA);

        // Admin bisa buka layar kasir
        $this->get(route('kasir.index'))->assertOk();

        // Admin bertransaksi di kasir
        $res = $this->postJson(route('kasir.penjualan.store'), [
            'token' => 'token-admin-kasir',
            'bayar' => 100000,
            'items' => [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
        ]);
        $res->assertOk();

        $penjualan = Penjualan::withoutGlobalScopes()->where('token', 'token-admin-kasir')->first();
        $this->assertEquals($this->adminA->id, $penjualan->user_id);
    }

    /**
     * Test 17: Dashboard: "Penjualan Hari Ini" dan "Transaksi Hari Ini" benar, hanya untuk kios sendiri, dan batas hari mengikuti zona waktu Asia/Jayapura
     */
    public function test_17_dashboard_penjualan_dan_transaksi_hari_ini_benar_zona_asia_jayapura(): void
    {
        $service = app(PenjualanService::class);
        $nowJayapura = Carbon::now('Asia/Jayapura');

        // Transaksi Kios A Hari Ini (total = 60.000)
        $service->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'token-dash-a-today',
            user: $this->adminA,
            waktuTransaksi: $nowJayapura
        );

        // Transaksi Kios A Kemarin (total = 17.000)
        $service->buat(
            items: [['barang_id' => $this->barangA2->id, 'jumlah' => 1]],
            bayar: 50000,
            token: 'token-dash-a-yesterday',
            user: $this->adminA,
            waktuTransaksi: (clone $nowJayapura)->subDay()
        );

        // Transaksi Kios B Hari Ini (total = 14.000)
        $service->buat(
            items: [['barang_id' => $this->barangB1->id, 'jumlah' => 1]],
            bayar: 50000,
            token: 'token-dash-b-today',
            user: $this->adminB,
            waktuTransaksi: $nowJayapura
        );

        $this->actingAs($this->adminA);
        $response = $this->get(route('admin.dashboard'));
        $response->assertOk();

        // Harus melihat Penjualan Hari Ini dan Transaksi Hari Ini
        $response->assertSee('Penjualan Hari Ini');
        $response->assertSee('Transaksi Hari Ini');
        $response->assertSee('60.000'); // Omzet hari ini Kios A saja
        $response->assertSee('1 Transaksi'); // Hanya 1 transaksi hari ini Kios A
    }

    /**
     * Test 18: Tidak ada route update/destroy untuk penjualan (cek route list)
     */
    public function test_18_tidak_ada_route_update_atau_destroy_untuk_penjualan(): void
    {
        $this->assertFalse(Route::has('admin.penjualan.edit'));
        $this->assertFalse(Route::has('admin.penjualan.update'));
        $this->assertFalse(Route::has('admin.penjualan.destroy'));
        $this->assertFalse(Route::has('kasir.penjualan.edit'));
        $this->assertFalse(Route::has('kasir.penjualan.update'));
        $this->assertFalse(Route::has('kasir.penjualan.destroy'));
    }

    /**
     * Test 19: Filter admin (tanggal, kasir, status, nomor) bekerja benar
     */
    public function test_19_filter_admin_bekerja_benar(): void
    {
        $service = app(PenjualanService::class);
        $nowJayapura = Carbon::now('Asia/Jayapura');

        // Nota 1: Kasir A, Hari ini
        $p1 = $service->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'filter-token-1',
            user: $this->kasirA,
            waktuTransaksi: $nowJayapura
        );

        // Nota 2: Kasir A2, 2 hari lalu
        $p2 = $service->buat(
            items: [['barang_id' => $this->barangA2->id, 'jumlah' => 1]],
            bayar: 50000,
            token: 'filter-token-2',
            user: $this->kasirA2,
            waktuTransaksi: (clone $nowJayapura)->subDays(2)
        );

        $this->actingAs($this->adminA);

        // 1. Filter nomor nota
        $resNomor = $this->get(route('admin.penjualan.index', ['nomor' => $p1->nomor]));
        $resNomor->assertOk()
            ->assertSee($p1->nomor)
            ->assertDontSee($p2->nomor);

        // 2. Filter kasir A2
        $resKasir = $this->get(route('admin.penjualan.index', [
            'kasir_id' => $this->kasirA2->id,
            'tanggal_mulai' => (clone $nowJayapura)->subDays(5)->toDateString(),
            'tanggal_selesai' => $nowJayapura->toDateString(),
        ]));
        $resKasir->assertOk()
            ->assertSee($p2->nomor)
            ->assertDontSee($p1->nomor);

        // 3. Filter status selesai
        $resStatus = $this->get(route('admin.penjualan.index', [
            'status' => 'selesai',
            'tanggal_mulai' => (clone $nowJayapura)->subDays(5)->toDateString(),
            'tanggal_selesai' => $nowJayapura->toDateString(),
        ]));
        $resStatus->assertOk()
            ->assertSee($p1->nomor)
            ->assertSee($p2->nomor);
    }
}
