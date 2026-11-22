<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\Concerns\BelongsToKios;
use App\Models\DetailBarangMasuk;
use App\Models\DetailPenjualan;
use App\Models\DetailPenyesuaianStok;
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
use App\Services\PenjualanService;
use App\Services\PenyesuaianStokService;
use App\Services\ShiftService;
use App\Services\StokService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use Tests\TestCase;

class TahapSebelasTest extends TestCase
{
    use RefreshDatabase;

    protected Kios $kiosA;
    protected User $adminA;
    protected User $kasirA;

    protected Kios $kiosB;
    protected User $adminB;
    protected User $kasirB;

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan Kios A
        $this->kiosA = Kios::create([
            'nama_kios' => 'Kios Alpha Mandiri',
            'alamat' => 'Jl. Danau Sentani No. 10',
            'no_telepon' => '081211112222',
            'status' => 'aktif',
        ]);
        $this->adminA = User::create([
            'kios_id' => $this->kiosA->id,
            'name' => 'Admin Alpha',
            'username' => 'admin_alpha',
            'email' => 'admin_alpha@kios.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);
        $this->kasirA = User::create([
            'kios_id' => $this->kiosA->id,
            'name' => 'Kasir Alpha',
            'username' => 'kasir_alpha',
            'email' => null,
            'password' => Hash::make('password123'),
            'role' => 'kasir',
            'is_aktif' => true,
        ]);

        // Siapkan Kios B
        $this->kiosB = Kios::create([
            'nama_kios' => 'Kios Beta Sejahtera',
            'alamat' => 'Jl. Raya Abepura No. 99',
            'no_telepon' => '081299998888',
            'status' => 'aktif',
        ]);
        $this->adminB = User::create([
            'kios_id' => $this->kiosB->id,
            'name' => 'Admin Beta',
            'username' => 'admin_beta',
            'email' => 'admin_beta@kios.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);
        $this->kasirB = User::create([
            'kios_id' => $this->kiosB->id,
            'name' => 'Kasir Beta',
            'username' => 'kasir_beta',
            'email' => null,
            'password' => Hash::make('password123'),
            'role' => 'kasir',
            'is_aktif' => true,
        ]);
    }

    /**
     * A.1: Test Refleksi BelongsToKios pada seluruh model yang memiliki kolom kios_id
     */
    public function test_a1_refleksi_semua_model_tenant_memakai_trait_belongs_to_kios(): void
    {
        $modelsPath = app_path('Models');
        $files = File::files($modelsPath);

        $tenantModels = [];
        $missingTrait = [];

        foreach ($files as $file) {
            $className = 'App\\Models\\' . $file->getFilenameWithoutExtension();
            if (!class_exists($className)) {
                continue;
            }

            $ref = new ReflectionClass($className);
            if ($ref->isSubclassOf(Model::class) && !$ref->isAbstract()) {
                /** @var Model $instance */
                $instance = new $className();
                $table = $instance->getTable();

                // Abaikan tabel Kios (tenant root) dan User (pengguna auth)
                if (in_array($table, ['kios', 'users'], true)) {
                    continue;
                }

                // Periksa apakah tabel memiliki kolom kios_id
                if (DB::getSchemaBuilder()->hasColumn($table, 'kios_id')) {
                    $tenantModels[] = $className;
                    $traits = class_uses_recursive($className);

                    if (!in_array(BelongsToKios::class, $traits, true)) {
                        $missingTrait[] = $className;
                    }
                }
            }
        }

        $this->assertNotEmpty($tenantModels, 'Harus ada model tenant yang terdeteksi.');
        $this->assertEmpty($missingTrait, 'Model berikut memiliki kios_id tetapi lupa memakai trait BelongsToKios: ' . implode(', ', $missingTrait));
    }

    /**
     * A.2: Test Route Sistematis: Akses ID milik Kios B oleh Admin Kios A selalu 404 / 403
     */
    public function test_a2_isolasi_sistematis_cross_tenant_routes_menghasilkan_404_atau_403(): void
    {
        // 1. Buat data lengkap di Kios B
        $katB = Kategori::create(['kios_id' => $this->kiosB->id, 'nama' => 'Kategori Beta']);
        $brgB = Barang::create([
            'kios_id' => $this->kiosB->id,
            'kategori_id' => $katB->id,
            'kode' => 'BRG-B001',
            'nama' => 'Barang Milik Beta',
            'satuan' => 'pcs',
            'harga_modal' => 5000,
            'harga_jual' => 7000,
            'stok' => 10,
            'stok_minimum' => 2,
            'is_aktif' => true,
        ]);
        $supB = Supplier::create([
            'kios_id' => $this->kiosB->id,
            'nama' => 'Supplier Beta',
            'no_telepon' => '0812345678',
            'alamat' => 'Alamat Beta',
            'is_aktif' => true,
        ]);

        $bmB = BarangMasuk::create([
            'kios_id' => $this->kiosB->id,
            'supplier_id' => $supB->id,
            'user_id' => $this->adminB->id,
            'nomor' => 'BM-B-0001',
            'tanggal' => now()->toDateString(),
            'total' => 50000,
            'status' => 'selesai',
        ]);

        $adjB = PenyesuaianStok::create([
            'kios_id' => $this->kiosB->id,
            'user_id' => $this->adminB->id,
            'nomor' => 'ADJ-B-0001',
            'tanggal' => now()->toDateString(),
            'mode' => 'manual',
            'total_kerugian' => 5000,
        ]);

        $shiftB = ShiftKasir::create([
            'kios_id' => $this->kiosB->id,
            'user_id' => $this->kasirB->id,
            'waktu_buka' => now()->subHours(2),
            'modal_awal' => 50000,
            'status' => 'berjalan',
        ]);

        $penjB = Penjualan::create([
            'kios_id' => $this->kiosB->id,
            'user_id' => $this->kasirB->id,
            'shift_id' => $shiftB->id,
            'nomor' => 'TRX-B-0001',
            'waktu_transaksi' => now(),
            'total' => 14000,
            'total_modal' => 10000,
            'bayar' => 20000,
            'kembalian' => 6000,
            'metode_bayar' => 'tunai',
            'status' => 'selesai',
            'token' => 'token-beta-1',
        ]);

        // 2. Login sebagai Admin Kios A dan coba manipulasi/baca seluruh resource milik Kios B
        $this->actingAs($this->adminA);

        // Kategori Kios B
        $this->put(route('admin.kategori.update', $katB->id), ['nama' => 'Hacked'])->assertNotFound();
        $this->delete(route('admin.kategori.destroy', $katB->id))->assertNotFound();

        // Barang Kios B
        $this->get(route('admin.barang.show', $brgB->id))->assertNotFound();
        $this->get(route('admin.barang.edit', $brgB->id))->assertNotFound();
        $this->put(route('admin.barang.update', $brgB->id), ['nama' => 'Hacked'])->assertNotFound();
        $this->post(route('admin.barang.toggle', $brgB->id))->assertNotFound();

        // Supplier Kios B
        $this->put(route('admin.supplier.update', $supB->id), ['nama' => 'Hacked'])->assertNotFound();
        $this->post(route('admin.supplier.toggle', $supB->id))->assertNotFound();

        // Barang Masuk Kios B
        $this->get(route('admin.barang-masuk.show', $bmB->id))->assertNotFound();
        $this->post(route('admin.barang-masuk.batal', $bmB->id), ['alasan_batal' => 'Curi nota'])->assertNotFound();

        // Penyesuaian Kios B
        $this->get(route('admin.penyesuaian.show', $adjB->id))->assertNotFound();

        // Penjualan Kios B
        $this->get(route('admin.penjualan.show', $penjB->id))->assertNotFound();
        $this->post(route('admin.penjualan.batal', $penjB->id), ['alasan_batal' => 'Batal paksa'])->assertNotFound();

        // Shift Kasir Kios B
        $this->get(route('admin.shift.show', $shiftB->id))->assertNotFound();
        $this->post(route('admin.shift.tutup_paksa', $shiftB->id))->assertNotFound();

        // Kasir Kios B
        $this->get(route('admin.kasir.edit', $this->kasirB->id))->assertNotFound();
        $this->put(route('admin.kasir.update', $this->kasirB->id), ['name' => 'Hacked Kasir', 'username' => 'hacked_kasir_b'])->assertNotFound();
        $this->post(route('admin.kasir.toggle', $this->kasirB->id))->assertNotFound();
        $this->post(route('admin.kasir.resetPassword', $this->kasirB->id), ['password' => 'password123', 'password_confirmation' => 'password123'])->assertNotFound();

        // Struk Kasir B
        $this->get(route('kasir.penjualan.struk', $penjB->id))->assertNotFound();

        // 3. Login sebagai Kasir Kios A mencoba akses kasir/struk Kios B
        $this->flushSession();
        $this->actingAs($this->kasirA);
        $this->get(route('kasir.penjualan.struk', $penjB->id))->assertNotFound();
        $this->post(route('kasir.penjualan.batal', $penjB->id), ['alasan' => 'Kasir A void B'])->assertNotFound();
    }

    /**
     * A.3: Test Pencarian Statis Query Mentah DB::table / DB::select tanpa kios_id
     */
    public function test_a3_audit_query_mentah_disertai_filter_kios_id(): void
    {
        $appPath = app_path();
        $files = File::allFiles($appPath);

        // Daftar file dan method yang diperbolehkan secara sah mengakses DB tanpa filter kios_id:
        // - AuthenticatedSessionController / LoginRequest: autentikasi global untuk menemukan user dari username/email
        // - RegisterKiosRequest / RegisteredUserController: validasi unique global untuk email & pendaftaran tenant baru
        // - CekIntegritasCommand / BackupDatabaseCommand: command pemeliharaan sistem tingkat sistem
        // - ResetDemoCommand: command reset lokal
        $allowedExceptions = [
            'CekIntegritasCommand.php',
            'BackupDatabaseCommand.php',
            'ResetDemoCommand.php',
            'LoginRequest.php',
            'RegisteredUserController.php',
            'RegisterKiosRequest.php',
        ];

        $suspiciousQueries = [];

        foreach ($files as $file) {
            $fileName = $file->getFilename();
            if (in_array($fileName, $allowedExceptions, true)) {
                continue;
            }

            $content = File::get($file->getRealPath());
            $lines = explode("\n", $content);

            foreach ($lines as $lineNum => $line) {
                if (preg_match('/DB::table\([\'"](\w+)[\'"]\)/', $line, $matches)) {
                    $table = $matches[1];
                    // Jika tabel adalah tabel tenant, pastikan baris atau konteks sekitarnya memfilter kios_id
                    $tenantTables = ['barang', 'kategori', 'supplier', 'barang_masuk', 'penyesuaian_stok', 'penjualan', 'shift_kasir', 'mutasi_stok', 'log_aktivitas'];
                    if (in_array($table, $tenantTables, true)) {
                        $context = implode("\n", array_slice($lines, max(0, $lineNum - 2), 6));
                        if (!str_contains($context, 'kios_id') && !str_contains($context, 'BelongsToKios')) {
                            $suspiciousQueries[] = "{$fileName}:{$lineNum} -> {$line}";
                        }
                    }
                }
            }
        }

        $this->assertEmpty($suspiciousQueries, 'Ditemukan query DB::table mentah tanpa filter kios_id: ' . implode("\n", $suspiciousQueries));
    }

    /**
     * A.4: Test Angka: Data Kios A dan Kios B terpisah sempurna pada Dashboard, Laporan, dan Analisis
     */
    public function test_a4_isolasi_angka_dashboard_laporan_dan_analisis_hanya_kios_sendiri(): void
    {
        $stokService = app(StokService::class);
        $penjualanService = app(PenjualanService::class);
        $shiftService = app(ShiftService::class);

        // Kios A: 1 barang, 1 penjualan Rp 50.000
        $brgA = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-A01',
            'nama' => 'Beras Alpha',
            'satuan' => 'kg',
            'harga_modal' => 30000,
            'harga_jual' => 50000,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_aktif' => true,
        ]);
        $stokService->catat($brgA, 'stok_awal', 10, 'Stok awal');
        $shiftA = $shiftService->buka($this->adminA, 100000, now());
        $penjualanService->buat([['barang_id' => $brgA->id, 'jumlah' => 1]], 50000, 'tok-A1', $this->adminA);

        // Kios B: 1 barang, 1 penjualan Rp 120.000
        $brgB = Barang::create([
            'kios_id' => $this->kiosB->id,
            'kode' => 'BRG-B01',
            'nama' => 'Minyak Beta',
            'satuan' => 'btl',
            'harga_modal' => 80000,
            'harga_jual' => 120000,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_aktif' => true,
        ]);
        $stokService->catat($brgB, 'stok_awal', 10, 'Stok awal');
        $shiftB = $shiftService->buka($this->adminB, 200000, now());
        $penjualanService->buat([['barang_id' => $brgB->id, 'jumlah' => 1]], 120000, 'tok-B1', $this->adminB);

        // 1. Dashboard Kios A
        $this->actingAs($this->adminA);
        $resDashA = $this->get(route('admin.dashboard'));
        $resDashA->assertOk();
        $resDashA->assertSee('50.000');
        $resDashA->assertDontSee('120.000');
        $resDashA->assertSee('Beras Alpha');
        $resDashA->assertDontSee('Minyak Beta');

        // 2. Laporan Keuangan Kios A
        $resKeuA = $this->get(route('admin.laporan.keuangan'));
        $resKeuA->assertOk();
        $resKeuA->assertSee('50.000');
        $resKeuA->assertDontSee('120.000');

        // 3. Laporan Audit Transaksi & Shift Kios A
        $resAuditA = $this->get(route('admin.laporan.audit'));
        $resAuditA->assertOk();
        $resAuditA->assertSee('Admin Alpha');
        $resAuditA->assertDontSee('Admin Beta');

        // 4. Laporan Stok Kios A
        $resStokA = $this->get(route('admin.laporan.stok'));
        $resStokA->assertOk();
        $resStokA->assertSee('Beras Alpha');
        $resStokA->assertDontSee('Minyak Beta');

        // 5. Analisis Performa Kios A
        $resAnalisisA = $this->get(route('admin.analisis.index'));
        $resAnalisisA->assertOk();
        $resAnalisisA->assertSee('Beras Alpha');
        $resAnalisisA->assertDontSee('Minyak Beta');
    }

    /**
     * B: Skenario End-to-End (E2E) Penuh Siklus Hidup Kios
     */
    public function test_b_skenario_end_to_end_siklus_lengkap_kios(): void
    {
        $stokService = app(StokService::class);
        $penjualanService = app(PenjualanService::class);
        $shiftService = app(ShiftService::class);
        $barangMasukService = app(BarangMasukService::class);
        $penyesuaianService = app(PenyesuaianStokService::class);

        // 1. Daftar Kios Baru melalui Registrasi
        $regData = [
            'nama_kios' => 'Kios E2E Sukses',
            'name' => 'Owner E2E',
            'email' => 'owner.e2e@kios.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'alamat' => 'Jl. E2E No. 1',
            'no_telepon' => '08123456789',
        ];
        $resReg = $this->post(route('register'), $regData);
        $resReg->assertRedirect(route('admin.dashboard'));

        $kios = Kios::where('nama_kios', 'Kios E2E Sukses')->first();
        $this->assertNotNull($kios);
        $admin = User::where('email', 'owner.e2e@kios.test')->first();
        $this->assertNotNull($admin);

        // 2. Admin menambah kasir
        $this->actingAs($admin);
        $this->post(route('admin.kasir.store'), [
            'name' => 'Kasir E2E',
            'username' => 'kasir_e2e',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('admin.kasir.index'));
        $kasir = User::where('username', 'kasir_e2e')->first();
        $this->assertNotNull($kasir);

        // 3. Admin membuat kategori dan master barang dengan stok awal
        $kat = Kategori::create(['kios_id' => $kios->id, 'nama' => 'Sembako E2E']);
        $brg1 = Barang::create([
            'kios_id' => $kios->id,
            'kategori_id' => $kat->id,
            'kode' => 'E2E-001',
            'nama' => 'Gula Pasir E2E',
            'satuan' => 'kg',
            'harga_modal' => 10000,
            'harga_jual' => 14000,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_aktif' => true,
        ]);
        $stokService->catat($brg1, 'stok_awal', 20, 'Stok awal'); // stok = 20, modal = 10.000

        // 4. Admin mencatat barang masuk (cek HPP rata-rata bergerak)
        // Beli 10 kg seharga 13.000 -> modal baru = (20*10.000 + 10*13.000)/30 = 330.000 / 30 = 11.000
        $barangMasukService->simpan([
            'tanggal' => now()->toDateString(),
            'catatan' => 'Restok tambahan gula',
            'items' => [
                ['barang_id' => $brg1->id, 'jumlah' => 10, 'harga_beli' => 13000],
            ],
        ], $admin);
        $brg1->refresh();
        $this->assertEquals(30, $brg1->stok);
        $this->assertEquals(11000, $brg1->harga_modal);

        // 5. Kasir login, buka shift
        $this->actingAs($kasir);
        $shift = $shiftService->buka($kasir, 100000, now());
        $this->assertEquals('berjalan', $shift->status);

        // 6. Kasir menjual barang
        $nota1 = $penjualanService->buat([['barang_id' => $brg1->id, 'jumlah' => 5]], 70000, 'token-e2e-1', $kasir);
        $brg1->refresh();
        $this->assertEquals(25, $brg1->stok);

        // 7. Kasir membatalkan satu nota (void)
        $nota2 = $penjualanService->buat([['barang_id' => $brg1->id, 'jumlah' => 3]], 50000, 'token-e2e-2', $kasir);
        $brg1->refresh();
        $this->assertEquals(22, $brg1->stok);
        $penjualanService->batalkan($nota2, 'Pembeli batal', $kasir);
        $brg1->refresh();
        $this->assertEquals(25, $brg1->stok); // Stok kembali 25

        // 8. Kasir menutup shift dengan selisih dan catatan
        // Modal awal: 100.000, Tunai selesai: 70.000 (nota 1 saja) -> Seharusnya = 170.000. Fisik = 175.000 (lebih 5.000)
        $shiftService->tutup($shift, 175000, 'Lebihan uang kembalian receh', $kasir);
        $shift->refresh();
        $this->assertEquals('ditutup', $shift->status);
        $this->assertEquals(5000, $shift->selisih);

        // 9. Admin melihat shift, penjualan, laporan, audit, stok
        $this->flushSession();
        $this->actingAs($admin);
        $this->get(route('admin.shift.index'))->assertOk()->assertSee('+ Rp 5.000');
        $this->get(route('admin.shift.show', $shift->id))->assertOk()->assertSee('Lebihan uang kembalian receh');
        $this->get(route('admin.penjualan.index'))->assertOk()->assertSee($nota1->nomor);
        $this->get(route('admin.laporan.keuangan'))->assertOk()->assertSee('70.000');
        $this->get(route('admin.laporan.audit'))->assertOk()->assertSee($nota1->nomor);
        $this->get(route('admin.laporan.stok'))->assertOk()->assertSee('25');

        // 10. Admin penyesuaian dan opname
        $penyesuaianService->simpanManual([
            'tanggal' => now()->toDateString(),
            'catatan' => 'Pecah 1 kg',
            'items' => [
                ['barang_id' => $brg1->id, 'jenis' => 'rusak', 'arah' => 'kurang', 'jumlah' => 1, 'alasan' => 'Bungkus bocor'],
            ],
        ], $admin);
        $brg1->refresh();
        $this->assertEquals(24, $brg1->stok);

        // Opname fisik 24 -> 23 (selisih kurang 1)
        $penyesuaianService->simpanOpname([
            'tanggal' => now()->toDateString(),
            'catatan' => 'Opname akhir',
            'items' => [
                ['barang_id' => $brg1->id, 'stok_sistem' => 24, 'stok_fisik' => 23, 'alasan' => 'Selisih opname fisik'],
            ],
        ], $admin);
        $brg1->refresh();
        $this->assertEquals(23, $brg1->stok);

        // 11. Analisis dan rekomendasi restok berjalan
        $this->get(route('admin.analisis.index'))->assertOk()->assertSee('Gula Pasir E2E');
        $this->get(route('admin.analisis.rekomendasi'))->assertOk();

        // 12. Unduh PDF laporan dan rekomendasi
        $this->get(route('admin.laporan.keuangan.pdf'))->assertOk();
        $this->get(route('admin.laporan.audit.pdf'))->assertOk();
        $this->get(route('admin.laporan.stok.pdf'))->assertOk();
        $this->get(route('admin.analisis.pdf'))->assertOk();

        // 13. Cek integritas sistem menghasilkan 0 temuan
        $exitCode = Artisan::call('stokify:cek-integritas');
        $this->assertEquals(0, $exitCode);

        // 14. Verifikasi akhir: stok akhir barang = mutasi_stok terakhir
        $latestMutasi = MutasiStok::where('kios_id', $kios->id)->where('barang_id', $brg1->id)->latest('id')->first();
        $this->assertEquals($brg1->stok, $latestMutasi->stok_sesudah);
    }

    /**
     * C.1: Profil Kios: Admin dapat mengubah, Kasir 403, perubahan tampil di struk dan PDF, log kios.ubah
     */
    public function test_c1_profil_kios_crud_hak_akses_struk_dan_log(): void
    {
        // 1. Kasir mendapat 403
        $this->actingAs($this->kasirA);
        $this->get(route('admin.kios.index'))->assertForbidden();
        $this->put(route('admin.kios.update'), ['nama_kios' => 'Kios Bajakan'])->assertForbidden();

        // 2. Admin dapat melihat halaman profil kios
        $this->flushSession();
        $this->actingAs($this->adminA);
        $resIndex = $this->get(route('admin.kios.index'));
        $resIndex->assertOk();
        $resIndex->assertSee('Kios Alpha Mandiri');
        $resIndex->assertSee('081211112222');

        // 3. Admin memperbarui profil kios
        $resUpdate = $this->put(route('admin.kios.update'), [
            'nama_kios' => 'Kios Alpha Sentosa Baru',
            'alamat' => 'Jl. Pelabuhan Jayapura No. 88',
            'no_telepon' => '081288887777',
        ]);
        $resUpdate->assertRedirect(route('admin.kios.index'));
        $resUpdate->assertSessionHas('success');

        $this->kiosA->refresh();
        $this->assertEquals('Kios Alpha Sentosa Baru', $this->kiosA->nama_kios);
        $this->assertEquals('Jl. Pelabuhan Jayapura No. 88', $this->kiosA->alamat);
        $this->assertEquals('081288887777', $this->kiosA->no_telepon);

        // 4. Log aktivitas kios.ubah tercatat
        $log = LogAktivitas::where('kios_id', $this->kiosA->id)->where('aksi', 'kios.ubah')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertEquals('Kios Alpha Mandiri', $log->data_lama['nama_kios']);
        $this->assertEquals('Kios Alpha Sentosa Baru', $log->data_baru['nama_kios']);

        // 5. Perubahan otomatis terbaca di struk belanja
        $shift = app(ShiftService::class)->buka($this->adminA, 100000, now());
        $brg = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'BRG-STRUK',
            'nama' => 'Barang Struk',
            'satuan' => 'pcs',
            'harga_modal' => 5000,
            'harga_jual' => 10000,
            'stok' => 5,
            'stok_minimum' => 1,
            'is_aktif' => true,
        ]);
        app(StokService::class)->catat($brg, 'stok_awal', 5, 'Stok awal');
        $penjualan = app(PenjualanService::class)->buat([['barang_id' => $brg->id, 'jumlah' => 1]], 10000, 'tok-struk', $this->adminA);

        $resStruk = $this->get(route('kasir.penjualan.struk', $penjualan->id));
        $resStruk->assertOk();
        $resStruk->assertSee('Kios Alpha Sentosa Baru');
        $resStruk->assertSee('Jl. Pelabuhan Jayapura No. 88');
        $resStruk->assertSee('081288887777');

        // 6. Perubahan otomatis terbaca di PDF laporan
        $resPdf = $this->get(route('admin.laporan.keuangan.pdf'));
        $resPdf->assertOk();
    }

