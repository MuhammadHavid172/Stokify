<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\Kategori;
use App\Models\Kios;
use App\Models\LogAktivitas;
use App\Models\MutasiStok;
use App\Models\Supplier;
use App\Models\User;
use App\Services\BarangMasukService;
use App\Services\StokService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TahapLimaTest extends TestCase
{
    use RefreshDatabase;

    protected Kios $kiosA;
    protected Kios $kiosB;
    protected User $adminA;
    protected User $kasirA;
    protected User $adminB;
    protected User $kasirB;
    protected Supplier $supplierA;
    protected Supplier $supplierB;
    protected Barang $barangA1;
    protected Barang $barangA2;
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

        // 3. Supplier A & B
        $this->supplierA = Supplier::create([
            'kios_id' => $this->kiosA->id,
            'nama' => 'CV Sumber Berkah',
            'no_telepon' => '081234567801',
            'alamat' => 'Jayapura',
            'is_aktif' => true,
        ]);

        $this->supplierB = Supplier::create([
            'kios_id' => $this->kiosB->id,
            'nama' => 'PT Pangan Bersama',
            'no_telepon' => '089876543201',
            'alamat' => 'Abepura',
            'is_aktif' => true,
        ]);

        // 4. Kategori & Barang
        $katA = Kategori::create([
            'kios_id' => $this->kiosA->id,
            'nama' => 'Sembako',
        ]);

        $this->barangA1 = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $katA->id,
            'kode' => 'BRG-0001',
            'nama' => 'Beras 5kg',
            'satuan' => 'karung',
            'harga_modal' => 50000,
            'harga_jual' => 60000,
            'stok' => 10,
            'stok_minimum' => 2,
            'is_aktif' => true,
        ]);

        $this->barangA2 = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $katA->id,
            'kode' => 'BRG-0002',
            'nama' => 'Minyak Goreng 1L',
            'satuan' => 'botol',
            'harga_modal' => 14000,
            'harga_jual' => 16000,
            'stok' => 5,
            'stok_minimum' => 2,
            'is_aktif' => true,
        ]);

        $katB = Kategori::create([
            'kios_id' => $this->kiosB->id,
            'nama' => 'Sembako B',
        ]);

        $this->barangB1 = Barang::create([
            'kios_id' => $this->kiosB->id,
            'kategori_id' => $katB->id,
            'kode' => 'BRG-B001',
            'nama' => 'Gula Pasir 1kg',
            'satuan' => 'kg',
            'harga_modal' => 12000,
            'harga_jual' => 15000,
            'stok' => 8,
            'stok_minimum' => 2,
            'is_aktif' => true,
        ]);
    }

    /**
     * Test 1: Nota barang masuk tersimpan: stok bertambah, baris mutasi_stok bertipe barang_masuk terbentuk, total benar.
     */
    public function test_nota_barang_masuk_tersimpan_stok_bertambah_mutasi_terbentuk_total_benar(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post(route('admin.barang-masuk.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'supplier_id' => $this->supplierA->id,
            'catatan' => 'Barang masuk sembako pertama',
            'items' => [
                ['barang_id' => $this->barangA1->id, 'jumlah' => 10, 'harga_beli' => 52000],
                ['barang_id' => $this->barangA2->id, 'jumlah' => 5, 'harga_beli' => 15000],
            ],
        ]);

        $this->assertDatabaseHas('barang_masuk', [
            'kios_id' => $this->kiosA->id,
            'supplier_id' => $this->supplierA->id,
            'total' => (10 * 52000) + (5 * 15000), // 520.000 + 75.000 = 595.000
            'status' => 'selesai',
        ]);

        $nota = BarangMasuk::withoutGlobalScopes()->where('kios_id', $this->kiosA->id)->first();
        $response->assertRedirect(route('admin.barang-masuk.show', $nota));

        // Cek stok bertambah
        $this->assertEquals(20, $this->barangA1->fresh()->stok); // 10 + 10
        $this->assertEquals(10, $this->barangA2->fresh()->stok); // 5 + 5

        // Cek mutasi stok bertipe barang_masuk terbentuk
        $this->assertDatabaseHas('mutasi_stok', [
            'kios_id' => $this->kiosA->id,
            'barang_id' => $this->barangA1->id,
            'tipe' => 'barang_masuk',
            'jumlah' => 10,
            'referensi_type' => BarangMasuk::class,
            'referensi_id' => $nota->id,
        ]);
    }

    /**
     * Test 2: Harga modal rata-rata tertimbang benar.
     * Contoh spek: stok 10 modal 1.000, beli 10 dengan harga 2.000 -> hasil modal 1.500 dan stok 20.
     */
    public function test_harga_modal_rata_rata_tertimbang_benar(): void
    {
        $this->actingAs($this->adminA);

        // Siapkan barang khusus sesuai contoh spek
        $barangContoh = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-SPEC',
            'nama' => 'Barang Spek Tertimbang',
            'satuan' => 'pcs',
            'harga_modal' => 1000,
            'harga_jual' => 2500,
            'stok' => 10,
            'stok_minimum' => 2,
            'is_aktif' => true,
        ]);

        $this->post(route('admin.barang-masuk.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                ['barang_id' => $barangContoh->id, 'jumlah' => 10, 'harga_beli' => 2000],
            ],
        ]);

        $barangContoh->refresh();
        $this->assertEquals(20, $barangContoh->stok);
        $this->assertEquals(1500, $barangContoh->harga_modal);
    }

    /**
     * Test 3: Stok lama 0 atau negatif: modal baru = harga beli.
     */
    public function test_stok_lama_nol_atau_negatif_modal_baru_sama_dengan_harga_beli(): void
    {
        $this->actingAs($this->adminA);

        $barangStokNol = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-NOL',
            'nama' => 'Barang Stok Nol',
            'satuan' => 'pcs',
            'harga_modal' => 5000,
            'harga_jual' => 7000,
            'stok' => 0,
            'stok_minimum' => 2,
            'is_aktif' => true,
        ]);

        $this->post(route('admin.barang-masuk.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                ['barang_id' => $barangStokNol->id, 'jumlah' => 15, 'harga_beli' => 8500],
            ],
        ]);

        $barangStokNol->refresh();
        $this->assertEquals(15, $barangStokNol->stok);
        $this->assertEquals(8500, $barangStokNol->harga_modal);
    }

    /**
     * Test 4: Pembulatan rupiah benar untuk hasil pecahan.
     * Stok 10 @ 1.000, beli 10 @ 1.255:
     * ((10 * 1000) + (10 * 1255)) / 20 = 22550 / 20 = 1127.5 -> round = 1128.
     */
    public function test_pembulatan_rupiah_benar_untuk_hasil_pecahan(): void
    {
        $this->actingAs($this->adminA);

        $barangPecahan = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-ROUND',
            'nama' => 'Barang Pembulatan',
            'satuan' => 'pcs',
            'harga_modal' => 1000,
            'harga_jual' => 2000,
            'stok' => 10,
            'stok_minimum' => 2,
            'is_aktif' => true,
        ]);

        $this->post(route('admin.barang-masuk.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                ['barang_id' => $barangPecahan->id, 'jumlah' => 10, 'harga_beli' => 1255],
            ],
        ]);

        $barangPecahan->refresh();
        $this->assertEquals(1128, $barangPecahan->harga_modal);
    }

    /**
     * Test 5: Rollback total: jika satu item tidak valid, tidak ada stok, nota, atau mutasi yang berubah.
     */
    public function test_rollback_total_jika_satu_item_tidak_valid_tidak_ada_yang_berubah(): void
    {
        $this->actingAs($this->adminA);

        $stokAwal = $this->barangA1->stok;

        // Kirim request di mana salah satu item invalid (ID barang 99999)
        $response = $this->post(route('admin.barang-masuk.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                ['barang_id' => $this->barangA1->id, 'jumlah' => 10, 'harga_beli' => 52000],
                ['barang_id' => 99999, 'jumlah' => 5, 'harga_beli' => 15000],
            ],
        ]);

        $response->assertSessionHasErrors();

        // Pastikan tidak ada data yang tersimpan atau berubah
        $this->assertEquals($stokAwal, $this->barangA1->fresh()->stok);
        $this->assertEquals(0, BarangMasuk::withoutGlobalScopes()->where('kios_id', $this->kiosA->id)->count());
        $this->assertEquals(0, MutasiStok::withoutGlobalScopes()->where('kios_id', $this->kiosA->id)->where('tipe', 'barang_masuk')->count());
    }

    /**
     * Test 6: Barang nonaktif dan barang kios lain ditolak; supplier kios lain ditolak.
     */
    public function test_barang_nonaktif_dan_barang_kios_lain_ditolak_supplier_kios_lain_ditolak(): void
    {
        $this->actingAs($this->adminA);

        $barangNonaktif = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-OFF',
            'nama' => 'Barang Nonaktif',
            'satuan' => 'pcs',
            'harga_modal' => 5000,
            'harga_jual' => 7000,
            'stok' => 5,
            'stok_minimum' => 2,
            'is_aktif' => false,
        ]);

        // 1. Barang nonaktif ditolak
        $response1 = $this->post(route('admin.barang-masuk.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                ['barang_id' => $barangNonaktif->id, 'jumlah' => 5, 'harga_beli' => 5000],
            ],
        ]);
        $response1->assertSessionHasErrors('items.0.barang_id');

        // 2. Barang milik kios B ditolak
        $response2 = $this->post(route('admin.barang-masuk.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                ['barang_id' => $this->barangB1->id, 'jumlah' => 5, 'harga_beli' => 12000],
            ],
        ]);
        $response2->assertSessionHasErrors('items.0.barang_id');

        // 3. Supplier milik kios B ditolak
        $response3 = $this->post(route('admin.barang-masuk.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'supplier_id' => $this->supplierB->id,
            'items' => [
                ['barang_id' => $this->barangA1->id, 'jumlah' => 5, 'harga_beli' => 50000],
            ],
        ]);
        $response3->assertSessionHasErrors('supplier_id');
    }

    /**
     * Test 7: Barang duplikat dalam satu nota ditangani sesuai aturan (digabungkan secara rapi).
     */
    public function test_barang_duplikat_dalam_satu_nota_ditangani_sesuai_aturan(): void
    {
        $this->actingAs($this->adminA);

        // Mengirimkan 2 baris item dengan barang_id yang sama
        $this->post(route('admin.barang-masuk.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                ['barang_id' => $this->barangA1->id, 'jumlah' => 5, 'harga_beli' => 50000],
                ['barang_id' => $this->barangA1->id, 'jumlah' => 5, 'harga_beli' => 52000],
            ],
        ]);

        $nota = BarangMasuk::withoutGlobalScopes()->where('kios_id', $this->kiosA->id)->latest('id')->first();
        $this->assertNotNull($nota);

        // Hanya 1 detail barang_masuk terkonsolidasi dengan jumlah 10 dan rata-rata harga beli 51.000
        $this->assertEquals(1, $nota->detail()->count());
        $detail = $nota->detail()->first();
        $this->assertEquals(10, $detail->jumlah);
        $this->assertEquals(51000, $detail->harga_beli);
        $this->assertEquals(510000, $detail->subtotal);

        // Total nota sesuai
        $this->assertEquals(510000, $nota->total);
    }

    /**
     * Test 8: Nomor nota unik per kios dan urut harian; kios lain boleh nomor sama.
     */
    public function test_nomor_nota_unik_per_kios_dan_urut_harian_kios_lain_boleh_nomor_sama(): void
    {
        $todayStr = Carbon::today()->format('Ymd');

        // Nota 1 Kios A
        $this->actingAs($this->adminA);
        $this->post(route('admin.barang-masuk.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [['barang_id' => $this->barangA1->id, 'jumlah' => 1, 'harga_beli' => 50000]],
        ]);

        // Nota 2 Kios A
        $this->post(route('admin.barang-masuk.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [['barang_id' => $this->barangA1->id, 'jumlah' => 1, 'harga_beli' => 50000]],
        ]);

        $notasA = BarangMasuk::withoutGlobalScopes()->where('kios_id', $this->kiosA->id)->orderBy('id', 'asc')->get();
        $this->assertEquals("BM-{$todayStr}-0001", $notasA[0]->nomor);
        $this->assertEquals("BM-{$todayStr}-0002", $notasA[1]->nomor);

        // Nota 1 Kios B (harus mulai dari 0001 lagi untuk kios B)
        $this->flushSession();
        $this->actingAs($this->adminB);
        $this->post(route('admin.barang-masuk.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [['barang_id' => $this->barangB1->id, 'jumlah' => 1, 'harga_beli' => 12000]],
        ]);

        $notaB = BarangMasuk::withoutGlobalScopes()->where('kios_id', $this->kiosB->id)->first();
        $this->assertEquals("BM-{$todayStr}-0001", $notaB->nomor);
    }

    /**
     * Test 9: Total dan subtotal dari input client diabaikan (dihitung ulang di server).
     */
    public function test_total_dan_subtotal_dari_input_client_diabaikan(): void
    {
        $this->actingAs($this->adminA);

        // Client mencoba spoof total = 100 dan subtotal = 50
        $this->post(route('admin.barang-masuk.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'total' => 100,
            'items' => [
                ['barang_id' => $this->barangA1->id, 'jumlah' => 10, 'harga_beli' => 50000, 'subtotal' => 50],
            ],
        ]);

        $nota = BarangMasuk::withoutGlobalScopes()->where('kios_id', $this->kiosA->id)->first();
        // Server tetap menghitung total = 10 * 50.000 = 500.000
        $this->assertEquals(500000, $nota->total);
        $this->assertEquals(500000, $nota->detail()->first()->subtotal);
    }

    /**
     * Test 10: Pembatalan nota: stok kembali, status berubah, alasan wajib.
     */
    public function test_pembatalan_nota_stok_kembali_status_berubah_alasan_wajib(): void
    {
        $this->actingAs($this->adminA);

        // Buat nota masuk (stok awal 10, masuk 5 -> stok jadi 15)
        $this->post(route('admin.barang-masuk.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                ['barang_id' => $this->barangA1->id, 'jumlah' => 5, 'harga_beli' => 50000],
            ],
        ]);

        $nota = BarangMasuk::withoutGlobalScopes()->where('kios_id', $this->kiosA->id)->first();
        $this->assertEquals(15, $this->barangA1->fresh()->stok);

        // 1. Pembatalan tanpa alasan / kurang dari 5 karakter gagal
        $response1 = $this->post(route('admin.barang-masuk.batal', $nota), [
            'alasan' => 'nope', // 4 karakter
        ]);
        $response1->assertSessionHasErrors('alasan');
        $this->assertEquals('selesai', $nota->fresh()->status);

        // 2. Pembatalan dengan alasan valid berhasil
        $response2 = $this->post(route('admin.barang-masuk.batal', $nota), [
            'alasan' => 'Salah input faktur barang masuk dari distributor',
        ]);
        $response2->assertRedirect(route('admin.barang-masuk.show', $nota));

        $nota->refresh();
        $this->assertEquals('dibatalkan', $nota->status);
        $this->assertEquals($this->adminA->id, $nota->dibatalkan_oleh);
        $this->assertNotNull($nota->dibatalkan_pada);
        $this->assertEquals('Salah input faktur barang masuk dari distributor', $nota->alasan_batal);

        // Stok kembali ke 10
        $this->assertEquals(10, $this->barangA1->fresh()->stok);

        // Mutasi stok penyesuaian terbentuk dengan jumlah negatif
        $this->assertDatabaseHas('mutasi_stok', [
            'kios_id' => $this->kiosA->id,
            'barang_id' => $this->barangA1->id,
            'tipe' => 'penyesuaian',
            'jumlah' => -5,
            'referensi_type' => BarangMasuk::class,
            'referensi_id' => $nota->id,
        ]);
    }

    /**
     * Test 11: Pembatalan ditolak penuh jika stok tidak cukup (tidak ada perubahan sama sekali).
     */
    public function test_pembatalan_ditolak_penuh_jika_stok_tidak_cukup_tidak_ada_perubahan_sama_sekali(): void
    {
        $this->actingAs($this->adminA);

        // Buat nota masuk dengan 2 barang
        $this->post(route('admin.barang-masuk.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                ['barang_id' => $this->barangA1->id, 'jumlah' => 10, 'harga_beli' => 50000],
                ['barang_id' => $this->barangA2->id, 'jumlah' => 10, 'harga_beli' => 14000],
            ],
        ]);

        $nota = BarangMasuk::withoutGlobalScopes()->where('kios_id', $this->kiosA->id)->first();
        $stokA1 = $this->barangA1->fresh()->stok; // 20
        $stokA2 = $this->barangA2->fresh()->stok; // 15

        // Simulasikan sebagian barang A1 terjual sehingga stok tersisa hanya 5 (kurang dari 10 yang masuk)
        $this->barangA1->stok = 5;
        $this->barangA1->save();

        // Coba batalkan nota
        $response = $this->post(route('admin.barang-masuk.batal', $nota), [
            'alasan' => 'Ingin batalkan seluruh nota barang masuk ini',
        ]);

        // Di-redirect kembali dengan flash error
        $response->assertSessionHas('error');
        $this->assertStringContainsString('tidak cukup untuk dibatalkan', session('error'));

        // Nota tetap selesai
        $this->assertEquals('selesai', $nota->fresh()->status);
        // Stok A1 tetap 5, Stok A2 tetap 15 (tidak ada perubahan parsial!)
        $this->assertEquals(5, $this->barangA1->fresh()->stok);
        $this->assertEquals($stokA2, $this->barangA2->fresh()->stok);
    }

    /**
     * Test 12: Nota dibatalkan tidak bisa dibatalkan dua kali.
     */
    public function test_nota_dibatalkan_tidak_bisa_dibatalkan_dua_kali(): void
    {
        $this->actingAs($this->adminA);

        $this->post(route('admin.barang-masuk.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                ['barang_id' => $this->barangA1->id, 'jumlah' => 5, 'harga_beli' => 50000],
            ],
        ]);

        $nota = BarangMasuk::withoutGlobalScopes()->where('kios_id', $this->kiosA->id)->first();

        // Batal pertama
        $this->post(route('admin.barang-masuk.batal', $nota), [
            'alasan' => 'Batal pertama kali oleh admin',
        ]);
        $this->assertEquals('dibatalkan', $nota->fresh()->status);

        // Batal kedua kali
        $response = $this->post(route('admin.barang-masuk.batal', $nota), [
            'alasan' => 'Mencoba membatalkan lagi untuk kedua kalinya',
        ]);

        $response->assertSessionHas('error');
    }

    /**
     * Test 13: Admin Kios A mendapat 404 pada nota dan supplier Kios B.
     */
    public function test_admin_kios_a_mendapat_404_pada_nota_dan_supplier_kios_b(): void
    {
        // Buat nota di Kios B
        $this->actingAs($this->adminB);
        $this->post(route('admin.barang-masuk.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'supplier_id' => $this->supplierB->id,
            'items' => [
                ['barang_id' => $this->barangB1->id, 'jumlah' => 5, 'harga_beli' => 12000],
            ],
        ]);

        $notaB = BarangMasuk::withoutGlobalScopes()->where('kios_id', $this->kiosB->id)->first();

        // Login sebagai Admin A
        $this->flushSession();
        $this->actingAs($this->adminA);

        // Akses nota B
        $responseNota = $this->get(route('admin.barang-masuk.show', $notaB));
        $responseNota->assertStatus(404);

        // Coba batalkan nota B
        $responseBatal = $this->post(route('admin.barang-masuk.batal', $notaB), [
            'alasan_batal' => 'Mencoba membatalkan nota milik kios lain',
        ]);
        $responseBatal->assertStatus(404);

        // Akses supplier B (update & toggle)
        $responseSupUpdate = $this->put(route('admin.supplier.update', $this->supplierB), [
            'nama' => 'Hacker Supplier',
        ]);
        $responseSupUpdate->assertStatus(404);

        $responseSupToggle = $this->post(route('admin.supplier.toggle', $this->supplierB));
        $responseSupToggle->assertStatus(404);
    }

    /**
     * Test 14: Kasir mendapat 403 di /admin/barang-masuk dan /admin/supplier.
     */
    public function test_kasir_mendapat_403_di_barang_masuk_dan_supplier(): void
    {
        $this->actingAs($this->kasirA);

        $this->get(route('admin.barang-masuk.index'))->assertStatus(403);
        $this->get(route('admin.barang-masuk.create'))->assertStatus(403);
        $this->get(route('admin.supplier.index'))->assertStatus(403);
    }

    /**
     * Test 15: Tidak ada route destroy untuk nota dan supplier.
     */
    public function test_tidak_ada_route_destroy_untuk_nota_dan_supplier(): void
    {
        $this->assertFalse(Route::has('admin.barang-masuk.destroy'));
        $this->assertFalse(Route::has('admin.supplier.destroy'));

        // Coba panggil DELETE method secara langsung
        $this->actingAs($this->adminA);

        $this->delete('/admin/supplier/1')->assertStatus(405);
        $this->delete('/admin/barang-masuk/1')->assertStatus(405);
    }

    /**
     * Test 16: Log aktivitas tercatat pada tambah dan batal.
     */
    public function test_log_aktivitas_tercatat_pada_tambah_dan_batal(): void
    {
        $this->actingAs($this->adminA);

        // 1. Tambah barang masuk
        $this->post(route('admin.barang-masuk.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                ['barang_id' => $this->barangA1->id, 'jumlah' => 10, 'harga_beli' => 55000],
            ],
        ]);

        $nota = BarangMasuk::withoutGlobalScopes()->where('kios_id', $this->kiosA->id)->first();

        $this->assertDatabaseHas('log_aktivitas', [
            'kios_id' => $this->kiosA->id,
            'aksi' => 'barang_masuk.tambah',
        ]);

        // Karena harga modal berubah dari 50.000 menjadi modal tertimbang baru, log barang.ubah_harga juga tercatat
        $this->assertDatabaseHas('log_aktivitas', [
            'kios_id' => $this->kiosA->id,
            'aksi' => 'barang.ubah_harga',
        ]);

        // 2. Batal nota
        $this->post(route('admin.barang-masuk.batal', $nota), [
            'alasan' => 'Batalkan untuk memeriksa log aktivitas',
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'kios_id' => $this->kiosA->id,
            'aksi' => 'barang_masuk.batal',
        ]);
    }
}
