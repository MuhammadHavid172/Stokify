<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\DetailPenyesuaianStok;
use App\Models\Kategori;
use App\Models\Kios;
use App\Models\LogAktivitas;
use App\Models\MutasiStok;
use App\Models\PenyesuaianStok;
use App\Models\User;
use App\Services\PenyesuaianStokService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TahapEnamTest extends TestCase
{
    use RefreshDatabase;

    protected Kios $kiosA;
    protected Kios $kiosB;
    protected User $adminA;
    protected User $kasirA;
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
            'nama' => 'Telur Ayam 1kg',
            'satuan' => 'kg',
            'harga_modal' => 25000,
            'harga_jual' => 28000,
            'stok' => 10,
            'stok_minimum' => 2,
            'is_aktif' => true,
        ]);

        $this->barangAOff = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kategori_id' => $katA->id,
            'kode' => 'BRG-AOFF',
            'nama' => 'Barang Nonaktif A',
            'satuan' => 'pcs',
            'harga_modal' => 10000,
            'harga_jual' => 15000,
            'stok' => 8,
            'stok_minimum' => 1,
            'is_aktif' => false, // Nonaktif
        ]);

        // 4. Barang Kios B
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
            'stok' => 20,
            'stok_minimum' => 2,
            'is_aktif' => true,
        ]);
    }

    /**
     * Test 1: Penyesuaian jenis rusak/hilang/kedaluwarsa mengurangi stok, membuat mutasi_stok, total_kerugian benar.
     */
    public function test_penyesuaian_jenis_rusak_hilang_kedaluwarsa_mengurangi_stok_mutasi_terbentuk_dan_total_kerugian_benar(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post(route('admin.penyesuaian.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'catatan' => 'Pembersihan rak display sembako',
            'items' => [
                [
                    'barang_id' => $this->barangA1->id,
                    'jenis' => 'rusak',
                    'arah' => 'kurang',
                    'jumlah' => 2,
                    'alasan' => 'Karung beras sobek terkena paku',
                ],
                [
                    'barang_id' => $this->barangA2->id,
                    'jenis' => 'kedaluwarsa',
                    'arah' => 'kurang',
                    'jumlah' => 3,
                    'alasan' => 'Telur busuk melewati tanggal simpan',
                ],
            ],
        ]);

        $penyesuaian = PenyesuaianStok::withoutGlobalScopes()->where('kios_id', $this->kiosA->id)->first();
        $this->assertNotNull($penyesuaian);
        $response->assertRedirect(route('admin.penyesuaian.show', $penyesuaian));

        // Stok barang berkurang
        $this->assertEquals(13, $this->barangA1->fresh()->stok); // 15 - 2
        $this->assertEquals(7, $this->barangA2->fresh()->stok);   // 10 - 3

        // Total kerugian: (2 * 50.000) + (3 * 25.000) = 100.000 + 75.000 = 175.000
        $this->assertEquals(175000, $penyesuaian->total_kerugian);

        // Mutasi stok bertipe penyesuaian terbentuk dengan jumlah bertanda negatif
        $this->assertDatabaseHas('mutasi_stok', [
            'kios_id' => $this->kiosA->id,
            'barang_id' => $this->barangA1->id,
            'tipe' => 'penyesuaian',
            'jumlah' => -2,
            'stok_sebelum' => 15,
            'stok_sesudah' => 13,
            'referensi_type' => PenyesuaianStok::class,
            'referensi_id' => $penyesuaian->id,
        ]);

        $this->assertDatabaseHas('mutasi_stok', [
            'kios_id' => $this->kiosA->id,
            'barang_id' => $this->barangA2->id,
            'tipe' => 'penyesuaian',
            'jumlah' => -3,
            'stok_sebelum' => 10,
            'stok_sesudah' => 7,
            'referensi_type' => PenyesuaianStok::class,
            'referensi_id' => $penyesuaian->id,
        ]);
    }

    /**
     * Test 2: Jenis rusak/hilang/kedaluwarsa dengan arah tambah ditolak.
     */
    public function test_jenis_rusak_hilang_kedaluwarsa_dengan_arah_tambah_ditolak(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post(route('admin.penyesuaian.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                [
                    'barang_id' => $this->barangA1->id,
                    'jenis' => 'rusak',
                    'arah' => 'tambah', // Tidak boleh!
                    'jumlah' => 2,
                    'alasan' => 'Mencoba menambah barang rusak',
                ],
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(15, $this->barangA1->fresh()->stok);
    }

    /**
     * Test 3: Koreksi_lain dapat menambah maupun mengurangi stok.
     */
    public function test_koreksi_lain_dapat_menambah_maupun_mengurangi_stok(): void
    {
        $this->actingAs($this->adminA);

        $this->post(route('admin.penyesuaian.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                [
                    'barang_id' => $this->barangA1->id,
                    'jenis' => 'koreksi_lain',
                    'arah' => 'tambah',
                    'jumlah' => 5,
                    'alasan' => 'Ditemukan stok terselip di bawah rak',
                ],
                [
                    'barang_id' => $this->barangA2->id,
                    'jenis' => 'koreksi_lain',
                    'arah' => 'kurang',
                    'jumlah' => 2,
                    'alasan' => 'Koreksi selisih hitung kasir',
                ],
            ],
        ]);

        $penyesuaian = PenyesuaianStok::withoutGlobalScopes()->where('kios_id', $this->kiosA->id)->first();

        // Stok A1 bertambah (+5), Stok A2 berkurang (-2)
        $this->assertEquals(20, $this->barangA1->fresh()->stok); // 15 + 5
        $this->assertEquals(8, $this->barangA2->fresh()->stok);   // 10 - 2

        // Total kerugian HANYA dari barang yang berkurang: 2 * 25.000 = 50.000
        $this->assertEquals(50000, $penyesuaian->total_kerugian);
    }

    /**
     * Test 4: Penyesuaian yang membuat stok negatif ditolak dan tidak mengubah apa pun (rollback total).
     */
    public function test_penyesuaian_yang_membuat_stok_negatif_ditolak_dan_rollback_total(): void
    {
        $this->actingAs($this->adminA);

        $stokAwalA1 = $this->barangA1->stok; // 15
        $stokAwalA2 = $this->barangA2->stok; // 10

        // Mencoba mengurangi barang A2 sebanyak 15 (melebihi stok 10)
        $response = $this->post(route('admin.penyesuaian.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                [
                    'barang_id' => $this->barangA1->id,
                    'jenis' => 'rusak',
                    'arah' => 'kurang',
                    'jumlah' => 2,
                    'alasan' => 'Kemasan robek',
                ],
                [
                    'barang_id' => $this->barangA2->id,
                    'jenis' => 'hilang',
                    'arah' => 'kurang',
                    'jumlah' => 15, // Gagal!
                    'alasan' => 'Hilang melebihi stok',
                ],
            ],
        ]);

        $response->assertSessionHas('error');

        // Pastikan rollback total: barang A1 juga tidak berubah
        $this->assertEquals($stokAwalA1, $this->barangA1->fresh()->stok);
        $this->assertEquals($stokAwalA2, $this->barangA2->fresh()->stok);
        $this->assertEquals(0, PenyesuaianStok::withoutGlobalScopes()->where('kios_id', $this->kiosA->id)->count());
    }

    /**
     * Test 5: Alasan wajib; jumlah 0 atau negatif dari input ditolak.
     */
    public function test_alasan_wajib_dan_jumlah_nol_atau_negatif_dari_input_ditolak(): void
    {
        $this->actingAs($this->adminA);

        // 1. Alasan kosong / <3 karakter
        $response1 = $this->post(route('admin.penyesuaian.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                [
                    'barang_id' => $this->barangA1->id,
                    'jenis' => 'rusak',
                    'arah' => 'kurang',
                    'jumlah' => 1,
                    'alasan' => 'no', // 2 huruf
                ],
            ],
        ]);
        $response1->assertSessionHasErrors('items.0.alasan');

        // 2. Jumlah 0
        $response2 = $this->post(route('admin.penyesuaian.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                [
                    'barang_id' => $this->barangA1->id,
                    'jenis' => 'rusak',
                    'arah' => 'kurang',
                    'jumlah' => 0,
                    'alasan' => 'Barang rusak',
                ],
            ],
        ]);
        $response2->assertSessionHasErrors('items.0.jumlah');
    }

    /**
     * Test 6: Barang kios lain ditolak validasi; admin kios A mendapat 404 pada penyesuaian kios B.
     */
    public function test_barang_kios_lain_ditolak_validasi_dan_admin_kios_a_mendapat_404_pada_penyesuaian_kios_b(): void
    {
        $this->actingAs($this->adminA);

        // Mengirimkan barang milik Kios B
        $response = $this->post(route('admin.penyesuaian.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                [
                    'barang_id' => $this->barangB1->id,
                    'jenis' => 'rusak',
                    'arah' => 'kurang',
                    'jumlah' => 1,
                    'alasan' => 'Penyusupan barang',
                ],
            ],
        ]);
        $response->assertSessionHasErrors('items.0.barang_id');

        // Buat penyesuaian di Kios B
        $this->flushSession();
        $this->actingAs($this->adminB);
        $this->post(route('admin.penyesuaian.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                [
                    'barang_id' => $this->barangB1->id,
                    'jenis' => 'rusak',
                    'arah' => 'kurang',
                    'jumlah' => 2,
                    'alasan' => 'Pecah di Kios B',
                ],
            ],
        ]);
        $penyesuaianB = PenyesuaianStok::withoutGlobalScopes()->where('kios_id', $this->kiosB->id)->first();

        // Admin A mencoba mengakses penyesuaian B -> 404
        $this->flushSession();
        $this->actingAs($this->adminA);
        $this->get(route('admin.penyesuaian.show', $penyesuaianB))->assertStatus(404);
    }

    /**
     * Test 7: Barang duplikat dalam satu form ditolak.
     */
    public function test_barang_duplikat_dalam_satu_form_ditolak(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post(route('admin.penyesuaian.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                [
                    'barang_id' => $this->barangA1->id,
                    'jenis' => 'rusak',
                    'arah' => 'kurang',
                    'jumlah' => 1,
                    'alasan' => 'Baris pertama',
                ],
                [
                    'barang_id' => $this->barangA1->id, // Duplikat
                    'jenis' => 'hilang',
                    'arah' => 'kurang',
                    'jumlah' => 1,
                    'alasan' => 'Baris kedua duplikat',
                ],
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('tidak boleh muncul lebih dari satu kali', session('error'));
    }

    /**
     * Test 8: Barang nonaktif tetap bisa disesuaikan.
     */
    public function test_barang_nonaktif_tetap_bisa_disesuaikan(): void
    {
        $this->actingAs($this->adminA);
        $this->assertFalse($this->barangAOff->is_aktif);

        $this->post(route('admin.penyesuaian.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                [
                    'barang_id' => $this->barangAOff->id,
                    'jenis' => 'kedaluwarsa',
                    'arah' => 'kurang',
                    'jumlah' => 3,
                    'alasan' => 'Barang nonaktif di gudang sudah kedaluwarsa',
                ],
            ],
        ]);

        $this->assertEquals(5, $this->barangAOff->fresh()->stok); // 8 - 3 = 5
    }

    /**
     * Test 9: Snapshot stok_sebelum, stok_sesudah, harga_modal benar dan tidak berubah saat modal diubah kemudian.
     */
    public function test_snapshot_stok_sebelum_stok_sesudah_harga_modal_benar_dan_tidak_berubah_saat_modal_berubah(): void
    {
        $this->actingAs($this->adminA);

        $this->post(route('admin.penyesuaian.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                [
                    'barang_id' => $this->barangA1->id,
                    'jenis' => 'rusak',
                    'arah' => 'kurang',
                    'jumlah' => 5,
                    'alasan' => 'Kardus basah terkena bocor atap',
                ],
            ],
        ]);

        $detail = DetailPenyesuaianStok::withoutGlobalScopes()->where('kios_id', $this->kiosA->id)->first();
        $this->assertEquals(15, $detail->stok_sebelum);
        $this->assertEquals(10, $detail->stok_sesudah);
        $this->assertEquals(50000, $detail->harga_modal);
        $this->assertEquals(-250000, $detail->nilai); // -5 * 50.000

        // Sekarang ubah harga modal barang secara langsung di database
        $this->barangA1->harga_modal = 75000;
        $this->barangA1->save();

        // Snapshot detail tetap 50.000!
        $this->assertEquals(50000, $detail->fresh()->harga_modal);
        $this->assertEquals(-250000, $detail->fresh()->nilai);
    }

    /**
     * Test 10: Nilai total_kerugian dari input client diabaikan.
     */
    public function test_nilai_total_kerugian_dari_input_client_diabaikan(): void
    {
        $this->actingAs($this->adminA);

        $this->post(route('admin.penyesuaian.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'total_kerugian' => 999, // Client mencoba spoof total
            'items' => [
                [
                    'barang_id' => $this->barangA1->id,
                    'jenis' => 'rusak',
                    'arah' => 'kurang',
                    'jumlah' => 2,
                    'alasan' => 'Kemasan rusak',
                    'nilai' => 10, // Spoofed nilai
                ],
            ],
        ]);

        $penyesuaian = PenyesuaianStok::withoutGlobalScopes()->where('kios_id', $this->kiosA->id)->first();
        // Server menghitung 2 * 50.000 = 100.000
        $this->assertEquals(100000, $penyesuaian->total_kerugian);
        $this->assertEquals(-100000, $penyesuaian->detail()->first()->nilai);
    }

    /**
     * Test 11: Nomor unik per kios dan urut harian; kios lain boleh nomor sama.
     */
    public function test_nomor_unik_per_kios_dan_urut_harian_kios_lain_boleh_nomor_sama(): void
    {
        $todayStr = Carbon::today()->format('Ymd');

        // Nota 1 & 2 Kios A
        $this->actingAs($this->adminA);
        $this->post(route('admin.penyesuaian.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [['barang_id' => $this->barangA1->id, 'jenis' => 'rusak', 'arah' => 'kurang', 'jumlah' => 1, 'alasan' => 'Rusak 1']],
        ]);
        $this->post(route('admin.penyesuaian.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [['barang_id' => $this->barangA1->id, 'jenis' => 'rusak', 'arah' => 'kurang', 'jumlah' => 1, 'alasan' => 'Rusak 2']],
        ]);

        $notasA = PenyesuaianStok::withoutGlobalScopes()->where('kios_id', $this->kiosA->id)->orderBy('id', 'asc')->get();
        $this->assertEquals("PS-{$todayStr}-0001", $notasA[0]->nomor);
        $this->assertEquals("PS-{$todayStr}-0002", $notasA[1]->nomor);

        // Kios B membuat nomor pertama hari ini -> PS-YYYYMMDD-0001
        $this->flushSession();
        $this->actingAs($this->adminB);
        $this->post(route('admin.penyesuaian.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [['barang_id' => $this->barangB1->id, 'jenis' => 'rusak', 'arah' => 'kurang', 'jumlah' => 1, 'alasan' => 'Rusak di B']],
        ]);

        $notaB = PenyesuaianStok::withoutGlobalScopes()->where('kios_id', $this->kiosB->id)->first();
        $this->assertEquals("PS-{$todayStr}-0001", $notaB->nomor);
    }

    /**
     * Test 12: Opname: hanya baris berselisih yang diproses; stok menjadi sama dengan stok fisik.
     */
    public function test_opname_hanya_baris_berselisih_yang_diproses_stok_menjadi_sama_dengan_fisik(): void
    {
        $this->actingAs($this->adminA);

        // A1 stok sistem 15, diisi fisik 12 (selisih -3)
        // A2 stok sistem 10, diisi fisik 10 (sama, harus diabaikan)
        // AOff stok sistem 8, fisik dikosongkan/null (dilewati)
        $this->post(route('admin.penyesuaian.storeOpname'), [
            'tanggal' => Carbon::today()->toDateString(),
            'catatan' => 'Opname sebagian rak',
            'items' => [
                [
                    'barang_id' => $this->barangA1->id,
                    'stok_sistem' => 15,
                    'stok_fisik' => 12,
                    'alasan' => 'Selisih opname fisik',
                ],
                [
                    'barang_id' => $this->barangA2->id,
                    'stok_sistem' => 10,
                    'stok_fisik' => 10,
                    'alasan' => 'Stok cocok',
                ],
                [
                    'barang_id' => $this->barangAOff->id,
                    'stok_sistem' => 8,
                    'stok_fisik' => null, // Tidak dihitung
                    'alasan' => '',
                ],
            ],
        ]);

        $penyesuaian = PenyesuaianStok::withoutGlobalScopes()->where('kios_id', $this->kiosA->id)->first();
        $this->assertEquals('opname', $penyesuaian->mode);

        // Hanya 1 item detail yang dibuat
        $this->assertEquals(1, $penyesuaian->detail()->count());
        $detail = $penyesuaian->detail()->first();
        $this->assertEquals($this->barangA1->id, $detail->barang_id);
        $this->assertEquals('selisih_opname', $detail->jenis);
        $this->assertEquals(-3, $detail->jumlah);
        $this->assertEquals(12, $detail->stok_sesudah);

        // Stok fisik di DB menjadi 12
        $this->assertEquals(12, $this->barangA1->fresh()->stok);
        // Barang A2 dan AOff tidak berubah
        $this->assertEquals(10, $this->barangA2->fresh()->stok);
        $this->assertEquals(8, $this->barangAOff->fresh()->stok);
    }

    /**
     * Test 13: Opname ditolak penuh jika stok sistem berubah sejak halaman dibuka.
     */
    public function test_opname_ditolak_penuh_jika_stok_sistem_berubah_sejak_halaman_dibuka(): void
    {
        $this->actingAs($this->adminA);

        // Simulasikan barang A1 stok di database telah berubah menjadi 14 (misal ada transaksi)
        $this->barangA1->stok = 14;
        $this->barangA1->save();

        // Namun form yang dibuka admin mencatat stok_sistem = 15
        $response = $this->post(route('admin.penyesuaian.storeOpname'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                [
                    'barang_id' => $this->barangA1->id,
                    'stok_sistem' => 15, // Outdated snapshot!
                    'stok_fisik' => 12,
                    'alasan' => 'Selisih opname',
                ],
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('berubah sejak halaman dibuka', session('error'));

        // Tidak ada opname tersimpan, stok tetap 14
        $this->assertEquals(14, $this->barangA1->fresh()->stok);
        $this->assertEquals(0, PenyesuaianStok::withoutGlobalScopes()->where('kios_id', $this->kiosA->id)->count());
    }

    /**
     * Test 14: Opname dengan stok fisik kosong atau sama dengan sistem tidak membuat detail.
     */
    public function test_opname_dengan_stok_fisik_kosong_atau_sama_dengan_sistem_tidak_membuat_detail(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post(route('admin.penyesuaian.storeOpname'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                [
                    'barang_id' => $this->barangA1->id,
                    'stok_sistem' => 15,
                    'stok_fisik' => 15, // Sama
                    'alasan' => '',
                ],
                [
                    'barang_id' => $this->barangA2->id,
                    'stok_sistem' => 10,
                    'stok_fisik' => null, // Kosong
                    'alasan' => '',
                ],
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Tidak ada selisih stok', session('error'));
        $this->assertEquals(0, PenyesuaianStok::withoutGlobalScopes()->where('kios_id', $this->kiosA->id)->count());
    }

    /**
     * Test 15: Tidak ada route update atau destroy untuk penyesuaian.
     */
    public function test_tidak_ada_route_update_atau_destroy_untuk_penyesuaian(): void
    {
        $this->assertFalse(Route::has('admin.penyesuaian.edit'));
        $this->assertFalse(Route::has('admin.penyesuaian.update'));
        $this->assertFalse(Route::has('admin.penyesuaian.destroy'));

        $this->actingAs($this->adminA);
        $this->put('/admin/penyesuaian-stok/1')->assertStatus(405);
        $this->delete('/admin/penyesuaian-stok/1')->assertStatus(405);
    }

    /**
     * Test 16: Kasir mendapat 403 di penyesuaian dan opname.
     */
    public function test_kasir_mendapat_403_di_penyesuaian_dan_opname(): void
    {
        $this->actingAs($this->kasirA);

        $this->get(route('admin.penyesuaian.index'))->assertStatus(403);
        $this->get(route('admin.penyesuaian.create'))->assertStatus(403);
        $this->get(route('admin.penyesuaian.opname'))->assertStatus(403);
    }

    /**
     * Test 17: Log aktivitas tercatat pada penyesuaian manual dan opname.
     */
    public function test_log_aktivitas_tercatat_pada_penyesuaian_manual_dan_opname(): void
    {
        $this->actingAs($this->adminA);

        // 1. Manual
        $this->post(route('admin.penyesuaian.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                [
                    'barang_id' => $this->barangA1->id,
                    'jenis' => 'rusak',
                    'arah' => 'kurang',
                    'jumlah' => 1,
                    'alasan' => 'Uji log aktivitas manual',
                ],
            ],
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'kios_id' => $this->kiosA->id,
            'aksi' => 'penyesuaian.tambah',
        ]);

        // 2. Opname
        $this->post(route('admin.penyesuaian.storeOpname'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                [
                    'barang_id' => $this->barangA2->id,
                    'stok_sistem' => 10,
                    'stok_fisik' => 9,
                    'alasan' => 'Uji log aktivitas opname',
                ],
            ],
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'kios_id' => $this->kiosA->id,
            'aksi' => 'penyesuaian.opname',
        ]);
    }

    /**
     * Test 18: Kartu "Kerugian Stok Bulan Ini" menghitung benar dan hanya untuk kios sendiri.
     */
    public function test_kartu_kerugian_stok_bulan_ini_menghitung_benar_dan_hanya_untuk_kios_sendiri(): void
    {
        // Penyesuaian Kios A: 2 item @ 50.000 = 100.000 kerugian
        $this->actingAs($this->adminA);
        $this->post(route('admin.penyesuaian.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                [
                    'barang_id' => $this->barangA1->id,
                    'jenis' => 'rusak',
                    'arah' => 'kurang',
                    'jumlah' => 2,
                    'alasan' => 'Kerugian Kios A',
                ],
            ],
        ]);

        // Penyesuaian Kios B: 3 item @ 12.000 = 36.000 kerugian
        $this->flushSession();
        $this->actingAs($this->adminB);
        $this->post(route('admin.penyesuaian.store'), [
            'tanggal' => Carbon::today()->toDateString(),
            'items' => [
                [
                    'barang_id' => $this->barangB1->id,
                    'jenis' => 'rusak',
                    'arah' => 'kurang',
                    'jumlah' => 3,
                    'alasan' => 'Kerugian Kios B',
                ],
            ],
        ]);

        // Admin A melihat dashboard: hanya melihat kerugian Kios A (100.000), bukan 136.000
        $this->flushSession();
        $this->actingAs($this->adminA);
        $responseA = $this->get(route('admin.dashboard'));
        $responseA->assertOk();
        $responseA->assertViewHas('kerugianBulanIni', 100000);

        // Admin B melihat dashboard: hanya melihat kerugian Kios B (36.000)
        $this->flushSession();
        $this->actingAs($this->adminB);
        $responseB = $this->get(route('admin.dashboard'));
        $responseB->assertOk();
        $responseB->assertViewHas('kerugianBulanIni', 36000);
    }
}