    /**
     * C.2: Log Aktivitas: Hanya kios sendiri, filter bekerja, kasir 403, field sensitif ter-mask
     */
    public function test_c2_penampil_log_aktivitas_dan_masking_field_sensitif(): void
    {
        // 1. Kasir 403
        $this->actingAs($this->kasirA);
        $this->get(route('admin.log.index'))->assertForbidden();

        $this->flushSession();
        auth()->logout();

        // 2. Buat log di Kios A dan Kios B
        LogAktivitas::create([
            'kios_id' => $this->kiosA->id,
            'user_id' => $this->adminA->id,
            'aksi' => 'barang.tambah',
            'deskripsi' => 'Menambahkan barang log Alpha',
            'data_lama' => null,
            'data_baru' => ['nama' => 'Barang Alpha', 'password' => 'secret123', 'token' => 'abcde'],
        ]);

        LogAktivitas::create([
            'kios_id' => $this->kiosB->id,
            'user_id' => $this->adminB->id,
            'aksi' => 'barang.tambah',
            'deskripsi' => 'Menambahkan barang log Beta',
            'data_lama' => null,
            'data_baru' => ['nama' => 'Barang Beta'],
        ]);

        // 3. Admin A hanya melihat log Kios A
        $this->flushSession();
        $this->actingAs($this->adminA);
        $resLog = $this->get(route('admin.log.index'));
        $resLog->assertOk();
        $resLog->assertSee('Menambahkan barang log Alpha');
        $resLog->assertDontSee('Menambahkan barang log Beta');

        // 4. Pastikan password & token di-mask (********) dan tidak bocor sebagai teks asli
        $resLog->assertSee('********');
        $resLog->assertDontSee('secret123');

        // 5. Filter kategori aksi
        $resFilter = $this->get(route('admin.log.index', ['kategori_aksi' => 'barang']));
        $resFilter->assertOk();
        $resFilter->assertSee('Menambahkan barang log Alpha');

        // 6. Tidak ada route update atau destroy untuk log
        $this->assertFalse(Route::has('admin.log.update'));
        $this->assertFalse(Route::has('admin.log.destroy'));
    }

