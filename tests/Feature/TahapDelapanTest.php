<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Kios;
use App\Models\LogAktivitas;
use App\Models\MutasiStok;
use App\Models\Penjualan;
use App\Models\ShiftKasir;
use App\Models\User;
use App\Services\PenjualanService;
use App\Services\ShiftService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TahapDelapanTest extends TestCase
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
    protected Barang $barangB1;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Kios A & Pengguna
        $this->kiosA = Kios::create([
            'nama_kios' => 'Kios Maju A',
            'status' => 'aktif',
        ]);

        $this->adminA = User::create([
            'kios_id' => $this->kiosA->id,
            'name' => 'Admin Maju',
            'username' => 'admin_maju_8',
            'email' => 'admin_maju_8@kios.test',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        $this->kasirA = User::create([
            'kios_id' => $this->kiosA->id,
            'name' => 'Kasir Maju 1',
            'username' => 'kasir_maju_8_1',
            'email' => null,
            'password' => bcrypt('password123'),
            'role' => 'kasir',
            'is_aktif' => true,
        ]);

        $this->kasirA2 = User::create([
            'kios_id' => $this->kiosA->id,
            'name' => 'Kasir Maju 2',
            'username' => 'kasir_maju_8_2',
            'email' => null,
            'password' => bcrypt('password123'),
            'role' => 'kasir',
            'is_aktif' => true,
        ]);

        // 2. Kios B & Pengguna
        $this->kiosB = Kios::create([
            'nama_kios' => 'Kios Sejahtera B',
            'status' => 'aktif',
        ]);

        $this->adminB = User::create([
            'kios_id' => $this->kiosB->id,
            'name' => 'Admin Sejahtera',
            'username' => 'admin_sej_8',
            'email' => 'admin_sej_8@kios.test',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        $this->kasirB = User::create([
            'kios_id' => $this->kiosB->id,
            'name' => 'Kasir Sejahtera',
            'username' => 'kasir_sej_8',
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
            'stok' => 20,
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
            'harga_jual' => 18000,
            'stok' => 30,
            'stok_minimum' => 5,
            'is_aktif' => true,
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
            'harga_jual' => 15000,
            'stok' => 25,
            'stok_minimum' => 5,
            'is_aktif' => true,
        ]);
    }

    /**
     * Test 1: Buka shift berhasil: status berjalan, user_aktif terisi, modal awal tersimpan
     */
    public function test_01_buka_shift_berhasil(): void
    {
        $this->actingAs($this->kasirA);

        $response = $this->post(route('kasir.shift.buka'), [
            'modal_awal' => 150000,
        ]);

        $response->assertRedirect(route('kasir.index'));

        $shift = ShiftKasir::where('user_id', $this->kasirA->id)->first();
        $this->assertNotNull($shift);
        $this->assertEquals('berjalan', $shift->status);
        $this->assertEquals($this->kasirA->id, $shift->user_aktif);
        $this->assertEquals(150000, $shift->modal_awal);
        $this->assertNotNull($shift->waktu_buka);
        $this->assertNull($shift->waktu_tutup);
    }

    /**
     * Test 2: Satu pengguna tidak bisa punya dua shift berjalan (termasuk penanganan unique constraint)
     */
    public function test_02_satu_pengguna_tidak_bisa_punya_dua_shift_berjalan(): void
    {
        $shiftService = app(ShiftService::class);
        $shiftService->buka($this->kasirA, 100000);

        // Percobaan buka shift kedua harus melempar ValidationException
        $this->expectException(ValidationException::class);
        $shiftService->buka($this->kasirA, 50000);
    }

    /**
     * Test 3: Transaksi tanpa shift berjalan ditolak; transaksi dengan shift berjalan mengisi shift_id
     */
    public function test_03_transaksi_tanpa_shift_berjalan_ditolak_dan_transaksi_dengan_shift_mengisi_shift_id(): void
    {
        $penjualanService = app(PenjualanService::class);

        // Tanpa shift berjalan -> ditolak
        try {
            $penjualanService->buat(
                items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
                bayar: 100000,
                token: 'tok-no-shift',
                user: $this->kasirA
            );
            $this->fail('Seharusnya gagal karena belum buka shift');
        } catch (ValidationException $e) {
            $this->assertTrue(str_contains(json_encode($e->errors()), 'Buka shift terlebih dahulu'));
        }

        // Buka shift
        $shiftService = app(ShiftService::class);
        $shift = $shiftService->buka($this->kasirA, 100000);

        // Transaksi berhasil dan shift_id terisi
        $penjualan = $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-with-shift',
            user: $this->kasirA
        );

        $this->assertEquals($shift->id, $penjualan->shift_id);
    }

    /**
     * Test 4: Setelah shift ditutup, transaksi baru ditolak sampai shift baru dibuka
     */
    public function test_04_setelah_shift_ditutup_transaksi_baru_ditolak_sampai_shift_baru_dibuka(): void
    {
        $shiftService = app(ShiftService::class);
        $penjualanService = app(PenjualanService::class);

        $shift1 = $shiftService->buka($this->kasirA, 100000);
        $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-s1',
            user: $this->kasirA
        );

        // Tutup shift 1
        $shiftService->tutup($shift1, 160000, null, $this->kasirA);

        // Coba buat transaksi lagi -> ditolak
        try {
            $penjualanService->buat(
                items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
                bayar: 100000,
                token: 'tok-s1-closed',
                user: $this->kasirA
            );
            $this->fail('Seharusnya gagal karena shift sudah ditutup');
        } catch (ValidationException $e) {
            $this->assertTrue(str_contains(json_encode($e->errors()), 'Buka shift terlebih dahulu'));
        }

        // Buka shift 2
        $shift2 = $shiftService->buka($this->kasirA, 200000);
        $p2 = $penjualanService->buat(
            items: [['barang_id' => $this->barangA2->id, 'jumlah' => 1]],
            bayar: 50000,
            token: 'tok-s2',
            user: $this->kasirA
        );

        $this->assertEquals($shift2->id, $p2->shift_id);
    }

    /**
     * Test 5: Tutup shift: uang_seharusnya = modal_awal + penjualan tunai nota selesai; selisih benar (kurang, pas, lebih)
     */
    public function test_05_tutup_shift_rekonsiliasi_uang_seharusnya_dan_selisih_pas_kurang_lebih(): void
    {
        $shiftService = app(ShiftService::class);
        $penjualanService = app(PenjualanService::class);

        // Kasus 1: Selisih PAS (0)
        $s1 = $shiftService->buka($this->kasirA, 100000);
        $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]], // 60.000
            bayar: 100000,
            token: 'tok-pas',
            user: $this->kasirA
        );
        $shiftService->tutup($s1, 160000, null, $this->kasirA);
        $s1->refresh();
        $this->assertEquals(160000, $s1->uang_seharusnya);
        $this->assertEquals(0, $s1->selisih);

        // Kasus 2: Selisih KURANG (-10.000)
        $s2 = $shiftService->buka($this->kasirA, 100000);
        $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]], // 60.000
            bayar: 100000,
            token: 'tok-kurang',
            user: $this->kasirA
        );
        $shiftService->tutup($s2, 150000, 'Kurang Rp 10.000 karena salah kembalian', $this->kasirA);
        $s2->refresh();
        $this->assertEquals(160000, $s2->uang_seharusnya);
        $this->assertEquals(-10000, $s2->selisih);

        // Kasus 3: Selisih LEBIH (+5.000)
        $s3 = $shiftService->buka($this->kasirA, 100000);
        $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]], // 60.000
            bayar: 100000,
            token: 'tok-lebih',
            user: $this->kasirA
        );
        $shiftService->tutup($s3, 165000, 'Lebih Rp 5.000 dari pembulatan receh', $this->kasirA);
        $s3->refresh();
        $this->assertEquals(160000, $s3->uang_seharusnya);
        $this->assertEquals(5000, $s3->selisih);
    }

    /**
     * Test 6: Nota dibatalkan tidak dihitung dalam uang seharusnya dan jumlah_transaksi
     */
    public function test_06_nota_dibatalkan_tidak_dihitung_dalam_uang_seharusnya_dan_jumlah_transaksi(): void
    {
        $shiftService = app(ShiftService::class);
        $penjualanService = app(PenjualanService::class);

        $shift = $shiftService->buka($this->kasirA, 100000);

        // Nota 1 (Selesai): 60.000
        $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-6-ok',
            user: $this->kasirA
        );

        // Nota 2 (Dibatalkan): 18.000
        $p2 = $penjualanService->buat(
            items: [['barang_id' => $this->barangA2->id, 'jumlah' => 1]],
            bayar: 50000,
            token: 'tok-6-void',
            user: $this->kasirA
        );
        $penjualanService->batalkan($p2, 'Salah input barang oleh kasir', $this->kasirA);

        // Tutup shift
        $shiftService->tutup($shift, 160000, null, $this->kasirA);
        $shift->refresh();

        $this->assertEquals(1, $shift->jumlah_transaksi);
        $this->assertEquals(1, $shift->jumlah_nota_batal);
        $this->assertEquals(60000, $shift->total_penjualan_tunai);
        $this->assertEquals(160000, $shift->uang_seharusnya);
        $this->assertEquals(0, $shift->selisih);
    }

    /**
     * Test 7: Selisih tidak nol tanpa catatan ditolak; selisih nol tanpa catatan diterima
     */
    public function test_07_selisih_tidak_nol_tanpa_catatan_ditolak_selisih_nol_tanpa_catatan_diterima(): void
    {
        $shiftService = app(ShiftService::class);
        $shift = $shiftService->buka($this->kasirA, 100000);

        // Selisih ada (+10.000) tetapi tanpa catatan -> ditolak
        try {
            $shiftService->tutup($shift, 110000, null, $this->kasirA);
            $this->fail('Seharusnya gagal karena ada selisih tanpa catatan');
        } catch (ValidationException $e) {
            $this->assertTrue(str_contains(json_encode($e->errors()), 'Catatan wajib diisi'));
        }

        // Selisih pas (100.000) tanpa catatan -> diterima
        $shiftService->tutup($shift, 100000, null, $this->kasirA);
        $shift->refresh();
        $this->assertEquals('ditutup', $shift->status);
    }

    /**
     * Test 8: Angka shift dari input client diabaikan
     */
    public function test_08_angka_shift_dari_input_client_diabaikan(): void
    {
        $shiftService = app(ShiftService::class);
        $shift = $shiftService->buka($this->kasirA, 100000);

        $this->actingAs($this->kasirA);

        // Client mengirim data palsu
        $response = $this->post(route('kasir.shift.tutup.post'), [
            'uang_fisik' => 100000,
            'uang_seharusnya' => 999999999,
            'selisih' => -88888888,
            'jumlah_transaksi' => 999,
            'total_penjualan_tunai' => 999999999,
        ]);

        $response->assertRedirect(route('kasir.shift.rekap', $shift));

        $shift->refresh();
        $this->assertEquals(100000, $shift->uang_seharusnya);
        $this->assertEquals(0, $shift->selisih);
        $this->assertEquals(0, $shift->jumlah_transaksi);
        $this->assertEquals(0, $shift->total_penjualan_tunai);
    }

    /**
     * Test 9: Shift yang sudah ditutup tidak bisa ditutup lagi; snapshot tidak berubah
     */
    public function test_09_shift_yang_sudah_ditutup_tidak_bisa_ditutup_lagi_dan_snapshot_tidak_berubah(): void
    {
        $shiftService = app(ShiftService::class);
        $shift = $shiftService->buka($this->kasirA, 100000);
        $shiftService->tutup($shift, 100000, null, $this->kasirA);

        $shift->refresh();
        $waktuTutupAsli = $shift->waktu_tutup;

        // Coba tutup lagi
        try {
            $shiftService->tutup($shift, 150000, 'Tutup lagi coba-coba', $this->kasirA);
            $this->fail('Seharusnya gagal menutup shift yang sudah berstatus ditutup');
        } catch (ValidationException $e) {
            $this->assertTrue(str_contains(json_encode($e->errors()), 'sudah ditutup'));
        }

        $shift->refresh();
        $this->assertEquals($waktuTutupAsli->toDateTimeString(), $shift->waktu_tutup->toDateTimeString());
        $this->assertEquals(100000, $shift->uang_fisik);
    }

    /**
     * Test 10: Void: status berubah, stok kembali, mutasi_stok bertipe void_penjualan positif terbentuk, detail dan snapshot harga tidak berubah
     */
    public function test_10_void_status_berubah_stok_kembali_mutasi_void_penjualan_terbentuk_detail_tetap(): void
    {
        $shiftService = app(ShiftService::class);
        $penjualanService = app(PenjualanService::class);

        $shift = $shiftService->buka($this->kasirA, 100000);
        $stokAwal = $this->barangA1->stok; // 20

        // Jual 3 pcs
        $penjualan = $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 3]],
            bayar: 200000,
            token: 'tok-void-10',
            user: $this->kasirA
        );

        $this->assertEquals($stokAwal - 3, $this->barangA1->fresh()->stok); // 17

        // Batalkan (Void)
        $penjualanService->batalkan($penjualan, 'Pelanggan membatalkan pesanan beras', $this->kasirA);

        $penjualan->refresh();
        $this->assertEquals('dibatalkan', $penjualan->status);
        $this->assertEquals($this->kasirA->id, $penjualan->dibatalkan_oleh);
        $this->assertNotNull($penjualan->dibatalkan_pada);
        $this->assertEquals('Pelanggan membatalkan pesanan beras', $penjualan->alasan_batal);

        // Stok kembali utuh
        $this->assertEquals($stokAwal, $this->barangA1->fresh()->stok);

        // Mutasi bertipe void_penjualan positif terbentuk
        $mutasi = MutasiStok::where('barang_id', $this->barangA1->id)
            ->where('tipe', 'void_penjualan')
            ->first();
        $this->assertNotNull($mutasi);
        $this->assertEquals(3, $mutasi->jumlah);
        $this->assertEquals($stokAwal, $mutasi->stok_sesudah);

        // Snapshot harga tidak berubah
        $detail = $penjualan->detail()->first();
        $this->assertEquals(60000, $detail->harga_jual);
        $this->assertEquals(50000, $detail->harga_modal);
    }

    /**
     * Test 11: Void tanpa alasan atau alasan kurang dari 5 karakter ditolak
     */
    public function test_11_void_tanpa_alasan_atau_alasan_kurang_dari_5_karakter_ditolak(): void
    {
        $shiftService = app(ShiftService::class);
        $penjualanService = app(PenjualanService::class);

        $shiftService->buka($this->kasirA, 100000);
        $penjualan = $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-alasan-pendek',
            user: $this->kasirA
        );

        // Alasan kosong atau < 5 karakter via HTTP
        $this->actingAs($this->kasirA);
        $response = $this->post(route('kasir.penjualan.batal', $penjualan), [
            'alasan' => 'abc',
        ]);

        $response->assertSessionHasErrors('alasan');
        $this->assertEquals('selesai', $penjualan->fresh()->status);
    }

    /**
     * Test 12: Void ditolak jika nota sudah dibatalkan dan jika shift sudah ditutup
     */
    public function test_12_void_ditolak_jika_nota_sudah_dibatalkan_dan_jika_shift_sudah_ditutup(): void
    {
        $shiftService = app(ShiftService::class);
        $penjualanService = app(PenjualanService::class);

        $shift = $shiftService->buka($this->kasirA, 100000);
        $penjualan1 = $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-void-twice',
            user: $this->kasirA
        );

        // Batalkan pertama kali -> sukses
        $penjualanService->batalkan($penjualan1, 'Pembatalan valid pertama', $this->kasirA);

        // Batalkan kedua kali -> ditolak
        try {
            $penjualanService->batalkan($penjualan1, 'Pembatalan kedua kali', $this->kasirA);
            $this->fail('Seharusnya gagal membatalkan nota yang sudah berstatus batal');
        } catch (ValidationException $e) {
            $this->assertTrue(str_contains(json_encode($e->errors()), 'sudah dibatalkan'));
        }

        // Buat nota 2, lalu tutup shift
        $penjualan2 = $penjualanService->buat(
            items: [['barang_id' => $this->barangA2->id, 'jumlah' => 1]],
            bayar: 50000,
            token: 'tok-void-after-close',
            user: $this->kasirA
        );
        $shiftService->tutup($shift, 118000, null, $this->kasirA);

        // Coba batalkan nota setelah shift ditutup -> ditolak
        try {
            $penjualanService->batalkan($penjualan2, 'Batalkan nota di shift yang sudah tutup', $this->kasirA);
            $this->fail('Seharusnya gagal membatalkan nota dari shift yang sudah ditutup');
        } catch (ValidationException $e) {
            $this->assertTrue(str_contains(json_encode($e->errors()), 'Shift sudah ditutup'));
        }
    }

    /**
     * Test 13: Kasir tidak bisa membatalkan nota kasir lain; admin bisa membatalkan nota kasir selama shift berjalan
     */
    public function test_13_kasir_tidak_bisa_batalkan_nota_kasir_lain_admin_bisa_batalkan_nota_kasir_selama_shift_berjalan(): void
    {
        $shiftService = app(ShiftService::class);
        $penjualanService = app(PenjualanService::class);

        // Kasir A buka shift dan buat transaksi
        $shiftA = $shiftService->buka($this->kasirA, 100000);
        $penjualan = $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-kasir-a',
            user: $this->kasirA
        );

        // Kasir A2 mencoba membatalkan nota Kasir A -> 403 Forbidden
        $this->actingAs($this->kasirA2);
        $response = $this->post(route('kasir.penjualan.batal', $penjualan), [
            'alasan' => 'Kasir lain mencoba membatalkan nota',
        ]);
        $response->assertForbidden();

        // Admin A membatalkan nota Kasir A saat shift masih berjalan -> Sukses
        $this->flushSession();
        $this->actingAs($this->adminA);
        $responseAdmin = $this->post(route('admin.penjualan.batal', $penjualan), [
            'alasan' => 'Dibatalkan oleh admin atas persetujuan kasir',
        ]);
        $responseAdmin->assertRedirect(route('admin.penjualan.show', $penjualan));

        $penjualan->refresh();
        $this->assertEquals('dibatalkan', $penjualan->status);
        $this->assertEquals($this->adminA->id, $penjualan->dibatalkan_oleh);
    }

    /**
     * Test 14: Void tetap mengembalikan stok untuk barang yang sudah dinonaktifkan
     */
    public function test_14_void_tetap_mengembalikan_stok_untuk_barang_yang_sudah_dinonaktifkan(): void
    {
        $shiftService = app(ShiftService::class);
        $penjualanService = app(PenjualanService::class);

        $shift = $shiftService->buka($this->kasirA, 100000);
        $penjualan = $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 2]],
            bayar: 150000,
            token: 'tok-deactivated-item',
            user: $this->kasirA
        );

        $stokSetelahJual = $this->barangA1->fresh()->stok; // 18

        // Admin menonaktifkan barang
        $this->barangA1->update(['is_aktif' => false]);

        // Kasir membatalkan nota
        $penjualanService->batalkan($penjualan, 'Pelanggan minta ganti barang lain', $this->kasirA);

        $this->assertEquals($stokSetelahJual + 2, $this->barangA1->fresh()->stok); // 20
        $mutasi = MutasiStok::where('barang_id', $this->barangA1->id)
            ->where('tipe', 'void_penjualan')
            ->first();
        $this->assertNotNull($mutasi);
        $this->assertEquals(2, $mutasi->jumlah);
    }

    /**
     * Test 15: Void dan tutup shift bersamaan (uji urutan: tutup dulu, lalu void ditolak)
     */
    public function test_15_void_dan_tutup_shift_bersamaan_tutup_dulu_lalu_void_ditolak(): void
    {
        $shiftService = app(ShiftService::class);
        $penjualanService = app(PenjualanService::class);

        $shift = $shiftService->buka($this->kasirA, 100000);
        $penjualan = $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-race-order',
            user: $this->kasirA
        );

        // Tutup shift terlebih dahulu
        $shiftService->tutup($shift, 160000, null, $this->kasirA);

        // Setelah shift ditutup, aksi pembatalan nota ditolak
        $this->expectException(ValidationException::class);
        $penjualanService->batalkan($penjualan, 'Batalkan setelah shift ditutup', $this->kasirA);
    }

    /**
     * Test 16: Tutup paksa oleh admin: ditutup_paksa true, ditutup_oleh = admin, catatan wajib; admin tidak bisa menutup paksa shift dirinya sendiri lewat jalur ini
     */
    public function test_16_tutup_paksa_oleh_admin_dan_larangan_tutup_paksa_diri_sendiri(): void
    {
        $shiftService = app(ShiftService::class);

        // Shift milik kasir
        $shiftKasir = $shiftService->buka($this->kasirA, 100000);

        // Admin menutup paksa shift kasir tanpa catatan -> ditolak validasi (catatan wajib min 5)
        $this->actingAs($this->adminA);
        $resNoNote = $this->post(route('admin.shift.tutup_paksa', $shiftKasir), [
            'catatan' => 'abc',
        ]);
        $resNoNote->assertSessionHasErrors('catatan');

        // Admin menutup paksa shift kasir dengan catatan valid
        $resOk = $this->post(route('admin.shift.tutup_paksa', $shiftKasir), [
            'uang_fisik' => 100000,
            'catatan' => 'Kasir meninggalkan kios darurat tanpa menutup shift terlebih dahulu.',
        ]);
        $resOk->assertRedirect(route('admin.shift.show', $shiftKasir));

        $shiftKasir->refresh();
        $this->assertEquals('ditutup', $shiftKasir->status);
        $this->assertTrue((bool) $shiftKasir->ditutup_paksa);
        $this->assertEquals($this->adminA->id, $shiftKasir->ditutup_oleh);

        // Shift milik admin sendiri
        $shiftAdmin = $shiftService->buka($this->adminA, 100000);

        // Admin coba tutup paksa shift dirinya sendiri -> ditolak
        $resSelf = $this->post(route('admin.shift.tutup_paksa', $shiftAdmin), [
            'catatan' => 'Mencoba menutup paksa shift diri sendiri',
        ]);
        $resSelf->assertSessionHasErrors('catatan');
        $this->assertEquals('berjalan', $shiftAdmin->fresh()->status);
    }

    /**
     * Test 17: Admin kios A mendapat 404 pada shift dan nota kios B
     */
    public function test_17_admin_kios_a_mendapat_404_pada_shift_dan_nota_kios_b(): void
    {
        $shiftService = app(ShiftService::class);
        $penjualanService = app(PenjualanService::class);

        // Kios B buka shift dan buat transaksi
        $shiftB = $shiftService->buka($this->kasirB, 100000);
        $penjualanB = $penjualanService->buat(
            items: [['barang_id' => $this->barangB1->id, 'jumlah' => 1]],
            bayar: 50000,
            token: 'tok-kios-b',
            user: $this->kasirB
        );

        $this->actingAs($this->adminA);

        // Admin A akses shift B -> 404
        $this->get(route('admin.shift.show', $shiftB))->assertNotFound();

        // Admin A akses penjualan B -> 404
        $this->get(route('admin.penjualan.show', $penjualanB))->assertNotFound();

        // Admin A coba void nota kios B -> 404
        $this->post(route('admin.penjualan.batal', $penjualanB), [
            'alasan' => 'Mencoba membatalkan transaksi kios lain',
        ])->assertNotFound();
    }

    /**
     * Test 18: Kasir mendapat 403 di /admin/shift dan route void admin
     */
    public function test_18_kasir_mendapat_403_di_admin_shift_dan_route_void_admin(): void
    {
        $shiftService = app(ShiftService::class);
        $shift = $shiftService->buka($this->kasirA, 100000);

        $this->actingAs($this->kasirA);

        $this->get(route('admin.shift.index'))->assertForbidden();
        $this->get(route('admin.shift.show', $shift))->assertForbidden();
        $this->post(route('admin.shift.tutup_paksa', $shift), [
            'catatan' => 'Kasir coba tutup paksa',
        ])->assertForbidden();
    }

    /**
     * Test 19: Tidak ada route update atau destroy untuk shift dan penjualan
     */
    public function test_19_tidak_ada_route_update_atau_destroy_untuk_shift_dan_penjualan(): void
    {
        $this->assertFalse(Route::has('admin.shift.edit'));
        $this->assertFalse(Route::has('admin.shift.update'));
        $this->assertFalse(Route::has('admin.shift.destroy'));
        $this->assertFalse(Route::has('kasir.shift.destroy'));
        $this->assertFalse(Route::has('kasir.shift.update'));
        $this->assertFalse(Route::has('admin.penjualan.edit'));
        $this->assertFalse(Route::has('admin.penjualan.update'));
        $this->assertFalse(Route::has('admin.penjualan.destroy'));
    }

    /**
     * Test 20: Log aktivitas tercatat pada shift.buka, shift.tutup, shift.tutup_paksa, penjualan.void
     */
    public function test_20_log_aktivitas_tercatat_lengkap(): void
    {
        $shiftService = app(ShiftService::class);
        $penjualanService = app(PenjualanService::class);

        // 1. shift.buka
        $shift = $shiftService->buka($this->kasirA, 100000);
        $logBuka = LogAktivitas::where('aksi', 'shift.buka')->first();
        $this->assertNotNull($logBuka);

        // 2. penjualan.void
        $penjualan = $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-log-void',
            user: $this->kasirA
        );
        $penjualanService->batalkan($penjualan, 'Alasan pembatalan untuk tes log', $this->kasirA);
        $logVoid = LogAktivitas::where('aksi', 'penjualan.void')->first();
        $this->assertNotNull($logVoid);

        // 3. shift.tutup
        $shiftService->tutup($shift, 100000, null, $this->kasirA);
        $logTutup = LogAktivitas::where('aksi', 'shift.tutup')->first();
        $this->assertNotNull($logTutup);

        // 4. shift.tutup_paksa
        $shift2 = $shiftService->buka($this->kasirA, 50000);
        $shiftService->tutup($shift2, 50000, 'Alasan ditutup paksa admin', $this->adminA, true);
        $logTutupPaksa = LogAktivitas::where('aksi', 'shift.tutup_paksa')->first();
        $this->assertNotNull($logTutupPaksa);
    }

    /**
     * Test 21: Kasir tidak melihat harga modal atau laba di halaman rekap shift dan struk
     */
    public function test_21_kasir_tidak_melihat_harga_modal_atau_laba_di_rekap_shift_dan_struk(): void
    {
        $shiftService = app(ShiftService::class);
        $penjualanService = app(PenjualanService::class);

        $shift = $shiftService->buka($this->kasirA, 100000);
        $penjualan = $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-privacy-test',
            user: $this->kasirA
        );
        $shiftService->tutup($shift, 160000, null, $this->kasirA);

        $this->actingAs($this->kasirA);

        // Cek halaman rekap shift
        $resRekap = $this->get(route('kasir.shift.rekap', $shift));
        $resRekap->assertOk();
        $resRekap->assertDontSee('harga_modal');
        $resRekap->assertDontSee('Harga Modal');
        $resRekap->assertDontSee('Laba Kotor');
        $resRekap->assertDontSee('Keuntungan');

        // Cek struk
        $resStruk = $this->get(route('kasir.penjualan.struk', $penjualan));
        $resStruk->assertOk();
        $resStruk->assertDontSee('Harga Modal');
        $resStruk->assertDontSee('Laba');
    }

    /**
     * Test 22: Dashboard: kartu Shift Berjalan dan Selisih Kas 7 Hari benar dan hanya untuk kios sendiri; penjualan hari ini tidak menghitung nota batal
     */
    public function test_22_dashboard_statistik_shift_dan_penjualan_benar(): void
    {
        $shiftService = app(ShiftService::class);
        $penjualanService = app(PenjualanService::class);

        // Kios A: 1 shift berjalan
        $shiftA = $shiftService->buka($this->kasirA, 100000);

        // Kios B: 1 shift berjalan (tidak boleh terhitung di kios A)
        $shiftService->buka($this->kasirB, 100000);

        // Nota 1 (selesai): 60.000
        $p1 = $penjualanService->buat(
            items: [['barang_id' => $this->barangA1->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'tok-dash-1',
            user: $this->kasirA
        );

        // Nota 2 (dibatalkan): 18.000
        $p2 = $penjualanService->buat(
            items: [['barang_id' => $this->barangA2->id, 'jumlah' => 1]],
            bayar: 50000,
            token: 'tok-dash-2',
            user: $this->kasirA
        );
        $penjualanService->batalkan($p2, 'Nota dibatalkan untuk uji dashboard', $this->kasirA);

        $this->actingAs($this->adminA);
        $response = $this->get(route('admin.dashboard'));
        $response->assertOk();

        // 1 shift berjalan milik kios A
        $response->assertViewHas('shiftBerjalanCount', 1);

        // Omzet hari ini hanya menghitung nota selesai (60.000), bukan 78.000
        $response->assertViewHas('omzetHariIni', 60000);
        $response->assertViewHas('totalTransaksiHariIni', 1);
    }

    /**
     * Test 23: Batas hari dan durasi shift mengikuti zona waktu Asia/Jayapura
     */
    public function test_23_batas_hari_dan_durasi_shift_mengikuti_zona_waktu(): void
    {
        $waktuBuka = Carbon::parse('2026-11-18 08:00:00', 'Asia/Jayapura');
        $waktuTutup = Carbon::parse('2026-11-18 10:30:00', 'Asia/Jayapura');

        $shift = ShiftKasir::create([
            'kios_id' => $this->kiosA->id,
            'user_id' => $this->kasirA->id,
            'modal_awal' => 100000,
            'waktu_buka' => $waktuBuka,
            'waktu_tutup' => $waktuTutup,
            'status' => 'ditutup',
        ]);

        $this->assertEquals('2 jam 30 menit', $shift->durasi);
        $this->assertFalse($shift->is_lebih_dari_16_jam);

        // Shift lebih dari 16 jam
        $shiftOverdue = ShiftKasir::create([
            'kios_id' => $this->kiosA->id,
            'user_id' => $this->kasirA2->id,
            'modal_awal' => 100000,
            'waktu_buka' => Carbon::now('Asia/Jayapura')->subHours(18),
            'status' => 'berjalan',
        ]);

        $this->assertTrue($shiftOverdue->is_lebih_dari_16_jam);
    }

    /**
     * Test 24: Filter daftar shift admin bekerja benar
     */
    public function test_24_filter_daftar_shift_admin_bekerja_benar(): void
    {
        $shiftService = app(ShiftService::class);

        // Shift 1: Kasir A, status berjalan
        $s1 = $shiftService->buka($this->kasirA, 100000);

        // Shift 2: Kasir A2, status ditutup dengan selisih
        $s2 = $shiftService->buka($this->kasirA2, 100000);
        $shiftService->tutup($s2, 95000, 'Kurang 5rb', $this->kasirA2);

        $this->actingAs($this->adminA);

        // Filter status=berjalan
        $resBerjalan = $this->get(route('admin.shift.index', ['status' => 'berjalan']));
        $resBerjalan->assertSee('Masih aktif');
        $resBerjalan->assertDontSee('- Rp 5.000');

        // Filter status=ditutup
        $resDitutup = $this->get(route('admin.shift.index', ['status' => 'ditutup']));
        $resDitutup->assertSee('- Rp 5.000');
        $resDitutup->assertDontSee('Masih aktif');

        // Filter hanya_selisih=1
        $resSelisih = $this->get(route('admin.shift.index', ['hanya_selisih' => '1']));
        $resSelisih->assertSee('- Rp 5.000');
    }
}
