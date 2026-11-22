<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Kios;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotifikasiStokDanKodeKategoriTest extends TestCase
{
    use RefreshDatabase;

    protected Kios $kiosA;
    protected Kios $kiosB;
    protected User $adminA;
    protected User $kasirA;
    protected User $adminB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kiosA = Kios::create([
            'nama_kios' => 'Kios Toko Apiid',
            'status' => 'aktif',
        ]);

        $this->adminA = User::create([
            'kios_id' => $this->kiosA->id,
            'name' => 'Admin Apiid',
            'username' => 'admin_apiid',
            'email' => 'admin@apiid.test',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        $this->kasirA = User::create([
            'kios_id' => $this->kiosA->id,
            'name' => 'Kasir Apiid',
            'username' => 'kasir_apiid',
            'email' => null,
            'password' => bcrypt('password123'),
            'role' => 'kasir',
            'is_aktif' => true,
        ]);

        $this->kiosB = Kios::create([
            'nama_kios' => 'Kios Kawan Sebelah',
            'status' => 'aktif',
        ]);

        $this->adminB = User::create([
            'kios_id' => $this->kiosB->id,
            'name' => 'Admin B',
            'username' => 'admin_b',
            'email' => 'admin_b@kios.test',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);
    }

    public function test_kode_otomatis_berdasarkan_awalan_kategori(): void
    {
        $this->actingAs($this->adminA);

        $katRokok = Kategori::create([
            'kios_id' => $this->kiosA->id,
            'nama' => 'Rokok',
        ]);

        $katMinuman = Kategori::create([
            'kios_id' => $this->kiosA->id,
            'nama' => 'Minuman Dingin',
        ]);

        // Cek prefix kategori
        $this->assertEquals('ROK', $katRokok->getPrefixKode());
        $this->assertEquals('MNM', $katMinuman->getPrefixKode());

        // Test Endpoint Generate Kode untuk Rokok
        $response = $this->getJson(route('admin.barang.generate_kode', ['kategori_id' => $katRokok->id]));
        $response->assertOk()
            ->assertJson([
                'kode' => 'ROK-0001',
                'prefix' => 'ROK',
            ]);

        // Buat satu barang rokok
        Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $katRokok->id,
            'kode' => 'ROK-0001',
            'nama' => 'Surya 16',
            'satuan' => 'bungkus',
            'harga_modal' => 30000,
            'harga_jual' => 33000,
            'stok' => 10,
            'stok_minimum' => 2,
            'is_aktif' => true,
        ]);

        // Kode rokok selanjutnya harus ROK-0002
        $responseNext = $this->getJson(route('admin.barang.generate_kode', ['kategori_id' => $katRokok->id]));
        $responseNext->assertOk()
            ->assertJson([
                'kode' => 'ROK-0002',
                'prefix' => 'ROK',
            ]);

        // Kategori minuman harus mulai dari MNM-0001
        $responseMinuman = $this->getJson(route('admin.barang.generate_kode', ['kategori_id' => $katMinuman->id]));
        $responseMinuman->assertOk()
            ->assertJson([
                'kode' => 'MNM-0001',
                'prefix' => 'MNM',
            ]);

        // Tanpa kategori harus BRG-0001
        $responseDefault = $this->getJson(route('admin.barang.generate_kode'));
        $responseDefault->assertOk()
            ->assertJson([
                'kode' => 'BRG-0001',
                'prefix' => 'BRG',
            ]);
    }

    public function test_admin_dapat_mengakses_notifikasi_stok_kios_sendiri(): void
    {
        // Barang 1: Menipis (stok <= stok_min)
        Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-0001',
            'nama' => 'Minyak Goreng 2L',
            'satuan' => 'botol',
            'harga_modal' => 30000,
            'harga_jual' => 35000,
            'stok' => 2,
            'stok_minimum' => 5,
            'is_aktif' => true,
        ]);

        // Barang 2: Habis (stok 0)
        Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-0002',
            'nama' => 'Beras Pandan Wangi 5kg',
            'satuan' => 'karung',
            'harga_modal' => 65000,
            'harga_jual' => 74000,
            'stok' => 0,
            'stok_minimum' => 3,
            'is_aktif' => true,
        ]);

        // Barang 3: Aman (stok > stok_min)
        Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-0003',
            'nama' => 'Gula Pasir 1kg',
            'satuan' => 'kg',
            'harga_modal' => 14000,
            'harga_jual' => 17000,
            'stok' => 50,
            'stok_minimum' => 5,
            'is_aktif' => true,
        ]);

        $this->actingAs($this->adminA);
        $resAdmin = $this->getJson(route('notifikasi.stok'));
        $resAdmin->assertOk()
            ->assertJson(['total' => 2])
            ->assertJsonCount(2, 'items');
    }

    public function test_kasir_dapat_mengakses_notifikasi_stok_tanpa_bocor_harga_modal(): void
    {
        Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-0001',
            'nama' => 'Minyak Goreng 2L',
            'satuan' => 'botol',
            'harga_modal' => 30000,
            'harga_jual' => 35000,
            'stok' => 2,
            'stok_minimum' => 5,
            'is_aktif' => true,
        ]);

        $this->actingAs($this->kasirA);
        $resKasir = $this->getJson(route('notifikasi.stok'));
        $resKasir->assertOk()
            ->assertJson(['total' => 1])
            ->assertJsonMissing(['harga_modal'])
            ->assertJsonMissing(['laba']);
    }

    public function test_isolasi_tenant_notifikasi_stok_antar_kios(): void
    {
        // Barang di Kios A menipis
        Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-0001',
            'nama' => 'Minyak Goreng 2L',
            'satuan' => 'botol',
            'harga_modal' => 30000,
            'harga_jual' => 35000,
            'stok' => 1,
            'stok_minimum' => 5,
            'is_aktif' => true,
        ]);

        // Admin B tidak boleh melihat notifikasi Kios A
        $this->actingAs($this->adminB);
        $resAdminB = $this->getJson(route('notifikasi.stok'));
        $resAdminB->assertOk()
            ->assertJson(['total' => 0])
            ->assertJsonCount(0, 'items');
    }

    public function test_notifikasi_lonceng_muncul_di_view_admin(): void
    {
        $this->actingAs($this->adminA);
        $resViewAdmin = $this->get(route('admin.dashboard'));
        $resViewAdmin->assertOk()
            ->assertSee('Peringatan Stok');
    }

    public function test_notifikasi_lonceng_muncul_di_view_kasir(): void
    {
        $this->actingAs($this->kasirA);
        $resViewKasir = $this->get(route('kasir.index'));
        $resViewKasir->assertOk()
            ->assertSee('Peringatan Stok');
    }
}