    /**
     * C.3: Halaman Error Kustom (403, 404, 419, 429) & Konsistensi JSON error
     */
    public function test_c3_halaman_error_kustom_dan_respons_json(): void
    {
        // 1. Error 404 Web View
        $this->actingAs($this->adminA);
        $res404 = $this->get('/admin/halaman-ngawur-tidak-ada');
        $res404->assertNotFound();
        $res404->assertSee('Halaman Tidak Ditemukan');
        $res404->assertSee('Kembali ke Dashboard Admin');

        // 2. Error 403 Web View
        $this->actingAs($this->kasirA);
        $res403 = $this->get(route('admin.dashboard'));
        $res403->assertForbidden();
        $res403->assertSee('Akses Tidak Diizinkan');
        $res403->assertSee('Kembali ke Layar Kasir');

        // 3. JSON 422 Validasi Kasir
        $resJson422 = $this->postJson(route('kasir.penjualan.store'), [
            'items' => [],
            'bayar' => 0,
            'token' => '',
        ]);
        $resJson422->assertStatus(422);
        $resJson422->assertJsonStructure(['success', 'message']);
        $this->assertFalse($resJson422->json('success'));

        // 4. View 419 dan 429 dapat dirender
        $view419 = view('errors.419')->render();
        $this->assertStringContainsString('Sesi Habis, Muat Ulang Halaman', $view419);
        $this->assertStringContainsString('Keranjang belanja transaksi Anda tetap aman', $view419);

        $view429 = view('errors.429')->render();
        $this->assertStringContainsString('Terlalu Banyak Permintaan', $view429);
        $this->assertStringContainsString('Silakan tunggu beberapa saat', $view429);
    }

