<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Kios;
use App\Models\LogAktivitas;
use App\Models\MutasiStok;
use App\Models\User;
use App\Services\StokService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;
use Tests\TestCase;

class TahapEmpatTest extends TestCase
{
    use RefreshDatabase;

    protected Kios $kiosA;
    protected Kios $kiosB;
    protected User $adminA;
    protected User $kasirA;
    protected User $adminB;
    protected User $kasirB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Kios A & Pengguna
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

        // 2. Setup Kios B & Pengguna
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
    }

    /**
     * 1. Admin dapat membuat barang; kios_id otomatis milik admin walau dikirim manual.
     */
    public function test_admin_dapat_membuat_barang_dan_kios_id_otomatis_milik_admin(): void
    {
        $response = $this->actingAs($this->adminA)->post(route('admin.barang.store'), [
            'kios_id' => $this->kiosB->id, // Mencoba memanipulasi kios_id ke Kios B
            'kode' => 'BRG-TEST-1',
            'nama' => 'Beras Pandan Wangi 5kg',
            'satuan' => 'karung',
            'harga_modal' => 70000,
            'harga_jual' => 78000,
            'stok_awal' => 10,
            'stok_minimum' => 3,
        ]);

        $response->assertRedirect(route('admin.barang.index'));
        $response->assertSessionHas('success');

        $barang = Barang::withoutGlobalScopes()->where('kode', 'BRG-TEST-1')->first();
        $this->assertNotNull($barang);
        $this->assertEquals($this->kiosA->id, $barang->kios_id, 'kios_id harus milik admin kios yang login, bukan yang dikirim manual');
        $this->assertEquals('Beras Pandan Wangi 5kg', $barang->nama);
        $this->assertEquals(10, $barang->stok);
    }

    /**
     * 2. Kode yang sama ditolak dalam satu kios, tetapi BOLEH di kios lain.
     */
    public function test_kode_sama_ditolak_dalam_satu_kios_tetapi_boleh_di_kios_lain(): void
    {
        // Buat barang pertama di Kios A
        Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-DUPLIKAT',
            'nama' => 'Barang Asli Kios A',
            'satuan' => 'pcs',
            'harga_modal' => 5000,
            'harga_jual' => 6000,
            'stok' => 5,
            'stok_minimum' => 2,
            'is_aktif' => true,
        ]);

        // Coba buat dengan kode sama di Kios A -> harus ditolak
        $responseA = $this->actingAs($this->adminA)->post(route('admin.barang.store'), [
            'kode' => 'BRG-DUPLIKAT',
            'nama' => 'Barang Duplikat Kios A',
            'satuan' => 'pcs',
            'harga_modal' => 5000,
            'harga_jual' => 6000,
            'stok_awal' => 0,
            'stok_minimum' => 0,
        ]);
        $responseA->assertSessionHasErrors('kode');

        // Coba buat dengan kode sama di Kios B -> harus BERHASIL
        $this->flushSession();
        $responseB = $this->actingAs($this->adminB)->post(route('admin.barang.store'), [
            'kode' => 'BRG-DUPLIKAT',
            'nama' => 'Barang Kios B Kode Sama',
            'satuan' => 'pcs',
            'harga_modal' => 5000,
            'harga_jual' => 6000,
            'stok_awal' => 0,
            'stok_minimum' => 0,
        ]);
        $responseB->assertRedirect(route('admin.barang.index'));
        $responseB->assertSessionHasNoErrors();

        // Pastikan ada 2 barang di database dengan kode BRG-DUPLIKAT pada 2 kios berbeda
        $count = Barang::withoutGlobalScopes()->where('kode', 'BRG-DUPLIKAT')->count();
        $this->assertEquals(2, $count);
    }

    /**
     * 3. Stok awal > 0 menghasilkan satu baris mutasi_stok bertipe stok_awal dengan stok_sebelum 0.
     */
    public function test_stok_awal_lebih_dari_nol_menghasilkan_mutasi_stok_awal(): void
    {
        $this->actingAs($this->adminA)->post(route('admin.barang.store'), [
            'kode' => 'BRG-STOKAWAL',
            'nama' => 'Gula Pasir 1kg',
            'satuan' => 'kg',
            'harga_modal' => 14000,
            'harga_jual' => 16000,
            'stok_awal' => 25,
            'stok_minimum' => 5,
        ]);

        $barang = Barang::withoutGlobalScopes()->where('kode', 'BRG-STOKAWAL')->first();
        $this->assertNotNull($barang);
        $this->assertEquals(25, $barang->stok);

        $mutasi = MutasiStok::withoutGlobalScopes()->where('barang_id', $barang->id)->first();
        $this->assertNotNull($mutasi);
        $this->assertEquals('stok_awal', $mutasi->tipe);
        $this->assertEquals(25, $mutasi->jumlah);
        $this->assertEquals(0, $mutasi->stok_sebelum);
        $this->assertEquals(25, $mutasi->stok_sesudah);
        $this->assertEquals($this->adminA->id, $mutasi->user_id);
    }

    /**
     * 4. Admin kios A mendapat 404 saat membuka, mengubah, atau menonaktifkan barang kios B.
     */
    public function test_admin_kios_a_tidak_bisa_mengakses_barang_kios_b_404(): void
    {
        $barangB = Barang::create([
            'kios_id' => $this->kiosB->id,
            'kode' => 'BRG-KIOS-B',
            'nama' => 'Barang Milik Kios B',
            'satuan' => 'pcs',
            'harga_modal' => 10000,
            'harga_jual' => 12000,
            'stok' => 10,
            'stok_minimum' => 2,
            'is_aktif' => true,
        ]);

        $this->actingAs($this->adminA);

        // Show detail -> 404
        $this->get(route('admin.barang.show', $barangB))->assertNotFound();

        // Edit form -> 404
        $this->get(route('admin.barang.edit', $barangB))->assertNotFound();

        // Update -> 404
        $this->put(route('admin.barang.update', $barangB), [
            'kode' => 'BRG-KIOS-B',
            'nama' => 'Nama Diretas',
            'satuan' => 'pcs',
            'harga_modal' => 10000,
            'harga_jual' => 12000,
        ])->assertNotFound();

        // Toggle -> 404
        $this->post(route('admin.barang.toggle', $barangB))->assertNotFound();

        // Pastikan nama barang B tidak berubah
        $this->assertEquals('Barang Milik Kios B', $barangB->fresh()->nama);
    }

    /**
     * 5. kategori_id milik kios lain ditolak validasi.
     */
    public function test_kategori_id_milik_kios_lain_ditolak_validasi(): void
    {
        $kategoriB = Kategori::create([
            'kios_id' => $this->kiosB->id,
            'nama' => 'Kategori Khusus Kios B',
        ]);

        $response = $this->actingAs($this->adminA)->post(route('admin.barang.store'), [
            'kode' => 'BRG-KAT-CROSS',
            'nama' => 'Barang Coba Kategori Lain',
            'kategori_id' => $kategoriB->id,
            'satuan' => 'pcs',
            'harga_modal' => 5000,
            'harga_jual' => 6000,
            'stok_awal' => 0,
            'stok_minimum' => 0,
        ]);

        $response->assertSessionHasErrors('kategori_id');
        $this->assertNull(Barang::withoutGlobalScopes()->where('kode', 'BRG-KAT-CROSS')->first());
    }

    /**
     * 6. Field stok yang dikirim lewat form ubah diabaikan (stok tidak berubah).
     */
    public function test_field_stok_pada_form_ubah_diabaikan(): void
    {
        $barang = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-EDIT-STOK',
            'nama' => 'Minyak Goreng 2L',
            'satuan' => 'botol',
            'harga_modal' => 30000,
            'harga_jual' => 35000,
            'stok' => 15,
            'stok_minimum' => 5,
            'is_aktif' => true,
        ]);

        $response = $this->actingAs($this->adminA)->put(route('admin.barang.update', $barang), [
            'kode' => 'BRG-EDIT-STOK',
            'nama' => 'Minyak Goreng 2L Refill',
            'satuan' => 'botol',
            'harga_modal' => 30000,
            'harga_jual' => 35000,
            'stok_minimum' => 5,
            'stok' => 9999, // Manipulasi stok via PUT
            'stok_awal' => 9999,
        ]);

        $response->assertRedirect(route('admin.barang.index'));

        $fresh = $barang->fresh();
        $this->assertEquals('Minyak Goreng 2L Refill', $fresh->nama);
        $this->assertEquals(15, $fresh->stok, 'Stok harus tetap 15 dan mengabaikan nilai input form ubah');
    }

    /**
     * 7. Ubah harga menghasilkan log aktivitas dengan data lama dan baru.
     */
    public function test_ubah_harga_menghasilkan_log_aktivitas(): void
    {
        $barang = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-LOG-HARGA',
            'nama' => 'Kopi Robusta 100g',
            'satuan' => 'bungkus',
            'harga_modal' => 12000,
            'harga_jual' => 15000,
            'stok' => 10,
            'stok_minimum' => 2,
            'is_aktif' => true,
        ]);

        $this->actingAs($this->adminA)->put(route('admin.barang.update', $barang), [
            'kode' => 'BRG-LOG-HARGA',
            'nama' => 'Kopi Robusta 100g',
            'satuan' => 'bungkus',
            'harga_modal' => 13500, // Harga modal naik
            'harga_jual' => 17000,  // Harga jual naik
            'stok_minimum' => 2,
        ]);

        $log = LogAktivitas::withoutGlobalScopes()
            ->where('aksi', 'barang.ubah_harga')
            ->where('kios_id', $this->kiosA->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(12000, $log->data_lama['harga_modal']);
        $this->assertEquals(15000, $log->data_lama['harga_jual']);
        $this->assertEquals(13500, $log->data_baru['harga_modal']);
        $this->assertEquals(17000, $log->data_baru['harga_jual']);
    }

    /**
     * 8. StokService menolak stok menjadi negatif dan tidak mengubah apa pun (rollback).
     */
    public function test_stok_service_menolak_stok_negatif_dan_rollback(): void
    {
        $barang = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-ROLLBACK',
            'nama' => 'Air Galon 19L',
            'satuan' => 'galon',
            'harga_modal' => 15000,
            'harga_jual' => 19000,
            'stok' => 5,
            'stok_minimum' => 2,
            'is_aktif' => true,
        ]);

        $stokService = app(StokService::class);

        $caughtException = false;
        try {
            // Pengurangan 10 saat stok hanya 5 -> stok sesudah = -5 -> harus throw
            $stokService->catat($barang, 'penjualan', -10, 'Uji coba stok minus');
        } catch (InvalidArgumentException $e) {
            $caughtException = true;
        }

        $this->assertTrue($caughtException, 'StokService harus melempar exception saat stok menjadi negatif');
        $this->assertEquals(5, $barang->fresh()->stok, 'Stok barang harus tetap 5 setelah rollback');

        // Pastikan tidak ada mutasi stok yang tersimpan untuk barang ini
        $mutasiCount = MutasiStok::withoutGlobalScopes()->where('barang_id', $barang->id)->count();
        $this->assertEquals(0, $mutasiCount, 'Baris mutasi tidak boleh terbentuk jika transaksi rollback');
    }

    /**
     * 9. Barang nonaktif dan tidak ada route destroy untuk barang (cek route list).
     */
    public function test_toggle_barang_dan_tidak_ada_route_destroy_barang(): void
    {
        $barang = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-TOGGLE',
            'nama' => 'Rokok Kretek',
            'satuan' => 'bungkus',
            'harga_modal' => 20000,
            'harga_jual' => 23000,
            'stok' => 10,
            'stok_minimum' => 2,
            'is_aktif' => true,
        ]);

        // Toggle jadi nonaktif
        $this->actingAs($this->adminA)->post(route('admin.barang.toggle', $barang))
            ->assertRedirect();
        $this->assertFalse($barang->fresh()->is_aktif);

        // Toggle kembali jadi aktif
        $this->actingAs($this->adminA)->post(route('admin.barang.toggle', $barang))
            ->assertRedirect();
        $this->assertTrue($barang->fresh()->is_aktif);

        // Cek bahwa TIDAK ada route delete / destroy untuk master barang
        $routes = Route::getRoutes();
        $this->assertFalse($routes->hasNamedRoute('admin.barang.destroy'), 'Route admin.barang.destroy tidak boleh ada!');

        foreach ($routes as $route) {
            if (in_array('DELETE', $route->methods()) && str_starts_with($route->uri(), 'admin/barang')) {
                $this->fail('Ditemukan route HTTP DELETE pada path admin/barang!');
            }
        }
    }

    /**
     * 10. Kategori yang dipakai barang tidak bisa dihapus; yang kosong bisa.
     */
    public function test_kategori_yang_dipakai_barang_tidak_bisa_dihapus_yang_kosong_bisa(): void
    {
        $kategoriPakai = Kategori::create([
            'kios_id' => $this->kiosA->id,
            'nama' => 'Sembako Beras',
        ]);

        Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $kategoriPakai->id,
            'kode' => 'BRG-KAT-1',
            'nama' => 'Beras 5kg',
            'satuan' => 'karung',
            'harga_modal' => 60000,
            'harga_jual' => 68000,
            'stok' => 10,
            'stok_minimum' => 2,
            'is_aktif' => true,
        ]);

        $kategoriKosong = Kategori::create([
            'kios_id' => $this->kiosA->id,
            'nama' => 'Kategori Kosong',
        ]);

        $this->actingAs($this->adminA);

        // Hapus kategori yang masih dipakai -> harus ditolak
        $responseTolak = $this->delete(route('admin.kategori.destroy', $kategoriPakai));
        $responseTolak->assertSessionHas('error');
        $this->assertNotNull($kategoriPakai->fresh());

        // Hapus kategori kosong -> harus berhasil
        $responseBisa = $this->delete(route('admin.kategori.destroy', $kategoriKosong));
        $responseBisa->assertSessionHas('success');
        $this->assertNull($kategoriKosong->fresh());
    }

    /**
     * 11. Kasir mendapat 403 di /admin/barang dan /admin/kategori.
     */
    public function test_kasir_mendapat_403_di_admin_barang_dan_kategori(): void
    {
        $this->actingAs($this->kasirA);

        $this->get('/admin/barang')->assertForbidden();
        $this->get('/admin/barang/create')->assertForbidden();
        $this->post('/admin/barang', [])->assertForbidden();

        $this->get('/admin/kategori')->assertForbidden();
        $this->post('/admin/kategori', [])->assertForbidden();
    }

    /**
     * 12. Kartu dan lonceng stok menipis menghitung dengan benar dan hanya untuk kios sendiri.
     */
    public function test_kartu_dan_lonceng_stok_menipis_menghitung_benar_dan_hanya_kios_sendiri(): void
    {
        // Kios A: 2 barang aktif menipis, 1 barang nonaktif menipis, 1 barang aman
        Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-A-MENIPIS-1',
            'nama' => 'Item Menipis 1 Kios A',
            'satuan' => 'pcs',
            'harga_modal' => 1000,
            'harga_jual' => 2000,
            'stok' => 2,
            'stok_minimum' => 5, // Stok 2 <= 5 -> MENIPIS
            'is_aktif' => true,
        ]);
        Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-A-HABIS-2',
            'nama' => 'Item Habis 2 Kios A',
            'satuan' => 'pcs',
            'harga_modal' => 1000,
            'harga_jual' => 2000,
            'stok' => 0,
            'stok_minimum' => 10, // Stok 0 <= 10 -> MENIPIS / HABIS
            'is_aktif' => true,
        ]);
        Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-A-NONAKTIF',
            'nama' => 'Item Nonaktif Kios A',
            'satuan' => 'pcs',
            'harga_modal' => 1000,
            'harga_jual' => 2000,
            'stok' => 0,
            'stok_minimum' => 10,
            'is_aktif' => false, // Nonaktif tidak dihitung sebagai alert operasional
        ]);
        Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-A-AMAN',
            'nama' => 'Item Aman Kios A',
            'satuan' => 'pcs',
            'harga_modal' => 1000,
            'harga_jual' => 2000,
            'stok' => 50,
            'stok_minimum' => 5, // Stok 50 > 5 -> AMAN
            'is_aktif' => true,
        ]);

        // Kios B: 4 barang menipis
        for ($i = 1; $i <= 4; $i++) {
            Barang::create([
                'kios_id' => $this->kiosB->id,
                'kode' => "BRG-B-MENIPIS-{$i}",
                'nama' => "Item Menipis {$i} Kios B",
                'satuan' => 'pcs',
                'harga_modal' => 1000,
                'harga_jual' => 2000,
                'stok' => 1,
                'stok_minimum' => 5,
                'is_aktif' => true,
            ]);
        }

        // Kios A login: harus mendapatkan tepat 2
        $responseA = $this->actingAs($this->adminA)->get(route('admin.dashboard'));
        $responseA->assertOk();
        $this->assertEquals(2, $responseA->viewData('barangMenipisCount'));
        $this->assertCount(2, $responseA->viewData('barangMenipisList'));

        // Kios B login: harus mendapatkan tepat 4
        $this->flushSession();
        $responseB = $this->actingAs($this->adminB)->get(route('admin.dashboard'));
        $responseB->assertOk();
        $this->assertEquals(4, $responseB->viewData('barangMenipisCount'));
        $this->assertCount(4, $responseB->viewData('barangMenipisList'));
    }

    /**
     * 13. Filter pencarian, kategori, status, dan stok menipis bekerja benar.
     */
    public function test_filter_pencarian_kategori_status_dan_stok_menipis(): void
    {
        $katSembako = Kategori::create(['kios_id' => $this->kiosA->id, 'nama' => 'Sembako']);
        $katMinuman = Kategori::create(['kios_id' => $this->kiosA->id, 'nama' => 'Minuman']);

        Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $katSembako->id,
            'kode' => 'BRG-FILTER-1',
            'nama' => 'Beras Maknyus 5kg',
            'satuan' => 'karung',
            'harga_modal' => 60000,
            'harga_jual' => 70000,
            'stok' => 20,
            'stok_minimum' => 5,
            'is_aktif' => true,
        ]);

        Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $katMinuman->id,
            'kode' => 'BRG-FILTER-2',
            'nama' => 'Teh Botol Kotak',
            'satuan' => 'kotak',
            'harga_modal' => 3000,
            'harga_jual' => 4500,
            'stok' => 2,
            'stok_minimum' => 10, // Menipis
            'is_aktif' => true,
        ]);

        Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $katMinuman->id,
            'kode' => 'BRG-FILTER-3',
            'nama' => 'Kopi Kaleng Beku',
            'satuan' => 'kaleng',
            'harga_modal' => 5000,
            'harga_jual' => 7000,
            'stok' => 10,
            'stok_minimum' => 5,
            'is_aktif' => false, // Nonaktif
        ]);

        $this->actingAs($this->adminA);

        // 1. Pencarian q=Beras
        $resSearch = $this->get(route('admin.barang.index', ['q' => 'Beras']));
        $this->assertEquals(1, $resSearch->viewData('barangList')->total());

        // 2. Filter kategori_id
        $resKat = $this->get(route('admin.barang.index', ['kategori_id' => $katMinuman->id]));
        $this->assertEquals(2, $resKat->viewData('barangList')->total());

        // 3. Filter status=nonaktif
        $resStatus = $this->get(route('admin.barang.index', ['status' => 'nonaktif']));
        $this->assertEquals(1, $resStatus->viewData('barangList')->total());

        // 4. Filter stok menipis
        $resMenipis = $this->get(route('admin.barang.index', ['menipis' => '1']));
        $this->assertEquals(1, $resMenipis->viewData('barangList')->total());
    }
}