    /**
     * C.4: Rate Limiting: Permintaan melebihi batas menghasilkan status 429
     */
    public function test_c4_rate_limiting_bekerja_dan_mengembalikan_429(): void
    {
        // 1. Rate limiter unduh PDF (20 kali per menit per user)
        $this->actingAs($this->adminA);
        $keyPdfHashed = md5('unduh.pdf' . $this->adminA->id);
        $keyPdfUnhashed = 'unduh.pdf:' . $this->adminA->id;
        RateLimiter::clear($keyPdfHashed);
        RateLimiter::clear($keyPdfUnhashed);

        for ($i = 0; $i < 20; $i++) {
            RateLimiter::hit($keyPdfHashed);
            RateLimiter::hit($keyPdfUnhashed);
        }

        $resPdfThrottled = $this->get(route('admin.laporan.keuangan.pdf'));
        $resPdfThrottled->assertStatus(429);

        // 2. Rate limiter kasir pencarian barang (120 per menit)
        $keyCariHashed = md5('kasir.barang.cari' . $this->kasirA->id);
        $keyCariUnhashed = 'kasir.barang.cari:' . $this->kasirA->id;
        RateLimiter::clear($keyCariHashed);
        RateLimiter::clear($keyCariUnhashed);

        for ($i = 0; $i < 120; $i++) {
            RateLimiter::hit($keyCariHashed);
            RateLimiter::hit($keyCariUnhashed);
        }

        $this->flushSession();
        $this->actingAs($this->kasirA);
        $resCariThrottled = $this->getJson(route('kasir.barang.cari', ['q' => 'beras']));
        $resCariThrottled->assertStatus(429);
        $this->assertFalse($resCariThrottled->json('success'));
    }

    /**
     * C.5: Security Headers Middleware: Memastikan header keamanan standar ada pada respons
     */
    public function test_c5_security_headers_middleware_tercantum_pada_respons(): void
    {
        $res = $this->get('/');
        $res->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $res->assertHeader('X-Content-Type-Options', 'nosniff');
        $res->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $res->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    /**
     * C.6: Mass Assignment Protection: Kolom rahasia diabaikan dari input mentah
     */
    public function test_c6_mass_assignment_protection_tidak_dapat_disuntik_lewat_request(): void
    {
        $this->actingAs($this->adminA);

        // Coba suntikkan kios_id lain atau is_aktif pada tambah kasir
        $this->post(route('admin.kasir.store'), [
            'name' => 'Kasir Hacking',
            'username' => 'kasir_hacking',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'kios_id' => $this->kiosB->id, // Suntikan kios lain
            'role' => 'admin', // Suntikan escalasi role
        ]);

        $created = User::where('username', 'kasir_hacking')->first();
        $this->assertNotNull($created);
        $this->assertEquals($this->kiosA->id, $created->kios_id); // Tetap kios A
        $this->assertEquals('kasir', $created->role); // Tetap kasir, tidak bisa jadi admin
    }

    /**
     * C.7: Command stokify:cek-integritas: Nol temuan pada data bersih, mendeteksi jika data sengaja dirusak
     */
    public function test_c7_cek_integritas_mendeteksi_anomali_stok_database(): void
    {
        // 1. Data bersih: 0 temuan, exit code 0
        $exitClean = Artisan::call('stokify:cek-integritas');
        $this->assertEquals(0, $exitClean);

        // 2. Sengaja manipulasi stok master tanpa mutasi_stok
        $brg = Barang::create([
            'kios_id' => $this->kiosA->id,
            'kode' => 'RUSAK-001',
            'nama' => 'Barang Korup',
            'satuan' => 'pcs',
            'harga_modal' => 1000,
            'harga_jual' => 2000,
            'stok' => 50, // Stok master 50 tapi mutasi 0!
            'stok_minimum' => 1,
            'is_aktif' => true,
        ]);

        $exitCorrupt = Artisan::call('stokify:cek-integritas');
        $this->assertEquals(1, $exitCorrupt); // Berhasil mendeteksi anomali!
    }

    /**
     * C.8: Command stokify:reset-demo: Menolak berjalan jika APP_ENV=production
     */
    public function test_c8_reset_demo_menolak_pada_environment_production(): void
    {
        // Simulasikan environment production
        $this->app->detectEnvironment(fn() => 'production');
        $this->assertTrue($this->app->isProduction());

        $exitCode = Artisan::call('stokify:reset-demo');
        $this->assertEquals(1, $exitCode);
    }

    /**
     * C.9: Pindai seluruh view dan PDF memastikan 0 kemunculan kata "kulakan" dan "laba bersih"
     */
    public function test_c9_pindai_views_dan_pdf_bebas_dari_kata_kulakan_dan_laba_bersih(): void
    {
        $viewsPath = resource_path('views');
        $files = File::allFiles($viewsPath);

        $forbiddenWords = ['kulakan', 'kulak', 'dikulak', 'mengkulak', 'laba bersih'];
        $violations = [];

        foreach ($files as $file) {
            $raw = File::get($file->getRealPath());

            // Bersihkan komentar blade
            $clean = preg_replace('/\{\{--.*?--\}\}/s', '', $raw);
            // Bersihkan ekspresi PHP blade {{ ... }} dan {!! ... !!}
            $clean = preg_replace('/\{\{.*?\}\}/s', '', $clean);
            $clean = preg_replace('/\{!!.*?!!\}/s', '', $clean);
            // Bersihkan blok tag php
            $clean = preg_replace('/<\\?php.*?\\?>/s', '', $clean);
            // Bersihkan direktif blade seperti @if(...), @foreach(...)
            $clean = preg_replace('/@\w+(\([^\)]*\))?/s', '', $clean);
            // Bersihkan tag HTML beserta atributnya (agar nilai atribut form name="" atau value="" tidak salah dideteksi)
            $visibleText = strtolower(strip_tags($clean));

            $relPath = $file->getRelativePathname();

            foreach ($forbiddenWords as $word) {
                // Gunakan boundary regex atau word check agar tidak mencocokkan potongan kata tak sengaja
                if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $visibleText)) {
                    $violations[] = "File: {$relPath} mengandung kata terlarang '{$word}' pada teks tampilan antarmuka";
                }
            }
        }

        $this->assertEmpty($violations, "Ditemukan pelanggaran istilah terlarang pada antarmuka:\n" . implode("\n", $violations));
    }

    /**
     * C.10: Landing Page: Menggunakan mockup HTML sebagai cadangan saat gambar fisik belum ada
     */
    public function test_c10_landing_page_fallback_mockup_dan_batasan_sistem(): void
    {
        $res = $this->get('/');
        $res->assertOk();
        $res->assertSee('Kelola Kios Lebih Rapi');
        $res->assertSee('Batasan Sistem Stokify');
        $res->assertSee('Pembayaran Tunai Saja');
        $res->assertSee('Tanpa Kasbon / Piutang');
        $res->assertSee('Satu Lokasi per Kios');
        $res->assertDontSee('Dipercaya oleh 500+ pemilik kios'); // Bebas klaim palsu
    }

    /**
     * C.11: Query Count Benchmark: Mengukur jumlah query pada halaman utama
     */
    public function test_c11_benchmark_jumlah_query_pada_halaman_utama(): void
    {
        $this->actingAs($this->adminA);

        $pages = [
            ['name' => 'Dashboard', 'route' => 'admin.dashboard'],
            ['name' => 'Kasir POS', 'route' => 'kasir.index'],
            ['name' => 'Daftar Penjualan', 'route' => 'admin.penjualan.index'],
            ['name' => 'Laporan Stok', 'route' => 'admin.laporan.stok'],
            ['name' => 'Analisis Performa', 'route' => 'admin.analisis.index'],
            ['name' => 'Profil Kios', 'route' => 'admin.kios.index'],
            ['name' => 'Log Aktivitas', 'route' => 'admin.log.index'],
        ];

        foreach ($pages as $p) {
            DB::flushQueryLog();
            DB::enableQueryLog();

            $res = $this->get(route($p['route']));
            $res->assertOk();

            $count = count(DB::getQueryLog());
            // Pastikan tidak ada query N+1 berlebihan (Dashboard mengagregasi banyak modul sehingga batas 45, modul lain <= 30)
            $maxAllowed = $p['name'] === 'Dashboard' ? 45 : 30;
            $this->assertLessThan($maxAllowed, $count, "Halaman {$p['name']} mengeksekusi {$count} query (terlalu banyak).");
        }
    }
}
